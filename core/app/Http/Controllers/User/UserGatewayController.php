<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\WhatsappAccount;
use App\Services\BaileysClient;
use Illuminate\Http\Request;

class UserGatewayController extends Controller
{
    public function index()
    {
        $pageTitle = 'WhatsApp Gateway & API Hub';
        $user = auth()->user();
        $plan = $user->currentPlan();

        $accounts = WhatsappAccount::where('user_id', $user->id)->latest()->get();
        $activeCount = WhatsappAccount::where('user_id', $user->id)->where('status', 1)->count();

        $health = BaileysClient::get('health', 3);
        $isEngineOnline = ($health && isset($health['status']) && $health['status'] === 'healthy');

        return view('Template::user.gateway.index', compact(
            'pageTitle',
            'user',
            'plan',
            'accounts',
            'activeCount',
            'isEngineOnline'
        ));
    }

    public function testSend(Request $request)
    {
        $request->validate([
            'session_id'   => 'required|string',
            'phone_number' => 'required|string',
            'message'      => 'required|string',
        ]);

        $user = auth()->user();
        $account = WhatsappAccount::where('user_id', $user->id)->where('session_id', $request->session_id)->firstOrFail();

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone_number);
        $res = BaileysClient::post('api/send-message', [
            'sessionId' => $account->session_id,
            'receiver'  => $cleanPhone,
            'message'   => $request->message,
        ], 10);

        if ($res && isset($res['status']) && $res['status'] === 'success') {
            $notify[] = ['success', "Test message successfully dispatched through Gateway (+{$account->phone_number})!"];
            return back()->withNotify($notify);
        }

        $errorMsg = $res['message'] ?? 'Gateway dispatch failed. Ensure the account is connected and online.';
        $notify[] = ['error', $errorMsg];
        return back()->withNotify($notify);
    }
}