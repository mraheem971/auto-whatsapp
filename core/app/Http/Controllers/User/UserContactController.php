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
}
