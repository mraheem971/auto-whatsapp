<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class UserTemplateController extends Controller
{
    public function index()
    {
        $pageTitle = 'My Message Templates';
        $user = auth()->user();
        $templates = MessageTemplate::where('user_id', $user->id)->latest()->paginate(getPaginate());
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
            'name'    => 'required|string|max:150',
            'type'    => 'required|in:text,image,video,document,template_button',
            'message' => 'required|string',
        ]);

        $template = new MessageTemplate();
        $template->user_id   = $user->id;
        $template->name      = $request->name;
        $template->type      = $request->type;
        $template->message   = $request->message;
        $template->media_url = $request->media_url;
        $template->save();

        $notify[] = ['success', 'Message template created successfully!'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $template = MessageTemplate::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'name'    => 'required|string|max:150',
            'type'    => 'required|in:text,image,video,document,template_button',
            'message' => 'required|string',
        ]);

        $template->name      = $request->name;
        $template->type      = $request->type;
        $template->message   = $request->message;
        $template->media_url = $request->media_url;
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
