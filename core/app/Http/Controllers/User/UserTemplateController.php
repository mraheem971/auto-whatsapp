<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class UserTemplateController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'My Message Templates';
        $user = auth()->user();
        $query = MessageTemplate::where('user_id', $user->id);

        if ($request->category || $request->type) {
            $cat = $request->category ?: $request->type;
            if ($cat === 'media') {
                $query->whereIn('category', ['image', 'video', 'document', 'media']);
                $pageTitle = 'Media Templates';
            } else {
                $query->where('category', $cat);
                $pageTitle = ucfirst($cat) . ' Templates';
            }
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                  ->orWhere('message', 'LIKE', "%$search%")
                  ->orWhere('category', 'LIKE', "%$search%");
            });
        }

        $templates = $query->latest()->paginate(getPaginate());
        $plan = $user->currentPlan();

        return view('Template::user.templates.index', compact('pageTitle', 'templates', 'plan'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $plan = $user->currentPlan();
        $currentCount = MessageTemplate::where('user_id', $user->id)->count();

        if ($plan && $currentCount >= $plan->template_limit) {
            $notify[] = ['warning', "Template limit reached ({$plan->template_limit}). Please upgrade your plan."];
            return back()->withNotify($notify);
        }

        $request->validate([
            'name'      => 'nullable|string|max:150',
            'title'     => 'nullable|string|max:150',
            'type'      => 'nullable|string',
            'category'  => 'nullable|string|max:100',
            'message'   => 'required|string',
            'media_url' => 'nullable|url|max:2000',
        ]);

        $title = $request->name ?: ($request->title ?: 'Untitled Template');
        $category = $request->category ?: ($request->type ?: 'text');

        $template = new MessageTemplate();
        $template->user_id   = $user->id;
        $template->title     = $title;
        $template->category  = $category;
        $template->message   = $request->message;
        $template->media_url = $request->media_url ?: null;
        $template->save();

        $notify[] = ['success', 'Message template created successfully!'];
        return back()->withNotify($notify);
    }

    public function edit(Request $request, $id)
    {
        $user = auth()->user();
        $template = MessageTemplate::where('user_id', $user->id)->findOrFail($id);

        if ($request->ajax() || $request->wantsJson() || $request->has('json') || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success'  => true,
                'template' => [
                    'id'        => $template->id,
                    'name'      => $template->name ?: $template->title,
                    'title'     => $template->title ?: $template->name,
                    'type'      => $template->type ?: ($template->category ?: 'text'),
                    'category'  => $template->category ?: ($template->type ?: 'text'),
                    'message'   => $template->message ?: '',
                    'media_url' => $template->media_url ?: '',
                ]
            ]);
        }

        return redirect()->route('user.templates.index', ['edit_id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $template = MessageTemplate::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'name'      => 'nullable|string|max:150',
            'title'     => 'nullable|string|max:150',
            'type'      => 'nullable|string',
            'category'  => 'nullable|string|max:100',
            'message'   => 'required|string',
            'media_url' => 'nullable|url|max:2000',
        ]);

        $title = $request->name ?: ($request->title ?: $template->title);
        $category = $request->category ?: ($request->type ?: $template->category);

        $template->title     = $title;
        $template->category  = $category;
        $template->message   = $request->message;
        $template->media_url = $request->media_url ?: null;
        $template->save();

        $notify[] = ['success', 'Message template updated successfully!'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $user = auth()->user();
        $template = MessageTemplate::where('user_id', $user->id)->findOrFail($id);
        $template->delete();

        $notify[] = ['success', 'Message template deleted.'];
        return back()->withNotify($notify);
    }
}
