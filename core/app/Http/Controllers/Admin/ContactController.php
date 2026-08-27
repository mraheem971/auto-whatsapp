<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContactController extends Controller
{
    protected $baileysUrl = 'http://127.0.0.1:3000';

    public function index(Request $request)
    {
        $pageTitle = 'Contact List';
        $query = Contact::query();

        if ($request->group) {
            $query->where('group_name', $request->group);
        }

        if ($request->group_id) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('phone_number', 'LIKE', "%$search%")
                  ->orWhere('group_name', 'LIKE', "%$search%")
                  ->orWhere('group_id', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        $contacts = $query->latest()->paginate(getPaginate());

        // Groups summary
        $groups = Contact::selectRaw('group_name, group_id, count(*) as total_count')
            ->groupBy('group_name', 'group_id')
            ->orderByDesc('total_count')
            ->get();

        $totalContactsCount = Contact::count();
        $selectedGroup = $request->group ?? $request->group_id ?? null;
        $connectedAccounts = WhatsappAccount::active()->latest()->get();

        return view('admin.contact.index', compact('pageTitle', 'contacts', 'groups', 'totalContactsCount', 'selectedGroup', 'connectedAccounts'));
    }

    public function create()
    {
        $pageTitle = 'Add New Contact';
        return view('admin.contact.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'phone_number' => 'required|string|max:50',
            'group_name'   => 'nullable|string|max:100',
            'group_id'     => 'nullable|string|max:100',
            'email'        => 'nullable|email|max:150',
        ]);

        $cleanedPhone = preg_replace('/[^0-9]/', '', $request->phone_number);

        $exists = Contact::where('phone_number', $cleanedPhone)->first();
        if ($exists) {
            $notify[] = ['error', 'A contact with this phone number already exists'];
            return back()->withInput()->withNotify($notify);
        }

        $contact = new Contact();
        $contact->admin_id     = auth('admin')->id() ?? 1;
        $contact->name         = $request->name;
        $contact->phone_number = $cleanedPhone;
        $contact->group_name   = $request->group_name;
        $contact->group_id     = $request->group_id;
        $contact->email        = $request->email;
        $contact->save();

        $notify[] = ['success', 'Contact added successfully'];
        return redirect()->route('admin.contacts.index')->withNotify($notify);
    }

    public function sync()
    {
        $pageTitle = 'Sync Contacts from WhatsApp';
        $connectedAccounts = WhatsappAccount::active()->latest()->get();
        return view('admin.contact.sync', compact('pageTitle', 'connectedAccounts'));
    }

    public function fetchWhatsAppContacts($sessionId)
    {
        try {
            $response = Http::timeout(25)->get("{$this->baileysUrl}/api/contacts/{$sessionId}");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            $err = $response->json()['error'] ?? 'Failed to fetch WhatsApp contacts.';
            return response()->json(['success' => false, 'error' => $err], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Baileys service connection error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importContacts(Request $request)
    {
        $request->validate([
            'contacts'   => 'required|array|min:1',
            'contacts.*' => 'required|string',
        ]);

        $adminId = auth('admin')->id() ?? 1;
        $imported = 0;
        $skipped = 0;

        foreach ($request->contacts as $contactJson) {
            $data = json_decode($contactJson, true);
            if (!$data || empty($data['phone'])) {
                continue;
            }

            $phone = preg_replace('/[^0-9]/', '', $data['phone']);
            if (empty($phone)) {
                continue;
            }

            $name = !empty($data['name']) ? $data['name'] : "+{$phone}";
            $group = !empty($data['groupName']) ? $data['groupName'] : 'WhatsApp Sync';
            $groupId = !empty($data['id']) ? $data['id'] : null;

            $exists = Contact::where('phone_number', $phone)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            $contact = new Contact();
            $contact->admin_id     = $adminId;
            $contact->name         = $name;
            $contact->phone_number = $phone;
            $contact->group_name   = $group;
            $contact->group_id     = $groupId;
            $contact->save();

            $imported++;
        }

        return response()->json([
            'success'  => true,
            'imported' => $imported,
            'skipped'  => $skipped,
            'message'  => "Successfully imported {$imported} contacts" . ($skipped > 0 ? " ({$skipped} duplicates skipped)" : "")
        ]);
    }

    public function importGroupContacts(Request $request)
    {
        $request->validate([
            'group_name'   => 'required|string|max:150',
            'group_id'     => 'required|string|max:100',
            'participants' => 'required|array|min:1',
        ]);

        $adminId = auth('admin')->id() ?? 1;
        $groupName = $request->group_name;
        $groupId = $request->group_id;
        $imported = 0;
        $skipped = 0;

        foreach ($request->participants as $p) {
            $rawPhone = is_array($p) ? ($p['phone'] ?? $p['id'] ?? '') : $p;
            $phone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (empty($phone)) {
                continue;
            }

            $exists = Contact::where('phone_number', $phone)->where('group_id', $groupId)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            $contact = new Contact();
            $contact->admin_id     = $adminId;
            $contact->name         = "+{$phone}";
            $contact->phone_number = $phone;
            $contact->group_name   = $groupName;
            $contact->group_id     = $groupId;
            $contact->save();

            $imported++;
        }

        return response()->json([
            'success'  => true,
            'imported' => $imported,
            'skipped'  => $skipped,
            'message'  => "Imported {$imported} members from \"{$groupName}\"" . ($skipped > 0 ? " ({$skipped} already in group)" : "")
        ]);
    }

    public function deleteGroup(Request $request)
    {
        $request->validate([
            'group_id'   => 'nullable|string',
            'group_name' => 'nullable|string',
        ]);

        $query = Contact::query();
        if ($request->group_id) {
            $query->where('group_id', $request->group_id);
        } elseif ($request->group_name) {
            $query->where('group_name', $request->group_name);
        } else {
            $notify[] = ['error', 'No group specified'];
            return back()->withNotify($notify);
        }

        $count = $query->count();
        $query->delete();

        $notify[] = ['success', "Removed {$count} contacts in group"];
        return back()->withNotify($notify);
    }

    public function delete(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        $notify[] = ['success', 'Contact removed successfully'];
        return back()->withNotify($notify);
    }
}
