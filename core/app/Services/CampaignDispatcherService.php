<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\Log;

class CampaignDispatcherService
{
    /**
     * Resolve all target recipients for a given campaign.
     */
    public static function getTargets(Campaign $campaign): array
    {
        $targets = [];
        $targetType = $campaign->target_type;
        $listId = $campaign->contact_list_id;
        $userId = $campaign->user_id;

        if ($targetType === 'contact_list' || str_starts_with($targetType ?? '', 'list_') || $listId) {
            $effectiveListId = $listId ?: ((str_starts_with($targetType ?? '', 'list_')) ? (int) str_replace('list_', '', $targetType) : null);
            $query = Contact::query();
            if ($userId) {
                $query->where('user_id', $userId);
            }
            $contacts = $query->where('contact_list_id', $effectiveListId)->get();

            if ($contacts->isEmpty()) {
                $fallbackQuery = ContactList::query();
                if ($userId) {
                    $fallbackQuery->where('user_id', $userId);
                } else {
                    $fallbackQuery->where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); });
                }
                $fallbackList = $fallbackQuery->first();
                if ($fallbackList) {
                    $contacts = Contact::where('contact_list_id', $fallbackList->id)->get();
                }
                if ($contacts->isEmpty()) {
                    $cQuery = Contact::query();
                    if ($userId) {
                        $cQuery->where('user_id', $userId);
                    } else {
                        $cQuery->where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); });
                    }
                    $contacts = $cQuery->get();
                }
                if ($contacts->isEmpty()) {
                    $contacts = Contact::all();
                }
            }

            foreach ($contacts as $c) {
                $targetJid = $c->target_jid ?: ($c->type === 'group' ? ($c->group_id ?: $c->phone_number) : "{$c->phone_number}@s.whatsapp.net");
                $targets[] = [
                    'type'       => $c->type ?: ($c->group_id ? 'group' : 'contact'),
                    'name'       => $c->name ?: ($c->group_name ?: "+{$c->phone_number}"),
                    'target_jid' => $targetJid,
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                ];
            }
        } elseif ($targetType === 'groups') {
            $query = Contact::query();
            if ($userId) {
                $query->where('user_id', $userId);
            }
            $groups = $query->whereNotNull('group_id')
                ->where('group_id', '!=', '')
                ->selectRaw('group_name, group_id, MAX(name) as name, MAX(phone_number) as phone_number')
                ->groupBy('group_name', 'group_id')
                ->get();

            if ($groups->isEmpty()) {
                $groups = Contact::whereNotNull('group_id')->where('group_id', '!=', '')
                    ->selectRaw('group_name, group_id, MAX(name) as name, MAX(phone_number) as phone_number')
                    ->groupBy('group_name', 'group_id')
                    ->get();
            }

            foreach ($groups as $g) {
                $targets[] = [
                    'type'       => 'group',
                    'name'       => $g->name ?: ($g->group_name ?: 'WhatsApp Group'),
                    'target_jid' => $g->group_id,
                    'phone'      => $g->phone_number ?: $g->group_id,
                    'group_name' => $g->group_name,
                ];
            }
        } elseif ($targetType === 'selected_groups') {
            $selectedIds = is_array($campaign->target_group_ids) ? $campaign->target_group_ids : (json_decode($campaign->target_group_ids ?? '[]', true) ?: []);
            $groups = Contact::whereIn('group_id', $selectedIds)
                ->selectRaw('group_name, group_id, MAX(name) as name, MAX(phone_number) as phone_number')
                ->groupBy('group_name', 'group_id')
                ->get();
            foreach ($groups as $g) {
                $targets[] = [
                    'type'       => 'group',
                    'name'       => $g->name ?: ($g->group_name ?: 'WhatsApp Group'),
                    'target_jid' => $g->group_id,
                    'phone'      => $g->phone_number ?: $g->group_id,
                    'group_name' => $g->group_name,
                ];
            }
        } elseif ($targetType === 'selected_group') {
            $group = Contact::where('group_id', $campaign->target_group_id)->first();
            $targets[] = [
                'type'       => 'group',
                'name'       => $group ? $group->group_name : 'Selected Group',
                'target_jid' => $campaign->target_group_id,
                'phone'      => $group ? $group->phone_number : $campaign->target_group_id,
                'group_name' => $group ? $group->group_name : 'Selected Group',
            ];
        } elseif ($targetType === 'contacts') {
            $query = Contact::where('type', 'contact');
            if ($userId) {
                $query->where('user_id', $userId);
            }
            $contacts = $query->get();
            if ($contacts->isEmpty()) {
                $contacts = Contact::where('user_id', $userId)->get();
            }
            if ($contacts->isEmpty()) {
                $contacts = Contact::all();
            }
            foreach ($contacts as $c) {
                $targets[] = [
                    'type'       => 'contact',
                    'name'       => $c->name ?: "+{$c->phone_number}",
                    'target_jid' => $c->target_jid ?: "{$c->phone_number}@s.whatsapp.net",
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                ];
            }
        } else {
            // All contacts & groups
            $cQuery = Contact::query();
            if ($userId) {
                $cQuery->where('user_id', $userId);
            } else {
                $cQuery->where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); });
            }
            $contacts = $cQuery->get();
            if ($contacts->isEmpty()) {
                $contacts = Contact::all();
            }
            foreach ($contacts as $c) {
                $targetJid = $c->target_jid ?: ($c->type === 'group' ? ($c->group_id ?: $c->phone_number) : "{$c->phone_number}@s.whatsapp.net");
                $targets[] = [
                    'type'       => $c->type ?: ($c->group_id ? 'group' : 'contact'),
                    'name'       => $c->name ?: ($c->group_name ?: "+{$c->phone_number}"),
                    'target_jid' => $targetJid,
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                ];
            }
        }

        // Deduplicate by target_jid
        $unique = [];
        $seen = [];
        foreach ($targets as $t) {
            if (!empty($t['target_jid']) && !isset($seen[$t['target_jid']])) {
                $seen[$t['target_jid']] = true;
                $unique[] = $t;
            }
        }

        return $unique;
    }

    /**
     * Auto-heal or resolve an active connected WhatsApp session.
     */
    public static function resolveActiveSession(Campaign $campaign): ?string
    {
        $sessionId = $campaign->session_id;

        // Check if current sessionId is active and online
        if ($sessionId) {
            try {
                $statusRes = BaileysClient::get("api/sessions/status/{$sessionId}", [], 4);
                if ($statusRes && $statusRes->successful() && $statusRes->json('status') === 'connected') {
                    return $sessionId;
                }
            } catch (\Exception $e) {}
        }

        // Current session is not connected. Find any active connected account for this user/admin
        $accountQuery = WhatsappAccount::active();
        if ($campaign->user_id) {
            $accountQuery->where('user_id', $campaign->user_id);
        } else {
            $accountQuery->adminOnly();
        }

        $activeAccount = $accountQuery->latest()->first();

        // If not found in DB with status=1, probe connected status directly via microservice
        if (!$activeAccount) {
            $allAccounts = $campaign->user_id 
                ? WhatsappAccount::where('user_id', $campaign->user_id)->get()
                : WhatsappAccount::adminOnly()->get();

            foreach ($allAccounts as $acc) {
                try {
                    $st = BaileysClient::get("api/sessions/status/{$acc->session_id}", [], 4);
                    if ($st && $st->successful() && $st->json('status') === 'connected') {
                        $acc->update(['status' => 1]);
                        $activeAccount = $acc;
                        break;
                    }
                } catch (\Exception $e) {}
            }
        }

        // If still no account and admin campaign, check any connected account
        if (!$activeAccount && !$campaign->user_id) {
            $activeAccount = WhatsappAccount::active()->latest()->first();
        }

        if ($activeAccount) {
            $campaign->session_id = $activeAccount->session_id;
            $campaign->save();
            return $activeAccount->session_id;
        }

        return $sessionId;
    }

    /**
     * Send message to a single target and update campaign logs and stats.
     */
    public static function sendTarget(Campaign $campaign, array $target): array
    {
        $sessionId = self::resolveActiveSession($campaign);
        $targetJid = $target['target_jid'] ?? '';
        $name = $target['name'] ?? 'Customer';
        $phone = $target['phone'] ?? preg_replace('/[^0-9]/', '', $targetJid);
        $groupName = $target['group_name'] ?? '';

        if (empty($targetJid)) {
            return ['success' => false, 'error' => 'Invalid target JID'];
        }

        // Personalization tags
        $msg = str_replace(
            ['{name}', '{phone}', '{group_name}', '{{name}}', '{{phone}}', '@name', '@phone'],
            [$name, $phone, $groupName, $name, $phone, $name, $phone],
            $campaign->message
        );

        $isGroup = ($target['type'] === 'group' || str_ends_with($targetJid, '@g.us')) ? 1 : 0;

        $payload = [
            'sessionId' => $sessionId,
            'receiver'  => $targetJid,
            'recipient' => $targetJid,
            'message'   => $msg,
            'isGroup'   => $isGroup,
        ];

        if (!empty($campaign->media_url) && ($campaign->media_type ?? 'text') !== 'text') {
            $payload['mediaUrl'] = $campaign->media_url;
            $payload['mediaType'] = $campaign->media_type;
        }

        $logEntry = [
            'timestamp'  => date('Y-m-d H:i:s'),
            'target'     => $name,
            'target_jid' => $targetJid,
            'type'       => $target['type'] ?? 'contact',
        ];

        try {
            $res = BaileysClient::post('api/messages/send', $payload, 25);
            $resData = $res ? $res->json() : [];

            $isSuccess = ($res && $res->successful() && (!empty($resData['success']) || (isset($resData['status']) && $resData['status'] === 'success')));

            if ($isSuccess) {
                $campaign->increment('sent_count');
                $logEntry['status'] = 'success';
                $logEntry['message'] = 'Delivered';

                $currentLogs = $campaign->fresh()->logs ?? [];
                $currentLogs[] = $logEntry;
                $campaign->logs = $currentLogs;
                $campaign->save();

                return ['success' => true, 'status' => 'success', 'message' => 'Delivered'];
            } else {
                $campaign->increment('failed_count');
                $errMsg = $resData['error'] ?? 'Delivery failed';
                $logEntry['status'] = 'failed';
                $logEntry['error'] = $errMsg;

                $currentLogs = $campaign->fresh()->logs ?? [];
                $currentLogs[] = $logEntry;
                $campaign->logs = $currentLogs;
                $campaign->save();

                return ['success' => false, 'status' => 'failed', 'error' => $errMsg];
            }
        } catch (\Exception $e) {
            $campaign->increment('failed_count');
            $logEntry['status'] = 'failed';
            $logEntry['error'] = $e->getMessage();

            $currentLogs = $campaign->fresh()->logs ?? [];
            $currentLogs[] = $logEntry;
            $campaign->logs = $currentLogs;
            $campaign->save();

            return ['success' => false, 'status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    /**
     * Start campaign execution in the background asynchronously.
     */
    public static function launchBackgroundProcess(int $campaignId): void
    {
        $artisanPath = base_path('artisan');
        $phpBinary = PHP_BINARY ?: 'php';

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B cmd /c \"\"{$phpBinary}\" \"{$artisanPath}\" campaign:run-single {$campaignId}\"", "r"));
        } else {
            exec("\"{$phpBinary}\" \"{$artisanPath}\" campaign:run-single {$campaignId} > /dev/null 2>&1 &");
        }
    }

    /**
     * Run an entire campaign loop until completed or paused.
     */
    public static function executeCampaign(int $campaignId): void
    {
        $campaign = Campaign::find($campaignId);
        if (!$campaign) return;

        // Ensure status is running
        $campaign->status = 'running';
        $campaign->save();

        $targets = self::getTargets($campaign);
        if (empty($targets)) {
            $campaign->status = 'completed';
            $campaign->save();
            return;
        }

        $campaign->total_targets = count($targets);
        $campaign->save();

        $minDelay = $campaign->min_delay ?: ($campaign->delay_seconds ?: 5);
        $maxDelay = $campaign->max_delay ?: ($campaign->delay_seconds ?: 15);
        if ($maxDelay < $minDelay) $maxDelay = $minDelay;

        // Find already sent target JIDs from logs
        $existingLogs = $campaign->logs ?? [];
        $processedJids = [];
        foreach ($existingLogs as $l) {
            if (!empty($l['target_jid'])) {
                $processedJids[$l['target_jid']] = true;
            }
        }

        foreach ($targets as $target) {
            // Check if user paused or stopped the campaign
            $fresh = Campaign::find($campaignId);
            if (!$fresh || $fresh->status !== 'running') {
                Log::info("Campaign #{$campaignId} stopped/paused by user.");
                return;
            }

            if (isset($processedJids[$target['target_jid']])) {
                continue; // Skip already processed target
            }

            // Random anti-ban delay
            $delay = rand($minDelay, $maxDelay);
            sleep($delay);

            // Double check status after sleep
            $fresh = Campaign::find($campaignId);
            if (!$fresh || $fresh->status !== 'running') {
                return;
            }

            self::sendTarget($fresh, $target);
            $processedJids[$target['target_jid']] = true;
        }

        // Mark completed
        $final = Campaign::find($campaignId);
        if ($final && $final->status === 'running') {
            $final->status = 'completed';
            $final->save();
        }
    }
}
