<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\MessageTemplate;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CampaignController extends Controller
{
    protected $baileysUrl = 'http://127.0.0.1:3000';

    public function index(Request $request)
    {
        $pageTitle = 'WhatsApp Marketing Campaigns';
        $query = Campaign::query();

        if ($request->search) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%$search%")
                  ->orWhere('message', 'LIKE', "%$search%");
        }

        $campaigns = $query->latest()->paginate(getPaginate());
        return view('admin.campaign.index', compact('pageTitle', 'campaigns'));
    }

    public function create()
    {
        $pageTitle = 'Create New Campaign';
        $connectedAccounts = WhatsappAccount::active()->latest()->get();
        $templates = MessageTemplate::latest()->get();
        
        // Distinct groups from contacts table
        $groups = Contact::whereNotNull('group_id')
            ->where('group_id', '!=', '')
            ->selectRaw('group_name, group_id, count(*) as member_count')
            ->groupBy('group_name', 'group_id')
            ->get();

        $totalContacts = Contact::where('type', 'contact')->count();
        $totalGroups = $groups->count();

        return view('admin.campaign.create', compact('pageTitle', 'connectedAccounts', 'templates', 'groups', 'totalContacts', 'totalGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:190',
            'session_id'       => 'required|string',
            'target_type'      => 'required|string|in:groups,selected_groups,contacts,all,selected_group',
            'target_group_ids' => 'nullable|array',
            'target_group_id'  => 'nullable|string',
            'message'          => 'required|string',
            'delay_seconds'    => 'required|integer|min:1|max:60',
        ]);

        // Calculate recipients list based on target type
        $recipients = [];
        if ($request->target_type === 'groups') {
            $groups = Contact::whereNotNull('group_id')
                ->where('group_id', '!=', '')
                ->selectRaw('group_name, group_id')
                ->groupBy('group_name', 'group_id')
                ->get();

            foreach ($groups as $g) {
                $recipients[] = [
                    'type'       => 'group',
                    'name'       => $g->group_name,
                    'target_jid' => $g->group_id,
                    'group_name' => $g->group_name,
                    'group_id'   => $g->group_id,
                ];
            }
        } elseif ($request->target_type === 'selected_groups') {
            $selectedIds = $request->target_group_ids ?: [];
            if (empty($selectedIds)) {
                $notify[] = ['error', 'Please select at least one WhatsApp group.'];
                return back()->withInput()->withNotify($notify);
            }

            $groups = Contact::whereIn('group_id', $selectedIds)
                ->selectRaw('group_name, group_id')
                ->groupBy('group_name', 'group_id')
                ->get();

            foreach ($groups as $g) {
                $recipients[] = [
                    'type'       => 'group',
                    'name'       => $g->group_name,
                    'target_jid' => $g->group_id,
                    'group_name' => $g->group_name,
                    'group_id'   => $g->group_id,
                ];
            }
        } elseif ($request->target_type === 'selected_group') {
            $group = Contact::where('group_id', $request->target_group_id)->first();
            $recipients[] = [
                'type'       => 'group',
                'name'       => $group ? $group->group_name : 'Selected Group',
                'target_jid' => $request->target_group_id,
                'group_name' => $group ? $group->group_name : 'Selected Group',
                'group_id'   => $request->target_group_id,
            ];
        } elseif ($request->target_type === 'contacts') {
            $contacts = Contact::where('type', 'contact')->get();
            foreach ($contacts as $c) {
                $recipients[] = [
                    'type'       => 'contact',
                    'name'       => $c->name,
                    'target_jid' => $c->target_jid ?: "{$c->phone_number}@s.whatsapp.net",
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                    'group_id'   => $c->group_id,
                ];
            }
        } else { // 'all'
            $groups = Contact::whereNotNull('group_id')
                ->where('group_id', '!=', '')
                ->selectRaw('group_name, group_id')
                ->groupBy('group_name', 'group_id')
                ->get();
            foreach ($groups as $g) {
                $recipients[] = [
                    'type'       => 'group',
                    'name'       => $g->group_name,
                    'target_jid' => $g->group_id,
                    'group_name' => $g->group_name,
                    'group_id'   => $g->group_id,
                ];
            }

            $contacts = Contact::where('type', 'contact')->get();
            foreach ($contacts as $c) {
                $recipients[] = [
                    'type'       => 'contact',
                    'name'       => $c->name,
                    'target_jid' => $c->target_jid ?: "{$c->phone_number}@s.whatsapp.net",
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                    'group_id'   => $c->group_id,
                ];
            }
        }

        if (empty($recipients)) {
            $notify[] = ['error', 'No recipients found for the selected target audience.'];
            return back()->withInput()->withNotify($notify);
        }

        $campaign = new Campaign();
        $campaign->admin_id         = auth('admin')->id() ?? 1;
        $campaign->name             = $request->name;
        $campaign->session_id       = $request->session_id;
        $campaign->target_type      = $request->target_type;
        $campaign->target_group_ids = !empty($request->target_group_ids) ? json_encode($request->target_group_ids) : null;
        $campaign->target_group_id  = $request->target_group_id;
        $campaign->message          = $request->message;
        $campaign->delay_seconds    = $request->delay_seconds;
        $campaign->status           = 'ready';
        $campaign->total_targets    = count($recipients);
        $campaign->sent_count       = 0;
        $campaign->failed_count     = 0;
        $campaign->logs             = [];
        $campaign->save();

        $notify[] = ['success', 'Campaign created successfully! Ready to launch.'];
        return redirect()->route('admin.campaigns.view', $campaign->id)->withNotify($notify);
    }

    public function view($id)
    {
        $pageTitle = 'Campaign Execution & Live Delivery';
        $campaign = Campaign::findOrFail($id);

        // Fetch targets list for live execution
        $targets = [];
        if ($campaign->target_type === 'groups') {
            $groups = Contact::whereNotNull('group_id')
                ->where('group_id', '!=', '')
                ->selectRaw('group_name, group_id')
                ->groupBy('group_name', 'group_id')
                ->get();
            foreach ($groups as $g) {
                $targets[] = [
                    'type'       => 'group',
                    'name'       => $g->group_name,
                    'target_jid' => $g->group_id,
                    'group_name' => $g->group_name,
                ];
            }
        } elseif ($campaign->target_type === 'selected_groups') {
            $selectedIds = json_decode($campaign->target_group_ids ?? '[]', true) ?: [];
            $groups = Contact::whereIn('group_id', $selectedIds)
                ->selectRaw('group_name, group_id')
                ->groupBy('group_name', 'group_id')
                ->get();
            foreach ($groups as $g) {
                $targets[] = [
                    'type'       => 'group',
                    'name'       => $g->group_name,
                    'target_jid' => $g->group_id,
                    'group_name' => $g->group_name,
                ];
            }
        } elseif ($campaign->target_type === 'selected_group') {
            $group = Contact::where('group_id', $campaign->target_group_id)->first();
            $targets[] = [
                'type'       => 'group',
                'name'       => $group ? $group->group_name : 'Selected Group',
                'target_jid' => $campaign->target_group_id,
                'group_name' => $group ? $group->group_name : 'Selected Group',
            ];
        } elseif ($campaign->target_type === 'contacts') {
            $contacts = Contact::where('type', 'contact')->get();
            foreach ($contacts as $c) {
                $targets[] = [
                    'type'       => 'contact',
                    'name'       => $c->name,
                    'target_jid' => $c->target_jid ?: "{$c->phone_number}@s.whatsapp.net",
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                ];
            }
        } else {
            $groups = Contact::whereNotNull('group_id')
                ->where('group_id', '!=', '')
                ->selectRaw('group_name, group_id')
                ->groupBy('group_name', 'group_id')
                ->get();
            foreach ($groups as $g) {
                $targets[] = [
                    'type'       => 'group',
                    'name'       => $g->group_name,
                    'target_jid' => $g->group_id,
                    'group_name' => $g->group_name,
                ];
            }
            $contacts = Contact::where('type', 'contact')->get();
            foreach ($contacts as $c) {
                $targets[] = [
                    'type'       => 'contact',
                    'name'       => $c->name,
                    'target_jid' => $c->target_jid ?: "{$c->phone_number}@s.whatsapp.net",
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                ];
            }
        }

        $account = WhatsappAccount::where('session_id', $campaign->session_id)->first();

        return view('admin.campaign.view', compact('pageTitle', 'campaign', 'targets', 'account'));
    }

    public function sendSingle(Request $request, $id)
    {
        $request->validate([
            'target_jid' => 'required|string',
            'name'       => 'nullable|string',
            'type'       => 'nullable|string',
            'group_name' => 'nullable|string',
        ]);

        $campaign = Campaign::findOrFail($id);
        $name = $request->name ?: 'Customer';
        $groupName = $request->group_name ?: '';
        $phone = preg_replace('/[^0-9]/', '', $request->target_jid);

        // Replace shortcodes
        $personalizedMessage = str_replace(
            ['{name}', '{phone}', '{group_name}'],
            [$name, $phone, $groupName],
            $campaign->message
        );

        $isGroup = ($request->type === 'group' || str_ends_with($request->target_jid, '@g.us')) ? 1 : 0;

        try {
            $response = Http::timeout(25)->post("{$this->baileysUrl}/api/messages/send", [
                'sessionId' => $campaign->session_id,
                'receiver'  => $request->target_jid,
                'message'   => $personalizedMessage,
                'isGroup'   => $isGroup,
            ]);

            $resData = $response->json();

            $logEntry = [
                'timestamp'  => date('Y-m-d H:i:s'),
                'target'     => $name,
                'target_jid' => $request->target_jid,
                'type'       => $request->type,
            ];

            if ($response->successful() && ($resData['success'] ?? false)) {
                $campaign->increment('sent_count');
                $logEntry['status'] = 'success';
                $logEntry['message'] = 'Delivered';
                
                $currentLogs = $campaign->logs ?? [];
                $currentLogs[] = $logEntry;
                $campaign->logs = $currentLogs;
                $campaign->save();

                return response()->json(['success' => true, 'status' => 'success', 'message' => 'Delivered']);
            }

            $campaign->increment('failed_count');
            $errorMsg = $resData['error'] ?? 'Delivery failed';
            $logEntry['status'] = 'failed';
            $logEntry['error'] = $errorMsg;

            $currentLogs = $campaign->logs ?? [];
            $currentLogs[] = $logEntry;
            $campaign->logs = $currentLogs;
            $campaign->save();

            return response()->json(['success' => false, 'status' => 'failed', 'error' => $errorMsg]);
        } catch (\Exception $e) {
            $campaign->increment('failed_count');
            return response()->json(['success' => false, 'status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->status = $request->status ?: 'completed';
        $campaign->save();

        return response()->json(['success' => true]);
    }

    public function delete($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();

        $notify[] = ['success', 'Campaign deleted successfully'];
        return back()->withNotify($notify);
    }
}
