<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\WhatsappAccount;
use App\Services\BaileysClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserWhatsAppController extends Controller
{
    public function index()
    {
        $pageTitle = 'My WhatsApp Accounts';
        $user = auth()->user();
        $accounts = WhatsappAccount::where('user_id', $user->id)->latest()->paginate(getPaginate());
        $activeCount = WhatsappAccount::where('user_id', $user->id)->active()->count();
        $plan = $user->currentPlan();
        $contactLists = ContactList::where('user_id', $user->id)->latest()->get();

        return view('Template::user.whatsapp.index', compact('pageTitle', 'accounts', 'activeCount', 'plan', 'contactLists'));
    }

    public function create()
    {
        $pageTitle = 'Link New WhatsApp Account';
        $user = auth()->user();
        $plan = $user->currentPlan();
        $currentCount = WhatsappAccount::where('user_id', $user->id)->active()->count();

        if ($plan && $currentCount >= $plan->account_limit) {
            $notify[] = ['warning', "You have reached your plan limit of {$plan->account_limit} WhatsApp account(s). Please upgrade your plan to link more accounts."];
            return redirect()->route('user.plans.index')->withNotify($notify);
        }

        return view('Template::user.whatsapp.create', compact('pageTitle', 'plan'));
    }

    public function initSession(Request $request)
    {
        $request->validate([
            'account_name'   => 'required|string|max:100',
            'pairing_method' => 'nullable|in:qr,code',
            'phone_number'   => 'nullable|string|max:50',
        ]);

        $user = auth()->user();
        $plan = $user->currentPlan();
        $currentCount = WhatsappAccount::where('user_id', $user->id)->active()->count();

        if ($plan && $currentCount >= $plan->account_limit) {
            return response()->json([
                'status' => 'error',
                'error'  => "Account limit reached ({$plan->account_limit}). Please upgrade your plan.",
            ], 403);
        }

        $accountName   = $request->account_name;
        $pairingMethod = $request->pairing_method ?: 'qr';
        $phoneNumber   = $request->phone_number ? preg_replace('/[^0-9]/', '', $request->phone_number) : null;

        if ($pairingMethod === 'code' && empty($phoneNumber)) {
            return response()->json([
                'status' => 'error',
                'error'  => 'Please enter your WhatsApp phone number with country code for pairing code.',
            ], 422);
        }

        $sessionId = 'usr_' . $user->id . '_' . time() . '_' . Str::random(6);

        // Remove stale pending accounts for this user
        $stalePending = WhatsappAccount::where('user_id', $user->id)->where('status', 0)->get();
        foreach ($stalePending as $stale) {
            $staleDir = base_path('../baileys-service/sessions/' . $stale->session_id);
            if (is_dir($staleDir)) {
                \Illuminate\Support\Facades\File::deleteDirectory($staleDir);
            }
            try {
                BaileysClient::post("api/sessions/delete/{$stale->session_id}", [], 3);
            } catch (\Exception $e) {}
            $stale->delete();
        }

        $account = new WhatsappAccount();
        $account->user_id      = $user->id;
        $account->session_id   = $sessionId;
        $account->account_name = $accountName;
        if ($phoneNumber) {
            $account->phone_number = $phoneNumber;
        }
        $account->status = 0;
        $account->save();

        $payload = [
            'sessionId'     => $sessionId,
            'accountName'   => $accountName,
            'pairingMethod' => $pairingMethod,
            'fresh'         => true,
        ];
        if ($phoneNumber) {
            $payload['phoneNumber'] = $phoneNumber;
        }

        try {
            $res = BaileysClient::post('api/sessions/start', $payload, 20);

            if ($res && $res->successful()) {
                $data = $res->json();
                if (!empty($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                    $account->delete();
                    return response()->json([
                        'status' => 'error',
                        'error'  => $data['error'] ?? 'WhatsApp microservice returned an error.',
                    ], 400);
                }

                return response()->json([
                    'status'        => 'success',
                    'sessionId'     => $sessionId,
                    'accountName'   => $accountName,
                    'pairingMethod' => $pairingMethod,
                    'pairingCode'   => $data['pairingCode'] ?? null,
                    'qr'            => $data['qr'] ?? null,
                    'qrImage'       => $data['qrImage'] ?? null,
                    'message'       => $pairingMethod === 'code' ? 'Enter the pairing code in WhatsApp on your phone' : 'Scan the QR code with WhatsApp',
                ]);
            } else {
                $account->delete();
                $err = $res ? ($res->json('error') ?: 'Server returned code ' . $res->status()) : 'Failed to connect to WhatsApp microservice. Please check node server status.';
                return response()->json([
                    'status' => 'error',
                    'error'  => $err,
                ], 500);
            }
        } catch (\Exception $e) {
            $account->delete();
            return response()->json([
                'status' => 'error',
                'error'  => 'Microservice connection failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sessionStatus($sessionId)
    {
        $user = auth()->user();
        $account = WhatsappAccount::where('user_id', $user->id)->where('session_id', $sessionId)->first();

        if (!$account) {
            return response()->json(['status' => 'not_found'], 404);
        }

        try {
            $res = BaileysClient::get("api/sessions/status/{$sessionId}", [], 8);

            if ($res && $res->successful()) {
                $data = $res->json();
                $status = $data['status'] ?? 'waiting';

                if ($status === 'connected') {
                    $userData = $data['user'] ?? [];
                    $account->status = 1;
                    $account->phone_number = $userData['phone'] ?? $account->phone_number;
                    $account->jid = $userData['id'] ?? $account->jid;
                    $account->profile_name = $userData['name'] ?? $account->profile_name;
                    $account->last_connected_at = now();
                    $account->save();
                }

                return response()->json([
                    'status'        => $status,
                    'sessionId'     => $sessionId,
                    'pairingMethod' => $data['pairingMethod'] ?? 'qr',
                    'pairingCode'   => $data['pairingCode'] ?? null,
                    'pairingError'  => $data['pairingError'] ?? null,
                    'qr'            => $data['qr'] ?? null,
                    'qrImage'       => $data['qrImage'] ?? null,
                    'user'          => $data['user'] ?? null,
                ]);
            }

            return response()->json(['status' => 'waiting']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'waiting']);
        }
    }

    public function testSendMessage(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'recipient'  => 'required|string',
            'message'    => 'required|string',
        ]);

        $user = auth()->user();
        $account = WhatsappAccount::where('user_id', $user->id)->where('session_id', $request->session_id)->firstOrFail();

        $res = BaileysClient::post('api/messages/send', [
            'sessionId' => $account->session_id,
            'recipient' => preg_replace('/[^0-9]/', '', $request->recipient),
            'message'   => $request->message,
        ], 10);

        if ($res && isset($res['status']) && $res['status'] === 'success') {
            return response()->json(['success' => true, 'message' => 'Test message sent successfully!']);
        }

        return response()->json(['success' => false, 'message' => $res['error'] ?? 'Failed to send message.'], 400);
    }

    public function extractGroups($sessionId)
    {
        $user = auth()->user();
        $account = WhatsappAccount::where('user_id', $user->id)->where('session_id', $sessionId)->first();
        if (!$account) {
            return response()->json(['success' => false, 'error' => 'WhatsApp account session not found.'], 404);
        }

        if ($account->status != 1) {
            return response()->json([
                'success' => false,
                'error'   => "WhatsApp account '{$account->account_name}' is not currently connected. Please ensure it is online."
            ], 400);
        }

        try {
            $response = BaileysClient::get("api/groups/{$sessionId}", [], 25);

            if ($response && $response->successful()) {
                $data = $response->json();
                return response()->json($data);
            }

            $errMsg = $response ? ($response->json()['error'] ?? 'WhatsApp service could not fetch groups.') : 'Baileys microservice is unreachable.';
            return response()->json(['success' => false, 'error' => $errMsg], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to extract WhatsApp groups: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function extractContacts(Request $request, $sessionId)
    {
        $user = auth()->user();
        $account = WhatsappAccount::where('user_id', $user->id)->where('session_id', $sessionId)->first();
        if (!$account) {
            return response()->json(['success' => false, 'error' => 'WhatsApp account session not found.'], 404);
        }

        if ($account->status != 1) {
            return response()->json([
                'success' => false,
                'error'   => "WhatsApp account is not connected. Please connect first."
            ], 400);
        }

        try {
            $response = BaileysClient::get("api/contacts/{$sessionId}", ['mode' => 'contacts_only'], 25);

            if ($response && $response->successful()) {
                $data = $response->json();
                $contacts = $data['contacts'] ?? [];

                $listId = $request->contact_list_id;
                if ($request->new_list_name) {
                    $newList = new ContactList();
                    $newList->user_id = $user->id;
                    $newList->name = $request->new_list_name;
                    $newList->save();
                    $listId = $newList->id;
                }

                $savedCount = 0;
                foreach ($contacts as $c) {
                    $phone = $c['phone'] ?? '';
                    if (!$phone) continue;
                    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

                    Contact::updateOrCreate(
                        [
                            'user_id'      => $user->id,
                            'phone_number' => $cleanPhone,
                        ],
                        [
                            'name'            => $c['name'] ?? "+{$cleanPhone}",
                            'target_jid'      => "{$cleanPhone}@s.whatsapp.net",
                            'type'            => 'contact',
                            'contact_list_id' => $listId,
                        ]
                    );
                    $savedCount++;
                }

                return response()->json([
                    'success'    => true,
                    'message'    => "Successfully extracted and saved {$savedCount} contacts!",
                    'savedCount' => $savedCount
                ]);
            }

            return response()->json(['success' => false, 'error' => 'Could not fetch contacts from WhatsApp.'], 400);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function saveExtractedGroupToList(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'group_jid'  => 'required|string',
        ]);

        $user = auth()->user();
        $account = WhatsappAccount::where('user_id', $user->id)->where('session_id', $request->session_id)->firstOrFail();

        $listName = $request->list_name ?: ($request->group_name ?: 'Extracted WhatsApp Group');
        $list = new ContactList();
        $list->user_id     = $user->id;
        $list->name        = $listName;
        $list->description = 'Extracted from group ' . ($request->group_name ?: $request->group_jid) . ' via ' . $account->account_name;
        $list->save();

        Contact::updateOrCreate(
            [
                'user_id'    => $user->id,
                'target_jid' => $request->group_jid,
            ],
            [
                'name'            => $request->group_name ?: 'WhatsApp Group',
                'phone_number'    => preg_replace('/[^0-9]/', '', explode('@', $request->group_jid)[0]),
                'type'            => 'group',
                'contact_list_id' => $list->id,
            ]
        );

        $savedMembers = 0;
        $members = json_decode($request->members_json, true) ?: [];

        foreach ($members as $m) {
            $phone = $m['phone'] ?? (isset($m['id']) ? preg_replace('/[^0-9]/', '', explode('@', $m['id'])[0]) : null);
            if (!$phone) continue;
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

            Contact::updateOrCreate(
                [
                    'user_id'         => $user->id,
                    'phone_number'    => $cleanPhone,
                    'contact_list_id' => $list->id,
                ],
                [
                    'name'       => $m['name'] ?? "+{$cleanPhone}",
                    'target_jid' => "{$cleanPhone}@s.whatsapp.net",
                    'type'       => 'contact',
                ]
            );
            $savedMembers++;
        }

        return response()->json([
            'success'      => true,
            'message'      => "Saved group and {$savedMembers} member(s) to Contact List '{$listName}'!",
            'list_id'      => $list->id,
            'savedMembers' => $savedMembers
        ]);
    }

    public function delete($id)
    {
        $user = auth()->user();
        $account = WhatsappAccount::where('user_id', $user->id)->findOrFail($id);

        try {
            BaileysClient::post("api/sessions/delete/{$account->session_id}", [], 5);
        } catch (\Exception $e) {}

        $sPath = base_path('../baileys-service/sessions/' . $account->session_id);
        if (is_dir($sPath)) {
            \Illuminate\Support\Facades\File::deleteDirectory($sPath);
        }

        $account->delete();

        $notify[] = ['success', 'WhatsApp account disconnected and removed.'];
        return back()->withNotify($notify);
    }
}
