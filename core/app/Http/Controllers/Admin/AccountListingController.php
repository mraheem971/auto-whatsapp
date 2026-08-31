<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappAccount;
use App\Services\BaileysClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountListingController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'All WhatsApp Accounts';
        $accounts  = WhatsappAccount::latest()->paginate(getPaginate());
        return view('admin.account_listing.index', compact('pageTitle', 'accounts'));
    }

    public function active(Request $request)
    {
        $pageTitle = 'Active WhatsApp Accounts';
        $accounts  = WhatsappAccount::active()->latest()->paginate(getPaginate());
        return view('admin.account_listing.index', compact('pageTitle', 'accounts'));
    }

    public function pending(Request $request)
    {
        $pageTitle = 'Pending WhatsApp Accounts';
        $accounts  = WhatsappAccount::pending()->latest()->paginate(getPaginate());
        return view('admin.account_listing.index', compact('pageTitle', 'accounts'));
    }

    public function create()
    {
        $pageTitle = 'Add WhatsApp Account';
        $connectedAccounts = WhatsappAccount::active()->latest()->get();
        return view('admin.account_listing.create', compact('pageTitle', 'connectedAccounts'));
    }

    public function initSession(Request $request)
    {
        $request->validate([
            'account_name' => 'required|string|max:100',
        ]);

        $accountName = $request->account_name;
        $sessionId   = 'wa_' . time() . '_' . Str::random(8);

        // Clean up previous unfinished pending accounts from database & disk
        $stalePending = WhatsappAccount::where('status', 0)->get();
        foreach ($stalePending as $stale) {
            $staleDir = base_path('../baileys-service/sessions/' . $stale->session_id);
            if (is_dir($staleDir)) {
                \Illuminate\Support\Facades\File::deleteDirectory($staleDir);
            }
            try {
                BaileysClient::post("api/sessions/delete/{$stale->session_id}", [], 3);
            } catch (\Exception $e) {}
            $stale->delete();
        }

        // Create new pending record
        $account = new WhatsappAccount();
        $account->session_id   = $sessionId;
        $account->account_name = $accountName;
        $account->admin_id     = auth('admin')->id() ?? 1;
        $account->status       = 0;
        $account->save();

        try {
            $response = BaileysClient::post('api/sessions/start', [
                'sessionId'   => $sessionId,
                'accountName' => $accountName,
                'fresh'       => true,
            ], 15);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'status'    => $data['status'] ?? 'initializing',
                    'sessionId' => $sessionId,
                    'qrImage'   => $data['qrImage'] ?? null,
                    'user'      => $data['user'] ?? null,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'error'  => 'Baileys service returned an error: ' . $response->body(),
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error'  => 'Could not connect to Baileys service. Auto-spawn initiated: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sessionStatus($sessionId)
    {
        try {
            $response = BaileysClient::get("api/sessions/status/{$sessionId}", [], 8);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['status'] ?? 'unknown';

                $account = WhatsappAccount::where('session_id', $sessionId)->first();

                if ($account && $status === 'connected') {
                    $userData = $data['user'] ?? [];
                    $account->status = 1;
                    $account->phone_number = $userData['phone'] ?? $account->phone_number;
                    $account->jid = $userData['id'] ?? $account->jid;
                    $account->profile_name = $userData['name'] ?? $account->profile_name;
                    $account->last_connected_at = now();
                    $account->save();
                }

                return response()->json([
                    'status'    => $status,
                    'sessionId' => $sessionId,
                    'qrImage'   => $data['qrImage'] ?? null,
                    'user'      => $data['user'] ?? null,
                    'account'   => $account,
                ]);
            }

            return response()->json(['status' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'error' => $e->getMessage()], 500);
        }
    }

    public function testSendMessage(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'receiver'   => 'required|string',
            'message'    => 'required|string',
        ]);

        try {
            $response = BaileysClient::post('api/messages/send', [
                'sessionId' => $request->session_id,
                'receiver'  => $request->receiver,
                'message'   => $request->message,
            ], 20);

            if ($response->successful()) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Test message sent successfully!',
                    'messageId' => $response->json()['messageId'] ?? null,
                ]);
            } else {
                $err = $response->json()['error'] ?? 'Failed to send message.';
                return response()->json(['status' => 'error', 'error' => $err], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error'  => 'Baileys service connection error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAccount(Request $request, $id)
    {
        $account = WhatsappAccount::findOrFail($id);
        $sessionId = $account->session_id;

        // 1. Notify Baileys service to kill active socket & delete credentials from disk
        if ($sessionId) {
            try {
                BaileysClient::post("api/sessions/delete/{$sessionId}", [], 5);
            } catch (\Exception $e) {
                // Log and continue to delete from DB
            }

            // 2. Double-check filesystem cleanup directly from PHP
            $sessionDir = base_path('../baileys-service/sessions/' . $sessionId);
            if (is_dir($sessionDir)) {
                try {
                    \Illuminate\Support\Facades\File::deleteDirectory($sessionDir);
                } catch (\Exception $e) {}
            }
        }

        // 3. Delete database record
        $account->delete();

        $notify[] = ['success', 'WhatsApp account and all session files deleted successfully!'];
        return back()->withNotify($notify);
    }
}
