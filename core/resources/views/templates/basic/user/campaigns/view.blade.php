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
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted"><i class="las la-clock me-1"></i> Human Delay</span>
                                    <div>
                                        <span class="fw-bold text-success" id="displayDelayRange">{{ $campaign->min_delay_seconds }}s - {{ $campaign->max_delay_seconds }}s</span>
                                        <button type="button" class="btn btn-xs btn-outline-secondary ms-1 py-0 px-1" id="btnEditDelay" style="font-size: 10px;">Edit</button>
                                    </div>
                                </div>
                                <div class="d-none mt-2 p-2 bg-light rounded border" id="editDelayBox">
                                    <div class="row g-1 align-items-center">
                                        <div class="col-5">
                                            <input type="number" id="inputMinDelay" class="form-control form-control-sm" placeholder="Min s" min="1" max="600" value="{{ $campaign->min_delay_seconds }}">
                                        </div>
                                        <div class="col-5">
                                            <input type="number" id="inputMaxDelay" class="form-control form-control-sm" placeholder="Max s" min="1" max="600" value="{{ $campaign->max_delay_seconds }}">
                                        </div>
                                        <div class="col-2 text-end">
                                            <button type="button" class="btn btn-sm btn--base w-100 p-1" id="btnSaveDelay" title="Save Delay"><i class="las la-save"></i></button>
                                        </div>
                                    </div>
                                    <small class="text-muted" style="font-size: 10px;">Anti-ban random delay between messages</small>
                                </div>
                            </li>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted"><i class="las la-calendar-check me-1"></i> Daily Send Limit</span>
                                    <div>
                                        <span class="fw-bold text-primary" id="displayDailyLimit">{{ $campaign->daily_limit > 0 ? $campaign->daily_limit . ' / day' : 'Unlimited' }}</span>
                                        <button type="button" class="btn btn-xs btn-outline-secondary ms-1 py-0 px-1" id="btnEditDailyLimit" style="font-size: 10px;">Edit</button>
                                    </div>
                                </div>
                                <div class="d-none mt-2 p-2 bg-light rounded border" id="editDailyLimitBox">
                                    <div class="input-group input-group-sm mb-1">
                                        <input type="number" id="inputDailyLimit" class="form-control form-control-sm" placeholder="0 = Unlimited" min="0" max="50000" value="{{ $campaign->daily_limit }}">
                                        <button type="button" class="btn btn-sm btn--base" id="btnSaveDailyLimit"><i class="las la-save"></i> Save</button>
                                    </div>
                                    <small class="text-muted" style="font-size: 10px;">Max messages per day (0 = unlimited)</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1" style="font-size: 11px;">
                                    <span class="text-muted">Today's Sent:</span>
                                    <span class="fw-bold text-dark" id="displayTodaySent">{{ $campaign->today_sent_count }}{{ $campaign->daily_limit > 0 ? ' / ' . $campaign->daily_limit : '' }}</span>
                                </div>
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
                        
                        <!-- Anti-Ban Human Delay Live Alert -->
                        <div id="delayCountdownAlert" class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between d-none" style="border-left: 4px solid #0d6efd;">
                            <div class="d-flex align-items-center">
                                <i class="las la-shield-alt fs-3 me-2 text-primary" id="delayAlertIcon"></i>
                                <div>
                                    <span class="fw-bold d-block text-dark" id="delayAlertTitle">Anti-Ban Human Delay Active</span>
                                    <small class="text-muted" id="delayAlertSubtitle">Next message will send in <span class="badge bg-primary text-white fw-bold fs-6" id="delayCountdownTimer">0s</span> <span class="text-secondary" id="delayRangeNotice">({{ $campaign->min_delay_seconds }}s - {{ $campaign->max_delay_seconds }}s human delay)</span></small>
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
        var countdownTimer = null;
        var currentSecondsLeft = 0;

        function formatDuration(sec) {
            sec = Math.max(0, parseInt(sec) || 0);
            if (sec < 60) return sec + 's';
            var hrs = Math.floor(sec / 3600);
            var mins = Math.floor((sec % 3600) / 60);
            var s = sec % 60;
            if (hrs > 0) return hrs + 'h ' + mins + 'm ' + s + 's';
            return mins + 'm ' + s + 's';
        }

        function startCountdown(seconds, isDailyLimitReached, dailyLimit, todaySent, minD, maxD) {
            currentSecondsLeft = parseInt(seconds) || 0;
            if (countdownTimer) clearInterval(countdownTimer);

            if (currentSecondsLeft > 0) {
                $('#delayCountdownAlert').removeClass('d-none');

                if (isDailyLimitReached) {
                    $('#delayAlertIcon').attr('class', 'las la-hourglass-half fs-3 me-2 text-warning');
                    $('#delayAlertTitle').text('Daily Target Message Limit Reached (' + todaySent + '/' + dailyLimit + ')');
                    $('#delayAlertSubtitle').html('Safe anti-ban sleep active. Broadcast automatically resumes tomorrow at midnight. Next message in <span class="badge bg-warning text-dark fw-bold fs-6" id="delayCountdownTimer">' + formatDuration(currentSecondsLeft) + '</span>');
                } else {
                    $('#delayAlertIcon').attr('class', 'las la-shield-alt fs-3 me-2 text-primary');
                    $('#delayAlertTitle').text('Anti-Ban Human Delay Active');
                    $('#delayAlertSubtitle').html('Next message will send in <span class="badge bg-primary text-white fw-bold fs-6" id="delayCountdownTimer">' + formatDuration(currentSecondsLeft) + '</span> <span class="text-secondary" id="delayRangeNotice">(' + (minD || 5) + 's - ' + (maxD || 15) + 's human delay)</span>');
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

        function updateProgress(sent, failed, totalTargets, status, pct, round, autoRestart, secondsUntilNext, minD, maxD, dailyLimit, todaySent, dailyRemaining, isDailyLimitReached) {
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
                $('#delayRangeNotice').text('(' + minD + 's - ' + maxD + 's human delay)');
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
                    updateProgress(res.sent_count, res.failed_count, res.total_targets, res.status, res.progress_percent, res.current_round, res.auto_restart, res.seconds_until_next, res.min_delay, res.max_delay, res.daily_limit, res.today_sent_count, res.daily_remaining, res.is_daily_limit_reached);
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

        $('#btnEditDelay').on('click', function () {
            $('#editDelayBox').toggleClass('d-none');
        });

        $('#btnSaveDelay').on('click', function () {
            var minVal = parseInt($('#inputMinDelay').val()) || 5;
            var maxVal = parseInt($('#inputMaxDelay').val()) || 15;
            if (maxVal < minVal) maxVal = minVal;

            $.post("{{ url('user/campaigns/update-status') }}/" + campaignId, {
                _token: "{{ csrf_token() }}",
                min_delay: minVal,
                max_delay: maxVal
            }, function (res) {
                $('#editDelayBox').addClass('d-none');
                $('#displayDelayRange').text(minVal + 's - ' + maxVal + 's');
                $('#delayRangeNotice').text('(' + minVal + 's - ' + maxVal + 's human delay)');
                notify('success', 'Anti-ban delay updated to ' + minVal + 's - ' + maxVal + 's.');
                log('⏱ Anti-ban delay updated: ' + minVal + 's - ' + maxVal + 's random delay between messages.', 'info');
            });
        });

        $('#btnEditDailyLimit').on('click', function () {
            $('#editDailyLimitBox').toggleClass('d-none');
        });

        $('#btnSaveDailyLimit').on('click', function () {
            var limitVal = parseInt($('#inputDailyLimit').val()) || 0;
            if (limitVal < 0) limitVal = 0;

            $.post("{{ url('user/campaigns/update-status') }}/" + campaignId, {
                _token: "{{ csrf_token() }}",
                daily_limit: limitVal
            }, function (res) {
                $('#editDailyLimitBox').addClass('d-none');
                $('#displayDailyLimit').text(limitVal > 0 ? limitVal + ' / day' : 'Unlimited');
                $('#displayTodaySent').text(res.today_sent_count + (limitVal > 0 ? ' / ' + limitVal : ''));
                notify('success', limitVal > 0 ? 'Daily message limit updated to ' + limitVal + ' msgs/day.' : 'Daily message limit removed (Unlimited).');
                log('🎯 Daily message limit updated: ' + (limitVal > 0 ? limitVal + ' msgs/day' : 'Unlimited'), 'info');
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
