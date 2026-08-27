@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    
    <!-- Left Col: Campaign Control & Progress -->
    <div class="col-xl-4 col-lg-5">
        <div class="card b-radius--10 shadow-sm border-0 mb-4">
            <div class="card-header bg--primary text-white py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-rocket me-2 fs-4"></i> @lang('Campaign Control Center')
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">@lang('Campaign Name')</label>
                    <h5 class="fw-bold text-dark mb-0">{{ $campaign->name }}</h5>
                </div>

                <div class="mb-3">
                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">@lang('Sending Account')</label>
                    <div class="d-flex align-items-center text-dark">
                        <i class="lab la-whatsapp text--success fs-4 me-2"></i>
                        <span class="fw-bold">{{ $account->account_name ?? 'Connected WhatsApp' }} (+{{ $account->phone_number ?? '' }})</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">@lang('Target Audience')</label>
                    @if($campaign->target_type === 'groups')
                        <span class="badge badge--warning fs-6"><i class="las la-users me-1"></i> @lang('All WhatsApp Groups') ({{ count($targets) }})</span>
                    @elseif($campaign->target_type === 'contacts')
                        <span class="badge badge--primary fs-6"><i class="las la-user me-1"></i> @lang('All Direct Contacts') ({{ count($targets) }})</span>
                    @elseif($campaign->target_type === 'selected_group')
                        <span class="badge badge--info fs-6"><i class="las la-layer-group me-1"></i> @lang('Specific Group') ({{ count($targets) }})</span>
                    @else
                        <span class="badge badge--dark fs-6"><i class="las la-globe me-1"></i> @lang('All Combined') ({{ count($targets) }})</span>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">@lang('Anti-Ban Cooldown Delay')</label>
                    <span class="badge bg-light text-dark border fs-6">
                        <i class="las la-stopwatch text--primary me-1"></i> {{ $campaign->delay_seconds }} @lang('Seconds per Message')
                    </span>
                </div>

                <!-- Live Progress Card -->
                <div class="p-3 bg-light rounded border mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-dark">@lang('Delivery Progress')</span>
                        <span class="fw-bold text--primary" id="progressPercentage">0%</span>
                    </div>
                    <div class="progress mb-3" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg--success" id="campaignProgressBar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between text-center">
                        <div>
                            <small class="text-muted d-block">@lang('Sent')</small>
                            <span class="fw-bold text--success fs-5" id="counterSent">{{ $campaign->sent_count }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">@lang('Failed')</small>
                            <span class="fw-bold text--danger fs-5" id="counterFailed">{{ $campaign->failed_count }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">@lang('Remaining')</small>
                            <span class="fw-bold text--warning fs-5" id="counterRemaining">{{ count($targets) - $campaign->sent_count }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">@lang('Total')</small>
                            <span class="fw-bold text-dark fs-5">{{ count($targets) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Controls -->
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn--success btn-lg fw-bold py-3" id="btnStartBroadcast">
                        <i class="las la-paper-plane me-1"></i> @lang('Start Auto Broadcast')
                    </button>
                    <button type="button" class="btn btn--warning btn-lg fw-bold py-3 d-none" id="btnPauseBroadcast">
                        <i class="las la-pause-circle me-1"></i> @lang('Pause Broadcast')
                    </button>
                </div>
            </div>
        </div>

        <!-- Message Preview Card -->
        <div class="card b-radius--10 shadow-sm border-0">
            <div class="card-header bg--dark text-white py-2">
                <h6 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-comment-dots me-2"></i> @lang('Broadcast Message Preview')
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="p-3 rounded" style="background-color: #dcf8c6; border-left: 4px solid #25d366;">
                    <p class="mb-0 text-dark font-monospace" style="white-space: pre-wrap; font-size: 13px;">{{ $campaign->message }}</p>
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
    const delaySeconds = {{ $campaign->delay_seconds }};
    const totalTargets = targets.length;

    let currentIndex = 0;
    let isRunning = false;
    let sentCount = {{ $campaign->sent_count }};
    let failedCount = {{ $campaign->failed_count }};

    function updateProgressBar(){
        const totalProcessed = sentCount + failedCount;
        const pct = totalTargets > 0 ? Math.round((totalProcessed / totalTargets) * 100) : 0;
        $('#progressPercentage').text(pct + '%');
        $('#campaignProgressBar').css('width', pct + '%');
        $('#counterSent').text(sentCount);
        $('#counterFailed').text(failedCount);
        $('#counterRemaining').text(Math.max(0, totalTargets - totalProcessed));
    }

    updateProgressBar();

    $('#btnStartBroadcast').on('click', function(){
        if(isRunning) return;
        isRunning = true;
        $('#btnStartBroadcast').addClass('d-none');
        $('#btnPauseBroadcast').removeClass('d-none');
        $('#queueStatusBadge').removeClass('bg--success').addClass('badge--warning').text('Broadcasting in Progress...');
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
                    statusCell.append(` <small class="text-muted">(${delaySeconds}s delay)</small>`);
                    setTimeout(processNextTarget, delaySeconds * 1000);
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
                    setTimeout(processNextTarget, delaySeconds * 1000);
                }
            }
        });
    }

})(jQuery);
</script>
@endpush
