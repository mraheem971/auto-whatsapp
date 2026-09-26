@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-2 py-sm-3">
    <div class="container-fluid px-2 px-sm-3">
        
        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="mb-1 fw-bold text-dark"><i class="las la-bullhorn text--base me-1"></i> {{ $campaign->name }}</h5>
                <p class="text-muted small mb-0">Live Campaign Dispatcher & Real-Time Delivery Monitor</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('user.campaigns.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="las la-arrow-left me-1"></i> Back to Campaigns
                </a>
            </div>
        </div>

        <div class="row g-3">
            
            <!-- Left: Campaign Overview & Controls -->
            <div class="col-12 col-lg-4">
                <div class="card custom--card border shadow-sm rounded-3 mb-3">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0 fw-bold"><i class="las la-info-circle text--base me-1"></i> Campaign Details</h6>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle small fw-semibold">
                            <i class="las la-shield-alt me-1"></i> Anti-Ban Active
                        </span>
                    </div>
                    <div class="card-body p-3 p-sm-4">
                        <div class="mb-3">
                            <span class="text-muted small fw-bold">Message Content</span>
                            <div class="p-2 p-sm-3 bg-light rounded border small text-dark mt-1 font-monospace" style="white-space: pre-wrap; max-height: 140px; overflow-y: auto;">{{ $campaign->message }}</div>
                        </div>

                        <ul class="list-group list-group-flush mb-3 small">
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted">Total Targets</span>
                                <span class="fw-bold text-dark">{{ count($targets) }} recipients</span>
                            </li>
                            
                            <!-- Anti-Ban Human Behaviour Rules Summary & Edit Trigger -->
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted fw-semibold text-danger">
                                        <i class="las la-user-shield me-1"></i> Anti-Ban Behaviour
                                    </span>
                                    <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2" id="btnOpenAntiBanModal" data-bs-toggle="modal" data-bs-target="#antiBanSettingsModal" style="font-size: 11px;">
                                        <i class="las la-sliders-h me-1"></i> Configure
                                    </button>
                                </div>
                                <div class="p-2 bg-light rounded border mt-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted" style="font-size: 11px;">Delay Range:</span>
                                        <span class="fw-bold text-success" id="displayDelayRange">{{ $campaign->min_delay_seconds }}s - {{ $campaign->max_delay_seconds }}s</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted" style="font-size: 11px;">Batch Pause:</span>
                                        <span class="fw-bold text-dark" id="displayBatchPause">Pause {{ $campaign->delay_after_duration }}s after {{ $campaign->delay_after_count }} msgs</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted" style="font-size: 11px;">Cycle Reset:</span>
                                        <span class="fw-bold text-dark" id="displayResetCount">Reset after {{ $campaign->reset_after_count }} msgs</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted" style="font-size: 11px;">Current Batch Sent:</span>
                                        <span class="badge bg-secondary" id="displayBatchSent">{{ $campaign->batch_sent_count ?? 0 }} / {{ $campaign->delay_after_count }}</span>
                                    </div>
                                </div>
                            </li>


                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
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
            <div class="col-12 col-lg-8">
                <div class="card custom--card border shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="card-title mb-0 fw-bold"><i class="las la-satellite-dish text-success me-1"></i> Live Dispatch Progress</h6>
                        <span class="badge bg-secondary" id="campaignStatusBadge">{{ strtoupper($campaign->status) }}</span>
                    </div>
                    <div class="card-body p-3 p-sm-4">
                        
                        <!-- Anti-Ban Human Delay Live Alert -->
                        <div id="delayCountdownAlert" class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2 d-none" style="border-left: 4px solid #0d6efd;">
                            <div class="d-flex align-items-center">
                                <i class="las la-shield-alt fs-3 me-2 text-primary" id="delayAlertIcon"></i>
                                <div>
                                    <span class="fw-bold d-block text-dark small" id="delayAlertTitle">Anti-Ban Human Delay Active</span>
                                    <small class="text-muted" id="delayAlertSubtitle">Next message will send in <span class="badge bg-primary text-white fw-bold" id="delayCountdownTimer">0s</span> <span class="text-secondary" id="delayRangeNotice">({{ $campaign->min_delay_seconds }}s - {{ $campaign->max_delay_seconds }}s)</span></small>
                                </div>
                            </div>
                            <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                        </div>

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
                        <div class="row g-2 g-sm-3 text-center mb-4">
                            <div class="col-4">
                                <div class="p-2 p-sm-3 bg-light rounded border">
                                    <span class="text-muted small d-block">Sent</span>
                                    <h5 class="fw-bold text-success mb-0 fs-6 fs-sm-5" id="statSent">0</h5>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 p-sm-3 bg-light rounded border">
                                    <span class="text-muted small d-block">Failed</span>
                                    <h5 class="fw-bold text-danger mb-0 fs-6 fs-sm-5" id="statFailed">0</h5>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 p-sm-3 bg-light rounded border">
                                    <span class="text-muted small d-block">Remaining</span>
                                    <h5 class="fw-bold text-dark mb-0 fs-6 fs-sm-5" id="statRemaining">{{ count($targets) }}</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Terminal Logs -->
                        <h6 class="fw-bold small text-muted text-uppercase mb-2"><i class="las la-terminal me-1"></i> Activity Log</h6>
                        <div class="bg-dark text-white p-2 p-sm-3 rounded font-monospace small" id="campaignTerminal" style="height: 250px; overflow-y: auto;">
                            <div class="text-secondary">[Ready] Click "Start Broadcast" to begin sending messages with anti-ban natural delays.</div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Anti-Ban Human Behaviour Modal Matching Screenshot -->
<div class="modal fade" id="antiBanSettingsModal" tabindex="-1" aria-labelledby="antiBanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.18);">
            <div class="modal-header border-0 pb-0 pt-3 px-3 px-sm-4 bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold text-dark fs-6" id="antiBanModalLabel">Anti-Ban Human Behaviour</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-3 px-sm-4 pt-3 pb-2">
                <form id="antiBanModalForm">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Per Message Minimum Delay (Seconds) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="modalMinDelay" class="form-control form-control-sm" value="{{ $campaign->min_delay_seconds }}" min="1" max="600" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Per Message Maximum Delay (Seconds) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="modalMaxDelay" class="form-control form-control-sm" value="{{ $campaign->max_delay_seconds }}" min="1" max="600" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Delay After Count <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="modalDelayAfterCount" class="form-control form-control-sm" value="{{ $campaign->delay_after_count }}" min="1" max="5000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Delay After Duration (Seconds) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="modalDelayAfterDuration" class="form-control form-control-sm" value="{{ $campaign->delay_after_duration }}" min="1" max="3600" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Reset After Count <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="modalResetAfterCount" class="form-control form-control-sm" value="{{ $campaign->reset_after_count }}" min="1" max="10000" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-3 px-sm-4 pb-3 d-flex justify-content-end gap-2 bg-transparent">
                <button type="button" class="btn" data-bs-dismiss="modal" style="border: 1px solid #e6535c; color: #e6535c; background: #fff; border-radius: 4px; padding: 7px 22px; font-weight: 500;">Close</button>
                <button type="button" class="btn text-white" id="btnSaveAntiBanModal" style="background-color: #e6535c; border-color: #e6535c; border-radius: 4px; padding: 7px 24px; font-weight: 500;">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function ($) {
        "use strict";

        var campaignId = {{ $campaign->id }};
        var total = {{ count($targets) }};
        var pollInterval = null;
        var countdownTimer = null;
        var currentSecondsLeft = 0;
        var initialStatus = '{{ $campaign->status }}';

        function formatDuration(seconds) {
            if (seconds <= 0) return '0s';
            var h = Math.floor(seconds / 3600);
            var m = Math.floor((seconds % 3600) / 60);
            var s = seconds % 60;
            if (h > 0) return h + 'h ' + m + 'm ' + s + 's';
            if (m > 0) return m + 'm ' + s + 's';
            return s + 's';
        }

        function startCountdown(seconds, isDailyLimitReached, dailyLimit, todaySent, minD, maxD) {
            currentSecondsLeft = parseInt(seconds) || 0;
            if (countdownTimer) clearInterval(countdownTimer);

            if (currentSecondsLeft > 0) {
                $('#delayCountdownAlert').removeClass('d-none');

                if (isDailyLimitReached) {
                    $('#delayAlertIcon').attr('class', 'las la-hourglass-half fs-3 me-2 text-warning');
                    $('#delayAlertTitle').text('Daily Target Message Limit Reached (' + todaySent + '/' + dailyLimit + ')');
                    $('#delayAlertSubtitle').html('Safe anti-ban sleep active. Resumes tomorrow at midnight. Next message in <span class="badge bg-warning text-dark fw-bold" id="delayCountdownTimer">' + formatDuration(currentSecondsLeft) + '</span>');
                } else {
                    $('#delayAlertIcon').attr('class', 'las la-shield-alt fs-3 me-2 text-primary');
                    $('#delayAlertTitle').text('Anti-Ban Human Delay Active');
                    $('#delayAlertSubtitle').html('Next message will send in <span class="badge bg-primary text-white fw-bold" id="delayCountdownTimer">' + formatDuration(currentSecondsLeft) + '</span> <span class="text-secondary" id="delayRangeNotice">(' + (minD || 30) + 's - ' + (maxD || 60) + 's delay)</span>');
                }

                countdownTimer = setInterval(function () {
                    currentSecondsLeft--;
                    if (currentSecondsLeft <= 0) {
                        clearInterval(countdownTimer);
                        countdownTimer = null;
                        $('#delayCountdownAlert').addClass('d-none');
                    } else {
                        $('#delayCountdownTimer').text(formatDuration(currentSecondsLeft));
                    }
                }, 1000);
            } else {
                $('#delayCountdownAlert').addClass('d-none');
            }
        }

        function log(msg, type) {
            var color = type === 'success' ? '#28c76f' : (type === 'error' ? '#ea5455' : '#7367f0');
            var time = new Date().toLocaleTimeString();
            $('#campaignTerminal').append('<div style="color:' + color + '">[' + time + '] ' + msg + '</div>');
            var term = document.getElementById('campaignTerminal');
            if (term) term.scrollTop = term.scrollHeight;
        }

        function updateProgress(sent, failed, totalTargets, status, pct, round, autoRestart, secondsUntilNext, minD, maxD, dailyLimit, todaySent, dailyRemaining, isDailyLimitReached, delayAfterCount, delayAfterDuration, resetAfterCount, batchSentCount) {
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

            if (minD && maxD) {
                $('#displayDelayRange').text(minD + 's - ' + maxD + 's');
                $('#delayRangeNotice').text('(' + minD + 's - ' + maxD + 's delay)');
            }

            if (delayAfterCount && delayAfterDuration) {
                $('#displayBatchPause').text('Pause ' + delayAfterDuration + 's after ' + delayAfterCount + ' msgs');
            }
            if (resetAfterCount) {
                $('#displayResetCount').text('Reset after ' + resetAfterCount + ' msgs');
            }
            if (batchSentCount !== undefined && delayAfterCount) {
                $('#displayBatchSent').text(batchSentCount + ' / ' + delayAfterCount);
            }

            if (dailyLimit !== undefined) {
                $('#displayDailyLimit').text(dailyLimit > 0 ? dailyLimit + ' / day' : 'Unlimited');
                $('#displayTodaySent').text((todaySent || 0) + (dailyLimit > 0 ? ' / ' + dailyLimit : ''));
            }

            if (status === 'running') {
                if (isDailyLimitReached) {
                    $('#campaignStatusBadge').removeClass('bg-secondary bg-warning bg-success').addClass('bg-info').text('DAILY LIMIT SLEEP (RESUMES TOMORROW)');
                } else {
                    $('#campaignStatusBadge').removeClass('bg-secondary bg-warning bg-success bg-info').addClass('bg-primary').text('RUNNING (ROUND #' + (round || 1) + ')');
                }
                $('#btnStartCampaign').addClass('d-none');
                $('#btnPauseCampaign').removeClass('d-none');

                if (secondsUntilNext > 0) {
                    startCountdown(secondsUntilNext, isDailyLimitReached, dailyLimit, todaySent, minD, maxD);
                } else {
                    if (countdownTimer) clearInterval(countdownTimer);
                    $('#delayCountdownAlert').addClass('d-none');
                }
            } else if (status === 'completed') {
                if (countdownTimer) clearInterval(countdownTimer);
                $('#delayCountdownAlert').addClass('d-none');
                if (autoRestart) {
                    $('#campaignStatusBadge').removeClass('bg-secondary bg-warning bg-success').addClass('bg-info').text('AUTO-RESTARTING...');
                } else {
                    $('#campaignStatusBadge').removeClass('bg-primary bg-warning bg-secondary bg-info').addClass('bg-success').text('COMPLETED');
                    $('#btnPauseCampaign').addClass('d-none');
                    $('#btnStartCampaign').removeClass('d-none').html('<i class="las la-check me-1"></i> Broadcast Finished').prop('disabled', true);
                    stopPolling();
                }
            } else if (status === 'paused') {
                if (countdownTimer) clearInterval(countdownTimer);
                $('#delayCountdownAlert').addClass('d-none');
                $('#campaignStatusBadge').removeClass('bg-primary bg-success bg-info').addClass('bg-warning').text('PAUSED');
                $('#btnPauseCampaign').addClass('d-none');
                $('#btnStartCampaign').removeClass('d-none').html('<i class="las la-play me-1"></i> Resume Broadcast');
                stopPolling();
            }
        }

        function pollStatus() {
            $.get("{{ url('user/campaigns/live-status') }}/" + campaignId, function (res) {
                if (res && res.success) {
                    updateProgress(
                        res.sent_count, 
                        res.failed_count, 
                        res.total_targets, 
                        res.status, 
                        res.progress_percent, 
                        res.current_round, 
                        res.auto_restart, 
                        res.seconds_until_next, 
                        res.min_delay, 
                        res.max_delay, 
                        res.daily_limit, 
                        res.today_sent_count, 
                        res.daily_remaining, 
                        res.is_daily_limit_reached,
                        res.delay_after_count,
                        res.delay_after_duration,
                        res.reset_after_count,
                        res.batch_sent_count
                    );
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
                notify('info', isAuto ? 'Auto-Restart Loop enabled.' : 'Auto-Restart Loop disabled.');
                log(isAuto ? '🔁 Auto-Restart Loop enabled.' : '⏹ Auto-Restart Loop disabled.', 'info');
            });
        });

        // Save Anti-Ban Settings dynamically from Modal
        $('#btnSaveAntiBanModal').on('click', function () {
            var minVal = parseInt($('#modalMinDelay').val()) || 30;
            var maxVal = parseInt($('#modalMaxDelay').val()) || 60;
            var delayCount = parseInt($('#modalDelayAfterCount').val()) || 50;
            var delayDuration = parseInt($('#modalDelayAfterDuration').val()) || 5;
            var resetCount = parseInt($('#modalResetAfterCount').val()) || 100;

            if (maxVal < minVal) maxVal = minVal;

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Saving...');

            $.post("{{ url('user/campaigns/update-status') }}/" + campaignId, {
                _token: "{{ csrf_token() }}",
                min_delay: minVal,
                max_delay: maxVal,
                delay_after_count: delayCount,
                delay_after_duration: delayDuration,
                reset_after_count: resetCount
            }, function (res) {
                $btn.prop('disabled', false).text('Save');
                $('#displayDelayRange').text(minVal + 's - ' + maxVal + 's');
                $('#displayBatchPause').text('Pause ' + delayDuration + 's after ' + delayCount + ' msgs');
                $('#displayResetCount').text('Reset after ' + resetCount + ' msgs');
                $('#delayRangeNotice').text('(' + minVal + 's - ' + maxVal + 's delay)');

                var modalEl = bootstrap.Modal.getInstance(document.getElementById('antiBanSettingsModal'));
                if (modalEl) modalEl.hide();

                notify('success', 'Anti-Ban Human Behaviour settings updated live!');
                log('🛡 Anti-Ban settings updated live: ' + minVal + 's-' + maxVal + 's delay, pause ' + delayDuration + 's after ' + delayCount + ' msgs, reset at ' + resetCount + ' msgs.', 'info');
            }).fail(function (xhr) {
                $btn.prop('disabled', false).text('Save');
                notify('error', 'Failed to update anti-ban settings.');
            });
        });



        // Connect poller
        if (initialStatus === 'running') {
            log('🔄 Connecting to live background campaign dispatcher...', 'info');
            startPolling();
        } else {
            pollStatus();
        }

    })(jQuery);
</script>
@endpush
