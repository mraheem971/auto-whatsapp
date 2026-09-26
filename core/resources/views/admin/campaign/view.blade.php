@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    
    <!-- Left Col: Campaign Overview & Controls -->
    <div class="col-xl-4 col-lg-5">
        <div class="card b-radius--10 shadow-sm border-0 mb-4">
            <div class="card-header bg--primary text-white py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-bullhorn me-2 fs-4"></i> {{ __($campaign->name) }}
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted">@lang('Status')</span>
                    <span id="campaignStatusBadge" class="badge badge--dark text-capitalize">{{ $campaign->status }}</span>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted">@lang('Sender Account')</span>
                    <strong class="text-dark">{{ $account ? $account->account_name : $campaign->session_id }}</strong>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted">@lang('Target Type')</span>
                    <span class="badge badge--info text-capitalize">{{ str_replace('_', ' ', $campaign->target_type) }}</span>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted">@lang('Auto-Restart Loop')</span>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="toggleAutoRestart" {{ ($campaign->auto_restart ?? 1) ? 'checked' : '' }} style="cursor: pointer; width: 2.2em; height: 1.2em;">
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted">@lang('Broadcast Round')</span>
                    <span class="badge badge--primary fw-bold" id="statRound">Round #{{ ($campaign->loop_count ?? 0) + 1 }}</span>
                </div>

                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted"><i class="las la-clock me-1"></i> @lang('Anti-Ban Delay')</span>
                        <div>
                            <span class="badge bg-light text-dark border font-monospace" id="displayDelayRange">
                                {{ $campaign->min_delay_seconds }}-{{ $campaign->max_delay_seconds }}s Random
                            </span>
                            <button type="button" class="btn btn-xs btn-outline--secondary ms-1 py-0 px-1" id="btnEditDelay" style="font-size: 10px;">Edit</button>
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
                                <button type="button" class="btn btn-sm btn--primary w-100 p-1" id="btnSaveDelay" title="Save Delay"><i class="las la-save"></i></button>
                            </div>
                        </div>
                        <small class="text-muted" style="font-size: 10px;">Random delay in seconds before next message</small>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="fw-bold text-muted small mb-1">@lang('Message Content'):</label>
                    <div class="p-3 bg-light rounded border text-dark font-monospace small" style="white-space: pre-wrap; max-height: 160px; overflow-y: auto;">{{ $campaign->message }}</div>
                </div>

                <!-- Anti-Ban Human Delay Live Alert -->
                <div id="delayCountdownAlert" class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between d-none" style="border-left: 4px solid #0d6efd;">
                    <div class="d-flex align-items-center">
                        <i class="las la-shield-alt fs-3 me-2 text-primary"></i>
                        <div>
                            <strong class="d-block small text-dark">Anti-Ban Human Delay Active</strong>
                            <small class="text-muted">Next message in <span class="badge bg--primary text-white fw-bold" id="delayCountdownTimer">0s</span> <span class="text-secondary" id="delayRangeNotice">({{ $campaign->min_delay_seconds }}s - {{ $campaign->max_delay_seconds }}s delay)</span></small>
                        </div>
                    </div>
                    <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                </div>

                <!-- Live Progress Bar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span>@lang('Broadcast Progress')</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div id="progressBar" class="progress-bar bg--success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Counters -->
                <div class="row text-center g-2 mb-4">
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <h5 class="mb-0 text--primary fw-bold" id="totalCountDisplay">{{ count($targets) }}</h5>
                            <small class="text-muted" style="font-size: 11px;">@lang('Total')</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <h5 class="mb-0 text--success fw-bold" id="sentCountDisplay">{{ $campaign->sent_count }}</h5>
                            <small class="text-muted" style="font-size: 11px;">@lang('Sent')</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <h5 class="mb-0 text--danger fw-bold" id="failedCountDisplay">{{ $campaign->failed_count }}</h5>
                            <small class="text-muted" style="font-size: 11px;">@lang('Failed')</small>
                        </div>
                    </div>
                </div>

                <!-- Broadcast Action Controls -->
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn--success btn-lg fw-bold" id="btnStartBroadcast">
                        <i class="las la-play me-1"></i> @lang('Start Auto-Broadcast')
                    </button>
                    <button type="button" class="btn btn--warning btn-lg fw-bold d-none text-dark" id="btnPauseBroadcast">
                        <i class="las la-pause me-1"></i> @lang('Pause Broadcast')
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Col: Live Delivery Feed & Queue Table -->
    <div class="col-xl-8 col-lg-7">
        <div class="card b-radius--10 shadow-sm border-0">
            <div class="card-header bg--dark text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-list-alt me-2 fs-4 text--primary"></i> @lang('Live Broadcast Delivery Queue')
                </h5>
                <span class="badge bg--success fs-6" id="queueStatusBadge">@lang('Ready to Broadcast')</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>#</th>
                                <th>@lang('Target Name')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('WhatsApp JID')</th>
                                <th>@lang('Delivery Status')</th>
                            </tr>
                        </thead>
                        <tbody id="broadcastTableBody">
                            @forelse($targets as $index => $t)
                                <tr id="target_row_{{ $index }}" data-index="{{ $index }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong class="text-dark">{{ $t['name'] }}</strong>
                                    </td>
                                    <td>
                                        @if($t['type'] === 'group')
                                            <span class="badge badge--warning"><i class="las la-users me-1"></i> @lang('Group')</span>
                                        @else
                                            <span class="badge badge--primary"><i class="las la-user me-1"></i> @lang('Contact')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-monospace text-muted small">{{ $t['target_jid'] }}</span>
                                    </td>
                                    <td class="status-cell">
                                        <span class="badge bg-secondary">@lang('Queued')</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="las la-users-slash text-muted" style="font-size: 54px;"></i>
                                            <h5 class="text-dark fw-bold mt-2">@lang('No Target Contacts in Queue')</h5>
                                            <p class="text-muted mb-3">@lang('Please sync or extract WhatsApp groups and contacts to populate this broadcast queue.')</p>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('admin.contacts.sync.page') }}" class="btn btn-sm btn--primary">
                                                    <i class="las la-sync me-1"></i> @lang('Extract Groups & Contacts')
                                                </a>
                                                <a href="{{ route('admin.contacts.lists.index') }}" class="btn btn-sm btn-outline--dark">
                                                    <i class="las la-list me-1"></i> @lang('Manage Contact Lists')
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.campaigns.cron.manual') }}" class="btn btn-sm btn--warning text-dark fw-bold me-2" title="@lang('Trigger cron manually on localhost')">
        <i class="las la-clock me-1"></i> @lang('Run Cron Job')
    </a>
    <a href="{{ route('admin.campaigns.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-arrow-left me-1"></i> @lang('All Campaigns')
    </a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    const targets = @json($targets);
    const campaignId = "{{ $campaign->id }}";
    const totalTargets = targets.length;

    let pollInterval = null;
    let initialStatus = "{{ $campaign->status }}";
    let countdownTimer = null;
    let currentSecondsLeft = 0;

    function startCountdown(seconds){
        currentSecondsLeft = parseInt(seconds) || 0;
        if (countdownTimer) clearInterval(countdownTimer);

        if (currentSecondsLeft > 0) {
            $('#delayCountdownAlert').removeClass('d-none');
            $('#delayCountdownTimer').text(currentSecondsLeft + 's');

            countdownTimer = setInterval(function(){
                currentSecondsLeft--;
                if (currentSecondsLeft <= 0) {
                    clearInterval(countdownTimer);
                    countdownTimer = null;
                    $('#delayCountdownAlert').addClass('d-none');
                } else {
                    $('#delayCountdownTimer').text(currentSecondsLeft + 's');
                }
            }, 1000);
        } else {
            $('#delayCountdownAlert').addClass('d-none');
        }
    }

    function updateCounters(sent, failed, total, status, pct, round, autoRestart, secondsUntilNext, minD, maxD){
        $('#sentCountDisplay').text(sent);
        $('#failedCountDisplay').text(failed);
        $('#totalCountDisplay').text(total);
        $('#progressBar').css('width', pct + '%');
        $('#progressPercent').text(pct + '%');
        
        if (round) {
            $('#statRound').text('Round #' + round);
        }
        if (autoRestart !== undefined) {
            $('#toggleAutoRestart').prop('checked', !!autoRestart);
        }

        if (minD && maxD) {
            $('#displayDelayRange').text(minD + 's - ' + maxD + 's Random');
            $('#delayRangeNotice').text('(' + minD + 's - ' + maxD + 's delay)');
        }

        if (status === 'running') {
            $('#campaignStatusBadge').removeClass('badge--dark badge--warning badge--secondary').addClass('badge--success').text('Running (Round #' + (round || 1) + ')');
            $('#queueStatusBadge').removeClass('bg-secondary bg--info').addClass('bg--warning text-dark').html('<i class="fas fa-spinner fa-spin me-1"></i> Broadcasting Round #' + (round || 1) + '...');
            $('#btnStartBroadcast').addClass('d-none');
            $('#btnPauseBroadcast').removeClass('d-none');

            if (secondsUntilNext > 0) {
                startCountdown(secondsUntilNext);
            } else {
                if (countdownTimer) clearInterval(countdownTimer);
                $('#delayCountdownAlert').addClass('d-none');
            }
        } else if (status === 'completed') {
            if (countdownTimer) clearInterval(countdownTimer);
            $('#delayCountdownAlert').addClass('d-none');
            if (autoRestart) {
                $('#campaignStatusBadge').removeClass('badge--dark badge--warning').addClass('badge--info').text('Restarting Next Round...');
                $('#queueStatusBadge').removeClass('bg--warning text-dark').addClass('badge--info').text('Auto-Restarting Loop...');
            } else {
                $('#campaignStatusBadge').removeClass('badge--dark badge--warning').addClass('badge--success').text('Completed');
                $('#queueStatusBadge').removeClass('bg--warning text-dark').addClass('bg--success').text('Broadcast Completed');
                $('#btnPauseBroadcast').addClass('d-none');
                $('#btnStartBroadcast').removeClass('d-none').prop('disabled', true).html('<i class="las la-check me-1"></i> Completed');
                stopPolling();
            }
        } else if (status === 'paused') {
            if (countdownTimer) clearInterval(countdownTimer);
            $('#delayCountdownAlert').addClass('d-none');
            $('#campaignStatusBadge').removeClass('badge--success badge--dark badge--info').addClass('badge--warning').text('Paused');
            $('#queueStatusBadge').removeClass('bg--warning text-dark').addClass('bg--info').text('Broadcast Paused');
            $('#btnPauseBroadcast').addClass('d-none');
            $('#btnStartBroadcast').removeClass('d-none').html('<i class="las la-play me-1"></i> Resume Broadcast');
            stopPolling();
        }
    }

    function syncLogs(logs){
        if (!logs || !Array.isArray(logs)) return;
        logs.forEach(function(l){
            if (!l.target_jid) return;
            const targetIndex = targets.findIndex(t => t.target_jid === l.target_jid);
            if (targetIndex !== -1) {
                const row = $(`#target_row_${targetIndex}`);
                const statusCell = row.find('.status-cell');
                if (l.status === 'success') {
                    statusCell.html('<span class="badge badge--success"><i class="las la-check-circle me-1"></i> Delivered</span>');
                } else if (l.status === 'failed') {
                    statusCell.html(`<span class="badge badge--danger" title="${l.error || ''}"><i class="las la-times-circle me-1"></i> Failed</span>`);
                }
            }
        });
    }

    function pollStatus(){
        $.get("{{ url('admin/campaigns/live-status') }}/" + campaignId, function(res){
            if (res && res.success) {
                updateCounters(res.sent_count, res.failed_count, res.total_targets, res.status, res.progress_percent, res.current_round, res.auto_restart, res.seconds_until_next, res.min_delay, res.max_delay);
                syncLogs(res.logs);
                if (res.status === 'completed' && !res.auto_restart) {
                    stopPolling();
                }
            }
        });
    }

    function startPolling(){
        if (pollInterval) clearInterval(pollInterval);
        pollStatus();
        pollInterval = setInterval(pollStatus, 2500);
    }

    function stopPolling(){
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    $('#btnStartBroadcast').on('click', function(){
        if (totalTargets === 0){
            notify('warning', 'No targets in queue. Please extract or sync WhatsApp contacts first.');
            return;
        }

        $('#btnStartBroadcast').addClass('d-none');
        $('#btnPauseBroadcast').removeClass('d-none');
        $('#campaignStatusBadge').removeClass('badge--dark').addClass('badge--success').text('Running');
        $('#queueStatusBadge').removeClass('badge--info').addClass('badge--warning').text('Broadcasting in Background...');

        $.post("{{ route('admin.campaigns.start.auto', $campaign->id) }}", {
            _token: "{{ csrf_token() }}"
        }, function(res){
            notify('success', 'Automatic background broadcast launched! Server will send messages continuously.');
            startPolling();
        }).fail(function(xhr){
            notify('error', xhr.responseJSON ? xhr.responseJSON.message : 'Failed to launch background broadcast');
        });
    });

    $('#btnPauseBroadcast').on('click', function(){
        $.post("{{ url('admin/campaigns/update-status') }}/" + campaignId, {
            _token: "{{ csrf_token() }}",
            status: 'paused'
        }, function(){
            stopPolling();
            $('#btnPauseBroadcast').addClass('d-none');
            $('#btnStartBroadcast').removeClass('d-none').html('<i class="las la-play me-1"></i> Resume Broadcast');
            $('#campaignStatusBadge').removeClass('badge--success').addClass('badge--warning').text('Paused');
            $('#queueStatusBadge').removeClass('badge--warning').addClass('badge--info').text('Broadcast Paused');
            notify('info', 'Broadcast paused by user.');
        });
    });

    $('#toggleAutoRestart').on('change', function(){
        var isAuto = this.checked ? 1 : 0;
        $.post("{{ url('admin/campaigns/update-status') }}/" + campaignId, {
            _token: "{{ csrf_token() }}",
            auto_restart: isAuto
        }, function(res){
            notify('info', isAuto ? 'Auto-Restart Loop enabled: Broadcast will restart when completed.' : 'Auto-Restart Loop disabled: Broadcast will finish once.');
        });
    });

    $('#btnEditDelay').on('click', function(){
        $('#editDelayBox').toggleClass('d-none');
    });

    $('#btnSaveDelay').on('click', function(){
        var minVal = parseInt($('#inputMinDelay').val()) || 5;
        var maxVal = parseInt($('#inputMaxDelay').val()) || 15;
        if (maxVal < minVal) maxVal = minVal;

        $.post("{{ url('admin/campaigns/update-status') }}/" + campaignId, {
            _token: "{{ csrf_token() }}",
            min_delay: minVal,
            max_delay: maxVal
        }, function(res){
            $('#editDelayBox').addClass('d-none');
            $('#displayDelayRange').text(minVal + 's - ' + maxVal + 's Random');
            $('#delayRangeNotice').text('(' + minVal + 's - ' + maxVal + 's delay)');
            notify('success', 'Anti-ban delay updated to ' + minVal + 's - ' + maxVal + 's.');
        });
    });

    // Auto-start polling if already running or if auto-dispatched
    if (initialStatus === 'running') {
        startPolling();
    } else {
        // Initial log sync
        $.get("{{ url('admin/campaigns/live-status') }}/" + campaignId, function(res){
            if (res && res.success) {
                updateCounters(res.sent_count, res.failed_count, res.total_targets, res.status, res.progress_percent, res.current_round, res.auto_restart, res.seconds_until_next, res.min_delay, res.max_delay);
                syncLogs(res.logs);
            }
        });
    }

})(jQuery);
</script>
@endpush
