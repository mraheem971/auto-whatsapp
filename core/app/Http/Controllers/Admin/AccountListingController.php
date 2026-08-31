<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AccountListingController extends Controller
{
    protected $baileysUrl = 'http://127.0.0.1:3000';

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

        // Upsert record in database as pending
        $account = new WhatsappAccount();
        $account->session_id   = $sessionId;
        $account->account_name = $accountName;
        $account->admin_id     = auth('admin')->id() ?? 1;
        $account->status       = 0;
        $account->save();

        try {
            $response = Http::timeout(15)->post("{$this->baileysUrl}/api/sessions/start", [
                'sessionId'   => $sessionId,
                'accountName' => $accountName,
                'fresh'       => true,
            ]);

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
                'error'  => 'Could not connect to Baileys service at ' . $this->baileysUrl . '. Please ensure the background service is running.',
            ], 500);
        }
    }

    public function sessionStatus($sessionId)
    {
        try {
            $response = Http::timeout(8)->get("{$this->baileysUrl}/api/sessions/status/{$sessionId}");

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
            $response = Http::timeout(15)->post("{$this->baileysUrl}/api/messages/send", [
                'sessionId' => $request->session_id,
                'receiver'  => $request->receiver,
                'message'   => $request->message,
            ]);

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
                Http::timeout(5)->post("{$this->baileysUrl}/api/sessions/delete/{$sessionId}");
            } catch (\Exception $e) {
                // Service may be offline, proceed with local deletion
            }

            // 2. Direct filesystem deletion fallback
            $sessionDir = base_path('../baileys-service/sessions/' . $sessionId);
            if (is_dir($sessionDir)) {
                \Illuminate\Support\Facades\File::deleteDirectory($sessionDir);
            }

            // 3. Convert any auto-reply rules targeting this account to Universal (null)
            \App\Models\AutoReply::where('session_id', $sessionId)->update(['session_id' => null]);
        }

        $account->delete();

        $notify[] = ['success', 'WhatsApp account and session files completely removed'];
        return back()->withNotify($notify);
    }

    public function extractGroups($sessionId)
    {
        try {
            $response = Http::timeout(20)->get("{$this->baileysUrl}/api/groups/{$sessionId}");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            $err = $response->json()['error'] ?? 'Failed to extract groups.';
            return response()->json(['success' => false, 'error' => $err], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Baileys service error: ' . $e->getMessage()], 500);
        }
    }

    public function details($id)
    {
        $pageTitle = 'WhatsApp Account Details';
        $account = WhatsappAccount::findOrFail($id);
        return view('admin.account_listing.details', compact('pageTitle', 'account'));
    }
}
