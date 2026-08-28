<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContactController extends Controller
{
    protected $baileysUrl = 'http://127.0.0.1:3000';

    // 1. All Contact Lists View
    public function listsIndex(Request $request)
    {
        $pageTitle = 'Contact Lists';
        $query = ContactList::withCount(['contacts', 'groupContacts', 'directContacts']);

        if ($request->search) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%$search%")
                  ->orWhere('description', 'LIKE', "%$search%");
        }

        $lists = $query->latest()->paginate(getPaginate());
        $connectedAccounts = WhatsappAccount::active()->latest()->get();

        return view('admin.contact.lists.index', compact('pageTitle', 'lists', 'connectedAccounts'));
    }

    // 2. View Specific Contact List & its Members / Groups
    public function listShow($id, Request $request)
    {
        $list = ContactList::withCount(['contacts', 'groupContacts', 'directContacts'])->findOrFail($id);
        $pageTitle = 'Contact List: ' . $list->name;

        $query = Contact::where('contact_list_id', $list->id);

        if ($request->type) {
            $query->where('type', $request->type);
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
        $connectedAccounts = WhatsappAccount::active()->latest()->get();

        return view('admin.contact.lists.show', compact('pageTitle', 'list', 'contacts', 'connectedAccounts'));
    }

    // 3. Create Custom Named Contact List
    public function listStore(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'type'        => 'required|string|in:contacts,groups,mixed',
            'description' => 'nullable|string',
        ]);

        $list = new ContactList();
        $list->admin_id    = auth('admin')->id() ?? 1;
        $list->name        = $request->name;
        $list->type        = $request->type;
        $list->description = $request->description;
        $list->save();

        $notify[] = ['success', "Contact List '{$list->name}' created successfully!"];
        return back()->withNotify($notify);
    }

    // 4. Update / Rename Contact List
    public function listUpdate(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $list = ContactList::findOrFail($id);
        $list->name        = $request->name;
        $list->description = $request->description;
        $list->save();

        $notify[] = ['success', "Contact List updated successfully!"];
        return back()->withNotify($notify);
    }

    // 5. Delete Contact List
    public function listDelete($id)
    {
        $list = ContactList::findOrFail($id);
        // Delete all child contacts
        Contact::where('contact_list_id', $list->id)->delete();
        $list->delete();

        $notify[] = ['success', "Contact List and all its members deleted successfully!"];
        return redirect()->route('admin.contact.lists.index')->withNotify($notify);
    }

    // 6. Import Extracted Groups into a Named List (No. 1 in User Request)
    public function importGroupsToList(Request $request)
    {
        $request->validate([
            'list_name' => 'required|string|max:150',
        ]);

        $groupsData = $request->groups;
        if (is_string($groupsData)) {
            $groupsData = json_decode($groupsData, true);
        }

        if (!is_array($groupsData) || empty($groupsData)) {
            return response()->json(['success' => false, 'error' => 'No groups provided to import.'], 400);
        }

        $adminId = auth('admin')->id() ?? 1;

        // Find or create the named list
        $list = ContactList::firstOrCreate(
            ['admin_id' => $adminId, 'name' => trim($request->list_name)],
            ['type' => 'groups', 'description' => 'Extracted WhatsApp Groups']
        );

        $imported = 0;
        $skipped = 0;

        foreach ($groupsData as $gJson) {
            $g = is_string($gJson) ? json_decode($gJson, true) : $gJson;
            if (!$g || empty($g['id'])) continue;

            $groupId = $g['id'];
            $groupName = !empty($g['subject']) ? $g['subject'] : (!empty($g['name']) ? $g['name'] : 'WhatsApp Group');

            $exists = Contact::where('contact_list_id', $list->id)
                ->where('group_id', $groupId)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $contact = new Contact();
            $contact->admin_id        = $adminId;
            $contact->contact_list_id = $list->id;
            $contact->type            = 'group';
            $contact->name            = $groupName;
            $contact->phone_number    = $groupId;
            $contact->target_jid      = $groupId;
            $contact->group_name      = $groupName;
            $contact->group_id        = $groupId;
            $contact->save();

            $imported++;
        }

        return response()->json([
            'success'  => true,
            'list_id'  => $list->id,
            'imported' => $imported,
            'skipped'  => $skipped,
            'message'  => "Saved {$imported} groups into Contact List \"{$list->name}\"" . ($skipped > 0 ? " ({$skipped} already existed)" : "")
        ]);
    }

    // 7. Extract Members from a Group into a Named List (No. 2 in User Request)
    public function extractGroupMembersToList(Request $request)
    {
        $request->validate([
            'list_name'         => 'required|string|max:150',
            'source_group_id'   => 'required|string',
            'source_group_name' => 'nullable|string',
        ]);

        $participantsData = $request->participants;
        if (is_string($participantsData)) {
            $participantsData = json_decode($participantsData, true);
        }

        if (!is_array($participantsData) || empty($participantsData)) {
            return response()->json(['success' => false, 'error' => 'No participants provided to extract.'], 400);
        }

        $adminId = auth('admin')->id() ?? 1;
        $sourceGroupName = $request->source_group_name ?: 'WhatsApp Group';
        $sourceGroupId = $request->source_group_id;

        // Find or create target contact list
        $list = ContactList::firstOrCreate(
            ['admin_id' => $adminId, 'name' => trim($request->list_name)],
            ['type' => 'contacts', 'description' => "Members extracted from group '{$sourceGroupName}'"]
        );

        $imported = 0;
        $skipped = 0;

        foreach ($participantsData as $p) {
            $pData = is_string($p) ? json_decode($p, true) : $p;
            if (!$pData) continue;

            $phone = preg_replace('/[^0-9]/', '', $pData['phone'] ?? $pData['id'] ?? '');
            if (empty($phone)) continue;

            // Real WhatsApp contact name resolution
            $name = !empty($pData['name']) && !str_starts_with($pData['name'], '+')
                ? $pData['name']
                : (!empty($pData['notify']) ? $pData['notify'] : "+{$phone}");

            $targetJid = !empty($pData['id']) && str_contains($pData['id'], '@') ? $pData['id'] : "{$phone}@s.whatsapp.net";

            $exists = Contact::where('contact_list_id', $list->id)
                ->where('phone_number', $phone)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $contact = new Contact();
            $contact->admin_id        = $adminId;
            $contact->contact_list_id = $list->id;
            $contact->type            = 'contact';
            $contact->name            = $name;
            $contact->phone_number    = $phone;
            $contact->target_jid      = $targetJid;
            $contact->group_name      = $sourceGroupName;
            $contact->group_id        = $sourceGroupId;
            $contact->save();

            $imported++;
        }

        return response()->json([
            'success'  => true,
            'list_id'  => $list->id,
            'imported' => $imported,
            'skipped'  => $skipped,
            'message'  => "Extracted {$imported} members into Contact List \"{$list->name}\"" . ($skipped > 0 ? " ({$skipped} duplicates skipped)" : "")
        ]);
    }

    // 8. Import Sync Contacts into a Named List (No. 3 in User Request)
    public function importContactsToList(Request $request)
    {
        $request->validate([
            'list_name' => 'required|string|max:150',
        ]);

        $contactsData = $request->contacts;
        if (is_string($contactsData)) {
            $contactsData = json_decode($contactsData, true);
        }

        if (!is_array($contactsData) || empty($contactsData)) {
            return response()->json(['success' => false, 'error' => 'No contacts provided to import.'], 400);
        }

        $adminId = auth('admin')->id() ?? 1;

        $list = ContactList::firstOrCreate(
            ['admin_id' => $adminId, 'name' => trim($request->list_name)],
            ['type' => 'mixed', 'description' => 'WhatsApp Sync Extraction']
        );

        $imported = 0;
        $skipped = 0;

        foreach ($contactsData as $itemJson) {
            $data = is_string($itemJson) ? json_decode($itemJson, true) : $itemJson;
            if (!$data) continue;

            $type = $data['type'] ?? 'contact';

            if ($type === 'group') {
                $groupId = $data['id'] ?? $data['groupId'] ?? '';
                if (empty($groupId)) continue;

                $groupName = !empty($data['name']) ? $data['name'] : 'WhatsApp Group';

                $exists = Contact::where('contact_list_id', $list->id)
                    ->where('group_id', $groupId)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $contact = new Contact();
                $contact->admin_id        = $adminId;
                $contact->contact_list_id = $list->id;
                $contact->type            = 'group';
                $contact->name            = $groupName;
                $contact->phone_number    = $groupId;
                $contact->target_jid      = $groupId;
                $contact->group_name      = $groupName;
                $contact->group_id        = $groupId;
                $contact->save();

                $imported++;
            } else {
                $phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
                if (empty($phone)) continue;

                $name = !empty($data['name']) ? $data['name'] : (!empty($data['notify']) ? $data['notify'] : "+{$phone}");
                $group = !empty($data['groupName']) ? $data['groupName'] : 'Direct Contact';
                $groupId = !empty($data['groupId']) ? $data['groupId'] : null;
                $targetJid = $data['target_jid'] ?? "{$phone}@s.whatsapp.net";

                $exists = Contact::where('contact_list_id', $list->id)
                    ->where('phone_number', $phone)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $contact = new Contact();
                $contact->admin_id        = $adminId;
                $contact->contact_list_id = $list->id;
                $contact->type            = 'contact';
                $contact->name            = $name;
                $contact->phone_number    = $phone;
                $contact->target_jid      = $targetJid;
                $contact->group_name      = $group;
                $contact->group_id        = $groupId;
                $contact->save();

                $imported++;
            }
        }

        return response()->json([
            'success'  => true,
            'list_id'  => $list->id,
            'imported' => $imported,
            'skipped'  => $skipped,
            'message'  => "Imported {$imported} items into \"{$list->name}\"" . ($skipped > 0 ? " ({$skipped} duplicates skipped)" : "")
        ]);
    }

    // 9. Manual Contact Add View
    public function create()
    {
        $pageTitle = 'Add New Contact';
        $lists = ContactList::latest()->get();
        return view('admin.contact.create', compact('pageTitle', 'lists'));
    }

    // 10. Manual Contact Store
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:150',
            'phone_number'    => 'required|string|max:50',
            'contact_list_id' => 'nullable|integer',
            'new_list_name'   => 'nullable|string|max:150',
            'group_name'      => 'nullable|string|max:100',
            'email'           => 'nullable|email|max:150',
        ]);

        $adminId = auth('admin')->id() ?? 1;
        $cleanedPhone = preg_replace('/[^0-9]/', '', $request->phone_number);

        // Determine contact list
        $listId = $request->contact_list_id;
        if (!empty($request->new_list_name)) {
            $list = ContactList::firstOrCreate(
                ['admin_id' => $adminId, 'name' => trim($request->new_list_name)],
                ['type' => 'contacts']
            );
            $listId = $list->id;
        }

        $exists = Contact::when($listId, fn($q) => $q->where('contact_list_id', $listId))
            ->where('phone_number', $cleanedPhone)
            ->first();

        if ($exists) {
            $notify[] = ['error', 'A contact with this phone number already exists in this list'];
            return back()->withInput()->withNotify($notify);
        }

        $contact = new Contact();
        $contact->admin_id        = $adminId;
        $contact->contact_list_id = $listId;
        $contact->type            = 'contact';
        $contact->name            = $request->name;
        $contact->phone_number    = $cleanedPhone;
        $contact->target_jid      = "{$cleanedPhone}@s.whatsapp.net";
        $contact->group_name      = $request->group_name;
        $contact->email           = $request->email;
        $contact->save();

        $notify[] = ['success', 'Contact added successfully'];
        return redirect()->route('admin.contact.lists.index')->withNotify($notify);
    }

    // 11. CSV / Excel Import View (No. 5 in User Request)
    public function csvImportView()
    {
        $pageTitle = 'Import Contacts via CSV / Excel';
        $lists = ContactList::latest()->get();
        return view('admin.contact.csv_import', compact('pageTitle', 'lists'));
    }

    // 12. CSV Import Process
    public function csvImportProcess(Request $request)
    {
        $request->validate([
            'csv_file'        => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'contact_list_id' => 'nullable|integer',
            'new_list_name'   => 'nullable|string|max:150',
        ]);

        $adminId = auth('admin')->id() ?? 1;

        // Determine list
        $listId = $request->contact_list_id;
        if (!empty($request->new_list_name)) {
            $list = ContactList::firstOrCreate(
                ['admin_id' => $adminId, 'name' => trim($request->new_list_name)],
                ['type' => 'contacts', 'description' => 'CSV Imported List']
            );
            $listId = $list->id;
        }

        if (!$listId) {
            $list = ContactList::firstOrCreate(
                ['admin_id' => $adminId, 'name' => 'CSV Imported Contacts (' . date('Y-m-d') . ')'],
                ['type' => 'contacts']
            );
            $listId = $list->id;
        }

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $imported = 0;
        $skipped = 0;
        $isFirstRow = true;

        while (($row = fgetcsv($handle, 2000, ',')) !== false) {
            if ($isFirstRow) {
                $isFirstRow = false;
                // If header contains phone/name, skip header
                if (isset($row[0]) && (stripos($row[0], 'name') !== false || stripos($row[0], 'phone') !== false)) {
                    continue;
                }
            }

            $name = isset($row[0]) ? trim($row[0]) : '';
            $phoneRaw = isset($row[1]) ? trim($row[1]) : (isset($row[0]) ? trim($row[0]) : '');
            $email = isset($row[2]) ? trim($row[2]) : null;
            $groupName = isset($row[3]) ? trim($row[3]) : 'CSV Import';

            $phone = preg_replace('/[^0-9]/', '', $phoneRaw);
            if (empty($phone)) continue;

            if (empty($name)) {
                $name = "+{$phone}";
            }

            $exists = Contact::where('contact_list_id', $listId)
                ->where('phone_number', $phone)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $contact = new Contact();
            $contact->admin_id        = $adminId;
            $contact->contact_list_id = $listId;
            $contact->type            = 'contact';
            $contact->name            = $name;
            $contact->phone_number    = $phone;
            $contact->target_jid      = "{$phone}@s.whatsapp.net";
            $contact->group_name      = $groupName;
            $contact->email           = $email;
            $contact->save();

            $imported++;
        }
        fclose($handle);

        $notify[] = ['success', "Successfully imported {$imported} contacts into list!" . ($skipped > 0 ? " ({$skipped} duplicates skipped)" : "")];
        return redirect()->route('admin.contact.lists.show', $listId)->withNotify($notify);
    }

    // 13. Sync WhatsApp View
    public function sync()
    {
        $pageTitle = 'Sync Contacts & Groups from WhatsApp';
        $connectedAccounts = WhatsappAccount::active()->latest()->get();
        $lists = ContactList::latest()->get();
        return view('admin.contact.sync', compact('pageTitle', 'connectedAccounts', 'lists'));
    }

    // 14. Fetch WhatsApp Contacts / Groups API
    public function fetchWhatsAppContacts($sessionId)
    {
        try {
            $mode = request('mode', 'all');
            $response = Http::timeout(25)->get("{$this->baileysUrl}/api/contacts/{$sessionId}?mode={$mode}");

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

    // 15. Delete Single Contact
    public function delete($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        $notify[] = ['success', 'Contact removed successfully'];
        return back()->withNotify($notify);
    }
}
