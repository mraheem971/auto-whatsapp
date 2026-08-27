<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Message Templates';
        $query = MessageTemplate::query();

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                  ->orWhere('message', 'LIKE', "%$search%")
                  ->orWhere('category', 'LIKE', "%$search%");
            });
        }

        $templates = $query->latest()->paginate(getPaginate());
        return view('admin.template.index', compact('pageTitle', 'templates'));
    }

    public function create()
    {
        $pageTitle = 'Create Message Template';
        return view('admin.template.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:150',
            'message'  => 'required|string',
            'category' => 'nullable|string|max:100',
        ]);

        $template = new MessageTemplate();
        $template->admin_id = auth('admin')->id() ?? 1;
        $template->title    = $request->title;
        $template->message  = $request->message;
        $template->category = $request->category ?: 'General';
        $template->save();

        $notify[] = ['success', 'Message template created successfully'];
        return redirect()->route('admin.templates.index')->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Message Template';
        $template = MessageTemplate::findOrFail($id);
        return view('admin.template.edit', compact('pageTitle', 'template'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'    => 'required|string|max:150',
            'message'  => 'required|string',
            'category' => 'nullable|string|max:100',
        ]);

        $template = MessageTemplate::findOrFail($id);
        $template->title    = $request->title;
        $template->message  = $request->message;
        $template->category = $request->category ?: 'General';
        $template->save();

        $notify[] = ['success', 'Message template updated successfully'];
        return redirect()->route('admin.templates.index')->withNotify($notify);
    }

    public function delete($id)
    {
        $template = MessageTemplate::findOrFail($id);
        $template->delete();

        $notify[] = ['success', 'Message template deleted successfully'];
        return back()->withNotify($notify);
    }

    public function listJson()
    {
        $templates = MessageTemplate::where('status', 1)->latest()->get(['id', 'title', 'message', 'category']);
        return response()->json([
            'success'   => true,
            'templates' => $templates
        ]);
    }
}
