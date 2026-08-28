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
                    <span class="text-muted">@lang('Anti-Ban Cooldown')</span>
                    <span class="badge bg-light text-dark border font-monospace">
                        {{ $campaign->min_delay ?: $campaign->delay_seconds }}-{{ $campaign->max_delay ?: ($campaign->delay_seconds + 5) }}s Random
                    </span>
                </div>

                <div class="mb-4">
                    <label class="fw-bold text-muted small mb-1">@lang('Message Content'):</label>
                    <div class="p-3 bg-light rounded border text-dark font-monospace small" style="white-space: pre-wrap; max-height: 160px; overflow-y: auto;">{{ $campaign->message }}</div>
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
                            @foreach($targets as $index => $t)
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
                            @endforeach
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
    const minDelay = {{ $campaign->min_delay ?: ($campaign->delay_seconds ?: 5) }};
    const maxDelay = {{ $campaign->max_delay ?: ($campaign->delay_seconds ?: 15) }};
    const totalTargets = targets.length;

    let currentIndex = 0;
    let isRunning = false;
    let sentCount = {{ $campaign->sent_count }};
    let failedCount = {{ $campaign->failed_count }};

    function getRandomDelay() {
        return Math.floor(Math.random() * (maxDelay - minDelay + 1)) + minDelay;
    }

    function updateProgressBar(){
        const totalProcessed = sentCount + failedCount;
        const pct = totalTargets > 0 ? Math.round((totalProcessed / totalTargets) * 100) : 0;
        $('#progressBar').css('width', pct + '%');
        $('#progressPercent').text(pct + '%');
        $('#sentCountDisplay').text(sentCount);
        $('#failedCountDisplay').text(failedCount);
    }

    $('#btnStartBroadcast').on('click', function(){
        if(totalTargets === 0){
            notify('warning', 'No targets in queue to broadcast.');
            return;
        }

        isRunning = true;
        $('#btnStartBroadcast').addClass('d-none');
        $('#btnPauseBroadcast').removeClass('d-none');
        $('#campaignStatusBadge').removeClass('badge--dark').addClass('badge--success').text('Running');
        $('#queueStatusBadge').removeClass('badge--info').addClass('badge--warning').text('Broadcasting live...');

        $.post("{{ url('admin/campaigns/update-status') }}/" + campaignId, {
            _token: "{{ csrf_token() }}",
            status: 'running'
        });

        processNextTarget();
    });

    $('#btnPauseBroadcast').on('click', function(){
        isRunning = false;
        $('#btnPauseBroadcast').addClass('d-none');
        $('#btnStartBroadcast').removeClass('d-none').html('<i class="las la-play me-1"></i> Resume Broadcast');
        $('#queueStatusBadge').removeClass('badge--warning').addClass('badge--info').text('Broadcast Paused');
    });

    function processNextTarget(){
        if(!isRunning) return;

        if(currentIndex >= totalTargets){
            isRunning = false;
            $('#btnPauseBroadcast').addClass('d-none');
            $('#btnStartBroadcast').removeClass('d-none').prop('disabled', true).html('<i class="las la-check me-1"></i> Completed');
            $('#queueStatusBadge').removeClass('badge--warning').addClass('badge--success').text('Broadcast Completed');
            notify('success', 'Campaign broadcast completed successfully!');

            $.post("{{ url('admin/campaigns/update-status') }}/" + campaignId, {
                _token: "{{ csrf_token() }}",
                status: 'completed'
            });
            return;
        }

        const target = targets[currentIndex];
        const row = $(`#target_row_${currentIndex}`);
        const statusCell = row.find('.status-cell');

        statusCell.html('<span class="badge badge--info"><i class="fas fa-spinner fa-spin me-1"></i> Sending...</span>');
        row.addClass('table-active');

        // Send message to this recipient
        $.ajax({
            url: "{{ url('admin/campaigns/send-single') }}/" + campaignId,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                target_jid: target.target_jid,
                name: target.name,
                type: target.type,
                group_name: target.group_name
            },
            success: function(res){
                row.removeClass('table-active');
                if(res.success){
                    sentCount++;
                    statusCell.html('<span class="badge badge--success"><i class="las la-check-circle me-1"></i> Delivered</span>');
                } else {
                    failedCount++;
                    statusCell.html(`<span class="badge badge--danger" title="${res.error || ''}"><i class="las la-times-circle me-1"></i> Failed</span>`);
                }

                updateProgressBar();
                currentIndex++;

                if(isRunning && currentIndex < totalTargets){
                    const waitDelay = getRandomDelay();
                    statusCell.append(` <small class="text-muted">(${waitDelay}s anti-ban delay)</small>`);
                    setTimeout(processNextTarget, waitDelay * 1000);
                } else if(currentIndex >= totalTargets){
                    processNextTarget();
                }
            },
            error: function(xhr){
                row.removeClass('table-active');
                failedCount++;
                statusCell.html('<span class="badge badge--danger"><i class="las la-times-circle me-1"></i> Error</span>');
                updateProgressBar();
                currentIndex++;

                if(isRunning && currentIndex < totalTargets){
                    const waitDelay = getRandomDelay();
                    setTimeout(processNextTarget, waitDelay * 1000);
                }
            }
        });
    }

    updateProgressBar();

})(jQuery);
</script>
@endpush
