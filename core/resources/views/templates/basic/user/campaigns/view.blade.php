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
        var minDelay = {{ (int) $campaign->min_delay_seconds }};
        var maxDelay = {{ (int) $campaign->max_delay_seconds }};
        var isRunning = false;
        var currentIndex = 0;
        var sentCount = 0;
        var failedCount = 0;

        function log(msg, type) {
            var color = type === 'success' ? '#28c76f' : (type === 'error' ? '#ea5455' : '#7367f0');
            var time = new Date().toLocaleTimeString();
            $('#campaignTerminal').append('<div style="color:' + color + '">[' + time + '] ' + msg + '</div>');
            var term = document.getElementById('campaignTerminal');
            term.scrollTop = term.scrollHeight;
        }

        function updateProgress() {
            var sent = sentCount;
            var pct = total > 0 ? Math.round(((sent + failedCount) / total) * 100) : 100;
            $('#progressBar').css('width', pct + '%');
            $('#progressPercent').text(pct + '%');
            $('#progressText').text((sent + failedCount) + ' of ' + total + ' processed');
            $('#statSent').text(sent);
            $('#statFailed').text(failedCount);
            $('#statRemaining').text(Math.max(0, total - (sent + failedCount)));
        }

        async function sendNext() {
            if (!isRunning || currentIndex >= total) {
                if (currentIndex >= total && total > 0) {
                    isRunning = false;
                    $('#campaignStatusBadge').removeClass('bg-primary').addClass('bg-success').text('COMPLETED');
                    $('#btnStartCampaign').removeClass('d-none').html('<i class="las la-check me-1"></i> Broadcast Finished').prop('disabled', true);
                    $('#btnPauseCampaign').addClass('d-none');
                    log('🎉 All ' + total + ' broadcast messages processed successfully!', 'success');
                }
                return;
            }

            var target = targets[currentIndex];
            var delay = Math.floor(Math.random() * (maxDelay - minDelay + 1)) + minDelay;

            log('⏳ Anti-ban wait: ' + delay + 's before sending to ' + (target.phone || target.name || target.target_jid) + '...', 'info');

            await new Promise(r => setTimeout(r, delay * 1000));

            if (!isRunning) return;

            log('🚀 Dispatching message to +' + (target.phone || target.name) + '...', 'info');

            $.ajax({
                url: "{{ route('user.campaigns.send.single', $campaign->id) }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    target_jid: target.target_jid,
                    name: target.name,
                    phone: target.phone
                },
                success: function (res) {
                    if (res.success) {
                        sentCount++;
                        log('✔ Sent successfully to ' + (target.phone || target.name), 'success');
                    } else {
                        failedCount++;
                        log('✖ Failed: ' + (res.error || 'Unknown error'), 'error');
                    }
                    currentIndex++;
                    updateProgress();
                    sendNext();
                },
                error: function (xhr) {
                    failedCount++;
                    log('✖ Dispatch error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Network error'), 'error');
                    currentIndex++;
                    updateProgress();
                    sendNext();
                }
            });
        }

        $('#btnStartCampaign').on('click', function () {
            isRunning = true;
            $('#campaignStatusBadge').removeClass('bg-secondary bg-warning').addClass('bg-primary').text('RUNNING');
            $('#btnStartCampaign').addClass('d-none');
            $('#btnPauseCampaign').removeClass('d-none');
            log('▶ Campaign broadcast started.', 'info');
            sendNext();
        });

        $('#btnPauseCampaign').on('click', function () {
            isRunning = false;
            $('#campaignStatusBadge').removeClass('bg-primary').addClass('bg-warning').text('PAUSED');
            $('#btnPauseCampaign').addClass('d-none');
            $('#btnStartCampaign').removeClass('d-none').html('<i class="las la-play me-1"></i> Resume Broadcast');
            log('⏸ Broadcast paused by user.', 'info');
        });

    })(jQuery);
</script>
@endpush
