@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">{{ $campaign->name }}</h4>
                <p class="text-muted mb-0">Live Campaign Dispatcher & Real-Time Delivery Monitor</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('user.campaigns.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="las la-arrow-left me-1"></i> Back to Campaigns
                </a>
            </div>
        </div>

        <div class="row gy-4">
            
            <!-- Left: Campaign Overview & Controls -->
            <div class="col-lg-4">
                <div class="card custom--card border shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="card-title mb-0 fw-bold"><i class="las la-info-circle text--base me-1"></i> Campaign Details</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <span class="text-muted small fw-bold">Message Content</span>
                            <div class="p-3 bg-light rounded border small text-dark mt-1 font-monospace" style="white-space: pre-wrap;">{{ $campaign->message }}</div>
                        </div>

                        <ul class="list-group list-group-flush mb-4 small">
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Total Targets</span>
                                <span class="fw-bold">{{ count($targets) }} recipients</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Human Delay Range</span>
                                <span class="fw-bold text-success">{{ $campaign->min_delay_seconds }}s - {{ $campaign->max_delay_seconds }}s</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Simulated Typing</span>
                                <span class="fw-bold text-primary">{{ $botSettings->typing_simulation ? 'Active (' . $botSettings->typing_duration_seconds . 's)' : 'Off' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted">Auto-Restart Loop</span>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="toggleAutoRestart" {{ ($campaign->auto_restart ?? 1) ? 'checked' : '' }} style="cursor: pointer; width: 2.2em; height: 1.2em;">
                                </div>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted">Broadcast Round</span>
                                <span class="badge bg-primary text-white fw-bold px-2 py-1" id="statRound">Round #{{ ($campaign->loop_count ?? 0) + 1 }}</span>
                            </li>
                        </ul>

                        <!-- Controls -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn--base py-2 fw-bold" id="btnStartCampaign">
                                <i class="las la-play me-1"></i> Start Broadcast
                            </button>
                            <button type="button" class="btn btn-warning py-2 fw-bold d-none" id="btnPauseCampaign">
                                <i class="las la-pause me-1"></i> Pause Broadcast
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Live Progress & Log Terminal -->
            <div class="col-lg-8">
                <div class="card custom--card border shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0 fw-bold"><i class="las la-satellite-dish text-success me-1"></i> Live Dispatch Progress</h6>
                        <span class="badge bg-secondary" id="campaignStatusBadge">{{ strtoupper($campaign->status) }}</span>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small fw-bold mb-2">
                                <span id="progressText">0 of {{ count($targets) }} dispatched</span>
                                <span id="progressPercent">0%</span>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="progressBar" role="progressbar" style="width: 0%;"></div>
                            </div>
                        </div>

                        <!-- Stats Row -->
                        <div class="row g-3 text-center mb-4">
                            <div class="col-4">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-muted small">Sent</span>
                                    <h4 class="fw-bold text-success mb-0" id="statSent">0</h4>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-muted small">Failed</span>
                                    <h4 class="fw-bold text-danger mb-0" id="statFailed">0</h4>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-muted small">Remaining</span>
                                    <h4 class="fw-bold text-dark mb-0" id="statRemaining">{{ count($targets) }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Terminal Logs -->
                        <h6 class="fw-bold small text-muted text-uppercase mb-2"><i class="las la-terminal me-1"></i> Activity Log</h6>
                        <div class="bg-dark text-white p-3 rounded font-monospace small" id="campaignTerminal" style="height: 250px; overflow-y: auto;">
                            <div class="text-secondary">[Ready] Click "Start Broadcast" to begin sending messages with anti-ban natural delays.</div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@push('script')
<script>
    (function ($) {
        "use strict";

        var targets = @json($targets);
        var total = targets.length;
        var campaignId = "{{ $campaign->id }}";
        var initialStatus = "{{ $campaign->status }}";
        var pollInterval = null;

        function log(msg, type) {
            var color = type === 'success' ? '#28c76f' : (type === 'error' ? '#ea5455' : '#7367f0');
            var time = new Date().toLocaleTimeString();
            $('#campaignTerminal').append('<div style="color:' + color + '">[' + time + '] ' + msg + '</div>');
            var term = document.getElementById('campaignTerminal');
            if (term) term.scrollTop = term.scrollHeight;
        }

        function updateProgress(sent, failed, totalTargets, status, pct, round, autoRestart) {
            var processed = sent + failed;
            $('#progressBar').css('width', pct + '%');
            $('#progressPercent').text(pct + '%');
            $('#progressText').text(pct + '% of target audience in current cycle');
            $('#statSent').text(sent);
            $('#statFailed').text(failed);
            $('#statRemaining').text(Math.max(0, totalTargets - (sent % (totalTargets || 1))));

            if (round) {
                $('#statRound').text('Round #' + round);
            }
            if (autoRestart !== undefined) {
                $('#toggleAutoRestart').prop('checked', !!autoRestart);
            }

            if (status === 'running') {
                $('#campaignStatusBadge').removeClass('bg-secondary bg-warning bg-success').addClass('bg-primary').text('RUNNING (ROUND #' + (round || 1) + ')');
                $('#btnStartCampaign').addClass('d-none');
                $('#btnPauseCampaign').removeClass('d-none');
            } else if (status === 'completed') {
                if (autoRestart) {
                    $('#campaignStatusBadge').removeClass('bg-secondary bg-warning bg-success').addClass('bg-info').text('AUTO-RESTARTING...');
                } else {
                    $('#campaignStatusBadge').removeClass('bg-primary bg-warning bg-secondary bg-info').addClass('bg-success').text('COMPLETED');
                    $('#btnPauseCampaign').addClass('d-none');
                    $('#btnStartCampaign').removeClass('d-none').html('<i class="las la-check me-1"></i> Broadcast Finished').prop('disabled', true);
                    stopPolling();
                }
            } else if (status === 'paused') {
                $('#campaignStatusBadge').removeClass('bg-primary bg-success bg-info').addClass('bg-warning').text('PAUSED');
                $('#btnPauseCampaign').addClass('d-none');
                $('#btnStartCampaign').removeClass('d-none').html('<i class="las la-play me-1"></i> Resume Broadcast');
                stopPolling();
            }
        }

        function pollStatus() {
            $.get("{{ url('user/campaigns/live-status') }}/" + campaignId, function (res) {
                if (res && res.success) {
                    updateProgress(res.sent_count, res.failed_count, res.total_targets, res.status, res.progress_percent, res.current_round, res.auto_restart);
                    if (res.logs && res.logs.length > 0) {
                        var lastLog = res.logs[res.logs.length - 1];
                        if (lastLog && lastLog.target) {
                            var statusText = lastLog.status === 'success' ? 'Delivered' : (lastLog.status === 'info' ? lastLog.message : ('Failed: ' + (lastLog.error || '')));
                            log('[' + lastLog.status.toUpperCase() + '] ' + (lastLog.target || lastLog.target_jid) + ' - ' + statusText, lastLog.status);
                        }
                    }
                    if (res.status === 'completed' && !res.auto_restart) {
                        log('🎉 Campaign broadcast completed successfully on server!', 'success');
                        stopPolling();
                    }
                }
            });
        }

        function startPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollStatus();
            pollInterval = setInterval(pollStatus, 2500);
        }

        function stopPolling() {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        }

        $('#btnStartCampaign').on('click', function () {
            if (total === 0) {
                notify('warning', 'No targets in queue. Please extract or sync WhatsApp contacts first.');
                log('⚠ No targets found in this audience. Please extract groups/contacts into a list first.', 'error');
                return;
            }

            $('#btnStartCampaign').addClass('d-none');
            $('#btnPauseCampaign').removeClass('d-none');
            $('#campaignStatusBadge').removeClass('bg-secondary bg-warning').addClass('bg-primary').text('RUNNING (SERVER BACKGROUND)');
            log('🚀 Launching automatic background broadcast on server...', 'info');

            $.post("{{ route('user.campaigns.start.auto', $campaign->id) }}", {
                _token: "{{ csrf_token() }}"
            }, function (res) {
                notify('success', 'Automatic background broadcast launched! Server will send messages continuously.');
                log('✔ Background broadcast active on server. You can safely close this page.', 'success');
                startPolling();
            }).fail(function (xhr) {
                notify('error', xhr.responseJSON ? xhr.responseJSON.message : 'Failed to start broadcast');
                log('✖ Failed to start background worker.', 'error');
            });
        });

        $('#btnPauseCampaign').on('click', function () {
            $.post("{{ url('user/campaigns/update-status') }}/" + campaignId, {
                _token: "{{ csrf_token() }}",
                status: 'paused'
            }, function () {
                stopPolling();
                $('#btnPauseCampaign').addClass('d-none');
                $('#btnStartCampaign').removeClass('d-none').html('<i class="las la-play me-1"></i> Resume Broadcast');
                $('#campaignStatusBadge').removeClass('bg-primary').addClass('bg-warning').text('PAUSED');
                log('⏸ Broadcast paused by user.', 'info');
                notify('info', 'Broadcast paused.');
            });
        });

        $('#toggleAutoRestart').on('change', function () {
            var isAuto = this.checked ? 1 : 0;
            $.post("{{ url('user/campaigns/update-status') }}/" + campaignId, {
                _token: "{{ csrf_token() }}",
                auto_restart: isAuto
            }, function (res) {
                notify('info', isAuto ? 'Auto-Restart Loop enabled: Broadcast will restart when completed.' : 'Auto-Restart Loop disabled: Broadcast will finish once.');
                log(isAuto ? '🔁 Auto-Restart Loop enabled.' : '⏹ Auto-Restart Loop disabled.', 'info');
            });
        });

        // If initial status is running, connect poller immediately
        if (initialStatus === 'running') {
            log('🔄 Connecting to live background campaign dispatcher...', 'info');
            startPolling();
        } else {
            pollStatus();
        }

    })(jQuery);
</script>
@endpush
