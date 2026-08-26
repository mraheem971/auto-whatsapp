<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'All Contacts';
        $query = Contact::query();

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('phone_number', 'LIKE', "%$search%")
                  ->orWhere('group_name', 'LIKE', "%$search%");
            });
        }

        $contacts = $query->latest()->paginate(getPaginate());
        return view('admin.contact.index', compact('pageTitle', 'contacts'));
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
            'email'        => 'nullable|email|max:150',
        ]);

        $cleanedPhone = preg_replace('/[^0-9]/', '', $request->phone_number);

        $contact = new Contact();
        $contact->admin_id     = auth('admin')->id() ?? 1;
        $contact->name         = $request->name;
        $contact->phone_number = $cleanedPhone;
        $contact->group_name   = $request->group_name;
        $contact->email        = $request->email;
        $contact->save();

        $notify[] = ['success', 'Contact added successfully'];
        return redirect()->route('admin.contacts.index')->withNotify($notify);
    }

    public function delete(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        $notify[] = ['success', 'Contact removed successfully'];
        return back()->withNotify($notify);
    }
}
