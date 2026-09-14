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

        $isEngineOnline = false;
        try {
            $health = BaileysClient::get('health', [], 3);
            if ($health && $health->successful()) {
                $data = $health->json();
                $isEngineOnline = (isset($data['status']) && $data['status'] === 'healthy');
            }
        } catch (\Throwable $e) {
            $isEngineOnline = false;
        }

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

        if ($res && $res->successful()) {
            $data = $res->json();
            if (isset($data['status']) && $data['status'] === 'success') {
                $notify[] = ['success', "Test message successfully dispatched through Gateway (+{$account->phone_number})!"];
                return back()->withNotify($notify);
            }
        }

        $errorMsg = $res ? ($res->json('message') ?? 'Gateway dispatch failed.') : 'Gateway connection error. Ensure WhatsApp is online.';
        $notify[] = ['error', $errorMsg];
        return back()->withNotify($notify);
    }
}