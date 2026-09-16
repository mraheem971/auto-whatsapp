<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\WhatsappAccount;
use App\Services\BaileysClient;
use Illuminate\Http\Request;

class UserContactController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'My WhatsApp Contacts & Audience';
        $user = auth()->user();
        $query = Contact::where('user_id', $user->id)->where('type', 'contact');

        if ($request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
            });
        }

        if ($request->list_id) {
            $query->where('contact_list_id', $request->list_id);
        }

        $contacts = $query->latest()->paginate(getPaginate());
        $contactLists = ContactList::where('user_id', $user->id)->withCount('contacts')->latest()->get();
        $connectedAccounts = WhatsappAccount::where('user_id', $user->id)->active()->latest()->get();
        $totalContacts = Contact::where('user_id', $user->id)->where('type', 'contact')->count();

        return view('Template::user.contacts.index', compact(
            'pageTitle',
            'contacts',
            'contactLists',
            'connectedAccounts',
            'totalContacts'
        ));
    }

    public function listsIndex()
    {
        $pageTitle = 'My Contact Lists';
        $user = auth()->user();
        $lists = ContactList::where('user_id', $user->id)->withCount('contacts')->latest()->paginate(getPaginate());

        return view('Template::user.contacts.lists', compact('pageTitle', 'lists'));
    }

    public function listStore(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $list = new ContactList();
        $list->user_id     = $user->id;
        $list->name        = $request->name;
        $list->description = $request->description;
        $list->save();

        $notify[] = ['success', 'Contact list created successfully!'];
        return back()->withNotify($notify);
    }

    public function storeContact(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'phone_number'    => 'required|string|max:50',
            'contact_list_id' => 'nullable|exists:contact_lists,id',
        ]);

        $user = auth()->user();
        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone_number);

        $contact = new Contact();
        $contact->user_id         = $user->id;
        $contact->name            = $request->name;
        $contact->phone_number    = $cleanPhone;
        $contact->target_jid      = "{$cleanPhone}@s.whatsapp.net";
        $contact->type            = 'contact';
        $contact->contact_list_id = $request->contact_list_id;
        $contact->save();

        $notify[] = ['success', 'Contact saved successfully!'];
        return back()->withNotify($notify);
    }

    public function syncWhatsApp(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $user = auth()->user();
        $account = WhatsappAccount::where('user_id', $user->id)->where('session_id', $request->session_id)->firstOrFail();

        $res = BaileysClient::get("api/contacts/{$account->session_id}", 15);

        if ($res && isset($res['status']) && $res['status'] === 'success' && !empty($res['contacts'])) {
            $synced = 0;
            foreach ($res['contacts'] as $item) {
                $phone = $item['phone'] ?? '';
                if (!$phone) continue;

                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                Contact::updateOrCreate(
                    [
                        'user_id'      => $user->id,
                        'phone_number' => $cleanPhone,
                    ],
                    [
                        'name'       => $item['name'] ?? "+{$cleanPhone}",
                        'target_jid' => "{$cleanPhone}@s.whatsapp.net",
                        'type'       => 'contact',
                    ]
                );
                $synced++;
            }

            $notify[] = ['success', "Synced {$synced} contacts from WhatsApp account!"];
            return back()->withNotify($notify);
        }

        $notify[] = ['error', 'Could not sync contacts. Make sure WhatsApp is active and online.'];
        return back()->withNotify($notify);
    }

    public function deleteContact($id)
    {
        $user = auth()->user();
        $contact = Contact::where('user_id', $user->id)->findOrFail($id);
        $contact->delete();

        $notify[] = ['success', 'Contact removed.'];
        return back()->withNotify($notify);
    }

    public function listDelete($id)
    {
        $user = auth()->user();
        $list = ContactList::where('user_id', $user->id)->findOrFail($id);
        Contact::where('user_id', $user->id)->where('contact_list_id', $list->id)->update(['contact_list_id' => null]);
        $list->delete();

        $notify[] = ['success', 'Contact list deleted.'];
        return back()->withNotify($notify);
    }

    public function importGroupsList(Request $request)
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

        $user = auth()->user();

        $list = ContactList::firstOrCreate(
            ['user_id' => $user->id, 'name' => trim($request->list_name)],
            ['type' => 'groups', 'description' => 'Imported WhatsApp Groups']
        );

        $imported = 0;
        $skipped = 0;

        foreach ($groupsData as $g) {
            $gData = is_string($g) ? json_decode($g, true) : $g;
            if (!$gData) continue;

            $groupId = $gData['id'] ?? '';
            $groupName = $gData['subject'] ?? $gData['name'] ?? 'WhatsApp Group';

            if (empty($groupId)) continue;

            $exists = Contact::where('user_id', $user->id)
                ->where('contact_list_id', $list->id)
                ->where('group_id', $groupId)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $contact = new Contact();
            $contact->user_id         = $user->id;
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

        $user = auth()->user();
        $sourceGroupName = $request->source_group_name ?: 'WhatsApp Group';
        $sourceGroupId = $request->source_group_id;

        $list = ContactList::firstOrCreate(
            ['user_id' => $user->id, 'name' => trim($request->list_name)],
            ['type' => 'contacts', 'description' => "Members extracted from group '{$sourceGroupName}'"]
        );

        $imported = 0;
        $skipped = 0;

        foreach ($participantsData as $p) {
            $pData = is_string($p) ? json_decode($p, true) : $p;
            if (!$pData) continue;

            $phone = preg_replace('/[^0-9]/', '', $pData['phone'] ?? $pData['id'] ?? '');
            if (empty($phone)) continue;

            $name = !empty($pData['name']) && !str_starts_with($pData['name'], '+')
                ? $pData['name']
                : (!empty($pData['notify']) ? $pData['notify'] : "+{$phone}");

            $targetJid = !empty($pData['id']) && str_contains($pData['id'], '@') ? $pData['id'] : "{$phone}@s.whatsapp.net";

            $exists = Contact::where('user_id', $user->id)
                ->where('contact_list_id', $list->id)
                ->where('phone_number', $phone)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $contact = new Contact();
            $contact->user_id         = $user->id;
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
            'message'  => "Extracted {$imported} members into Contact List \"{$list->name}\"" . ($skipped > 0 ? " ({$skipped} already existed)" : "")
        ]);
    }
}
