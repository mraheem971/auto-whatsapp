@extends('admin.layouts.app')

@push('style')
<style>
    /* ================= Enhanced Typography & Contrast for Campaign Creation ================= */
    .campaign-create-card {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
    }

    .campaign-create-card .card-header {
        background: linear-gradient(135deg, #4634ff 0%, #3020e3 100%) !important;
        border-top-left-radius: 12px !important;
        border-top-right-radius: 12px !important;
        padding: 16px 24px !important;
    }

    /* Form Labels: Crisp, Bold, High Contrast */
    .campaign-create-card label.form-label-enhanced {
        color: #0f172a !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em !important;
        margin-bottom: 6px !important;
        display: flex !important;
        align-items: center !important;
    }

    .campaign-create-card label.form-label-enhanced i {
        font-size: 17px !important;
        margin-right: 6px !important;
    }

    /* Form Controls & Selects: Solid Dark Text & Crisp Borders */
    .campaign-create-card .form-control,
    .campaign-create-card .form-select,
    .campaign-create-card textarea {
        color: #0f172a !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        background-color: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px !important;
        padding: 9px 13px !important;
        line-height: 1.5 !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
    }

    .campaign-create-card .form-control:focus,
    .campaign-create-card .form-select:focus,
    .campaign-create-card textarea:focus {
        border-color: #4634ff !important;
        box-shadow: 0 0 0 3px rgba(70, 52, 255, 0.2) !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        outline: none !important;
    }

    /* Placeholders: Legible Medium Slate */
    .campaign-create-card .form-control::placeholder,
    .campaign-create-card textarea::placeholder {
        color: #64748b !important;
        opacity: 1 !important;
        font-weight: 400 !important;
        font-size: 13.5px !important;
    }

    /* Input Group Text Badges: High Contrast, Slate-900 on Slate-100 */
    .campaign-create-card .input-group-text {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border: 1.5px solid #cbd5e1 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        padding: 8px 14px !important;
        display: flex !important;
        align-items: center !important;
    }

    .campaign-create-card .input-group > :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
        margin-left: -1.5px !important;
    }

    /* Helper Texts: Clear, Highly Legible Slate-700 */
    .campaign-create-card .text-muted,
    .campaign-create-card small.text-muted,
    .campaign-create-card .form-text,
    .campaign-create-card p.text-muted {
        color: #334155 !important;
        font-size: 12.5px !important;
        font-weight: 500 !important;
        line-height: 1.55 !important;
    }

    .campaign-create-card .text-muted i {
        font-size: 14px !important;
    }

    /* Section Cards Inside Form */
    .campaign-create-card .bg-light-section {
        background-color: #f8fafc !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 8px !important;
    }

    /* Personalization Tag Buttons */
    .campaign-create-card .btn-tag {
        background-color: #ffffff !important;
        color: #1e293b !important;
        border: 1.5px solid #cbd5e1 !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        padding: 4px 10px !important;
        border-radius: 6px !important;
        transition: all 0.15s ease !important;
    }

    .campaign-create-card .btn-tag:hover {
        background-color: #4634ff !important;
        color: #ffffff !important;
        border-color: #4634ff !important;
    }
</style>
@endpush

@section('panel')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card campaign-create-card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="card-title text-white mb-0 d-flex align-items-center fw-bold">
                    <i class="las la-bullhorn me-2 fs-4"></i> @lang('Create WhatsApp Marketing Campaign')
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.campaigns.cron.manual') }}" class="btn btn-xs btn--warning text-dark fw-bold" title="@lang('Trigger cron job manually on localhost')">
                        <i class="las la-clock me-1"></i> @lang('Run Cron Job')
                    </a>
                    <a href="{{ route('admin.campaigns.index') }}" class="btn btn-xs btn-outline-light fw-semibold">
                        <i class="las la-list me-1"></i> @lang('All Campaigns')
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.campaigns.store') }}" method="POST">
                @csrf
                <div class="card-body p-3 p-sm-4">
                    <div class="row g-3">
                        
                        <!-- ================= LEFT COLUMN ================= -->
                        <!-- 1. Campaign Name -->
                        <div class="col-lg-6 col-md-12">
                            <label class="form-label-enhanced">
                                <i class="las la-font text--primary"></i> @lang('Campaign Name') <span class="text--danger ms-1">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" placeholder="@lang('e.g. Weekly Deals Broadcast 2026')" value="{{ old('name') }}" required>
                        </div>

                        <!-- ================= RIGHT COLUMN ================= -->
                        <!-- 1. Sender Account -->
                        <div class="col-lg-6 col-md-12">
                            <label class="form-label-enhanced">
                                <i class="lab la-whatsapp text--success fs-5"></i> @lang('Sender WhatsApp Account') <span class="text--danger ms-1">*</span>
                            </label>
                            <select name="session_id" class="form-control form-select" required>
                                @forelse($connectedAccounts as $acc)
                                    <option value="{{ $acc->session_id }}">
                                        {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Connected' }})
                                    </option>
                                @empty
                                    <option value="" disabled selected>@lang('No active WhatsApp account connected')</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- ================= LEFT COLUMN ================= -->
                        <!-- 2. Target Audience Dropdown -->
                        <div class="col-lg-6 col-md-12">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label-enhanced mb-0">
                                    <i class="las la-users text--primary"></i> @lang('Target Audience / Contact List') <span class="text--danger ms-1">*</span>
                                </label>
                                <a href="{{ route('admin.contacts.lists.index') }}" class="small text--primary text-decoration-none fw-bold">
                                    <i class="las la-cog me-1"></i>@lang('Manage Lists')
                                </a>
                            </div>
                            <select name="target_type" id="target_type" class="form-control form-select" required>
                                @if(isset($contactLists) && $contactLists->count() > 0)
                                    <optgroup label="@lang('My Contact Lists')">
                                        @foreach($contactLists as $lst)
                                            <option value="list_{{ $lst->id }}" {{ (request('list_id') == $lst->id || $loop->first) ? 'selected' : '' }}>
                                                📁 {{ $lst->name }} ({{ $lst->contacts_count }} {{ $lst->type === 'groups' ? trans('Groups') : trans('Contacts') }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @else
                                    <option value="" disabled selected>@lang('No contact lists found. Please extract or create a list first.')</option>
                                @endif
                                <optgroup label="@lang('Custom Selection')">
                                    <option value="selected_groups">🎯 @lang('Select Multiple Specific Groups')</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- ================= RIGHT COLUMN ================= -->
                        <!-- 2. Anti-Ban Random Delay Time Range (Min - Max) -->
                        <div class="col-lg-6 col-md-12">
                            <label class="form-label-enhanced">
                                <i class="las la-stopwatch text--primary"></i> @lang('Anti-Ban Random Delay Range (Seconds)') <span class="text--danger ms-1">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="las la-stopwatch me-1 text--primary"></i> Min</span>
                                <input type="number" name="min_delay" id="min_delay" class="form-control text-center fw-bold" value="30" min="1" max="600" required placeholder="30">
                                <span class="input-group-text">to Max</span>
                                <input type="number" name="max_delay" id="max_delay" class="form-control text-center fw-bold" value="60" min="1" max="600" required placeholder="60">
                                <span class="input-group-text">Seconds</span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="las la-shield-alt text--success me-1"></i>@lang('Random delay between Min and Max seconds (default 30s - 60s) for natural human pace.')
                            </small>
                        </div>

                        <!-- ================= LEFT COLUMN ================= -->
                        <!-- 3. Anti-Ban Batch Pause & Reset Rules -->
                        <div class="col-lg-6 col-md-12">
                            <label class="form-label-enhanced">
                                <i class="las la-layer-group text--primary"></i> @lang('Anti-Ban Batch Pause & Cycle Reset') <span class="text--danger ms-1">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Pause</span>
                                <input type="number" name="delay_after_duration" id="delay_after_duration" class="form-control text-center fw-bold" value="5" min="1" max="3600" required placeholder="5">
                                <span class="input-group-text">s after</span>
                                <input type="number" name="delay_after_count" id="delay_after_count" class="form-control text-center fw-bold" value="50" min="1" max="5000" required placeholder="50">
                                <span class="input-group-text">msgs</span>
                            </div>
                            <div class="input-group mt-2">
                                <span class="input-group-text">Reset Cycle After</span>
                                <input type="number" name="reset_after_count" id="reset_after_count" class="form-control text-center fw-bold" value="100" min="1" max="10000" required placeholder="100">
                                <span class="input-group-text">msgs (repeats cycle)</span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="las la-info-circle text--primary me-1"></i>@lang('Pauses for 5s after every 50 messages, and resets batch cycle counter at 100 messages.')
                            </small>
                        </div>

                        <!-- ================= RIGHT COLUMN ================= -->
                        <!-- 3. Target Message Limit Per Day -->
                        <div class="col-lg-6 col-md-12">
                            <label class="form-label-enhanced">
                                <i class="las la-calendar-check text--primary"></i> @lang('Target Message Limit Per Day')
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="las la-calendar-check me-1 text--primary"></i> Limit</span>
                                <input type="number" name="daily_limit" id="daily_limit" class="form-control fw-bold" min="0" max="50000" placeholder="@lang('e.g. 100 (0 or empty = unlimited)')" value="{{ old('daily_limit') }}">
                                <span class="input-group-text">msgs / day</span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="las la-info-circle text--primary me-1"></i>@lang('Max messages sent per day. When reached, broadcast safely pauses and automatically resumes tomorrow.')
                            </small>
                        </div>

                        <!-- ================= LEFT COLUMN ================= -->
                        <!-- 4. Broadcast Execution Mode -->
                        <div class="col-lg-6 col-md-12">
                            <label class="form-label-enhanced">
                                <i class="las la-rocket text--primary"></i> @lang('Broadcast Execution Mode') <span class="text--danger ms-1">*</span>
                            </label>
                            <select name="dispatch_mode" id="dispatch_mode" class="form-control form-select" required>
                                <option value="auto" selected>⚡ @lang('Automatic Background Broadcast (Server runs continuously without keeping browser open)')</option>
                                <option value="manual">🖥 @lang('Interactive In-Browser Broadcast (Live real-time feed on screen)')</option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                <i class="las la-info-circle text--primary me-1"></i>@lang('In Automatic Mode, the campaign will run continuously on the server until completed or paused by you.')
                            </small>
                        </div>

                        <!-- ================= RIGHT COLUMN ================= -->
                        <!-- 4. Auto-Restart Loop Option -->
                        <div class="col-lg-6 col-md-12">
                            <label class="form-label-enhanced">
                                <i class="las la-sync text--success"></i> @lang('Broadcast Loop Control')
                            </label>
                            <div class="p-2 px-3 bg-light-section d-flex align-items-center justify-content-between" style="min-height: 48px;">
                                <div>
                                    <label class="fw-bold text-dark mb-0 cursor-pointer small" for="autoRestartCheck">
                                        @lang('Auto-Restart Broadcast Loop')
                                    </label>
                                    <small class="text-muted d-block" style="font-size: 11px;">@lang('Broadcast starts again from beginning when finished.')</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="auto_restart" id="autoRestartCheck" value="1" checked style="width: 2.5em; height: 1.3em; cursor: pointer;">
                                </div>
                            </div>
                        </div>

                        <!-- Multiple Specific Groups Selector (Hidden by default) -->
                        <div class="col-12 d-none" id="multiple_groups_wrapper">
                            <div class="card border bg-light-section">
                                <div class="card-header bg-white d-flex align-items-center justify-content-between py-2 flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-dark"><i class="las la-tasks text--primary me-1"></i> @lang('Select Target WhatsApp Groups'):</span>
                                        <span class="badge badge--primary" id="selectedGroupsCount">0 @lang('Selected')</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-xs btn-outline--dark" id="btnCheckAllGroups">@lang('Select All')</button>
                                        <button type="button" class="btn btn-xs btn-outline--secondary" id="btnUncheckAllGroups">@lang('Deselect All')</button>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="mb-3">
                                        <input type="text" id="filterGroupsSearch" class="form-control form-control-sm" placeholder="@lang('Filter group names...')">
                                    </div>
                                    <div class="row g-2" id="groupsChecklist" style="max-height: 260px; overflow-y: auto;">
                                        @forelse($groups as $g)
                                            <div class="col-lg-4 col-md-6 group-item-col">
                                                <label class="p-2 border rounded bg-white w-100 d-flex align-items-center justify-content-between mb-0 cursor-pointer hover-shadow">
                                                    <div class="d-flex align-items-center text-truncate me-2">
                                                        <input type="checkbox" name="target_group_ids[]" value="{{ $g->group_id }}" class="form-check-input group-chk me-2">
                                                        <div>
                                                            <strong class="text-dark d-block text-truncate group-name-label" style="max-width: 220px;">{{ $g->group_name }}</strong>
                                                            <span class="font-monospace text-muted" style="font-size: 10px;">{{ $g->group_id }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="badge badge--info">{{ $g->member_count }} @lang('Members')</span>
                                                </label>
                                            </div>
                                        @empty
                                            <div class="col-12 text-center text-muted py-3">@lang('No WhatsApp groups found. Please sync groups first.')</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Message Content & Template Loader -->
                        <div class="col-12 mt-2">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label-enhanced mb-0">
                                    <i class="las la-comment-dots text--primary"></i> @lang('Broadcast Message Content') <span class="text--danger ms-1">*</span>
                                </label>
                                
                                @if($templates->count() > 0)
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-outline--secondary dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown">
                                        <i class="las la-envelope-open-text me-1"></i> @lang('Load Template')
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="max-height: 220px; overflow-y: auto;">
                                        @foreach($templates as $tpl)
                                            <li>
                                                <a class="dropdown-item py-2 border-bottom btnApplyTpl" href="javascript:void(0)" data-msg="{{ $tpl->message }}">
                                                    <strong class="d-block text-dark">{{ $tpl->title }}</strong>
                                                    <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $tpl->message }}</small>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>

                            <textarea name="message" id="campaign_message" rows="6" class="form-control" placeholder="@lang('Write the broadcast message to send across all members/groups of this list... Use tags like {name} and {phone} for personalization.')" required>{{ old('message') }}</textarea>
                            
                            <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                <span class="text-muted small fw-bold">@lang('Personalization tags'):</span>
                                <button type="button" class="btn btn-tag" data-tag="{name}">{name}</button>
                                <button type="button" class="btn btn-tag" data-tag="{phone}">{phone}</button>
                                <button type="button" class="btn btn-tag" data-tag="{group_name}">{group_name}</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.campaigns.index') }}" class="btn btn--dark btn-sm px-3 fw-semibold">
                        <i class="las la-arrow-left me-1"></i> @lang('Cancel')
                    </a>
                    <button type="submit" class="btn btn--primary btn-sm px-4 fw-bold">
                        <i class="las la-rocket me-1"></i> @lang('Create & Launch Campaign')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function($){
    "use strict";

    $('#target_type').on('change', function(){
        const val = $(this).val();

        if(val === 'selected_groups'){
            $('#multiple_groups_wrapper').removeClass('d-none');
        } else {
            $('#multiple_groups_wrapper').addClass('d-none');
        }
    });

    // Checklist buttons
    $('#btnCheckAllGroups').on('click', function(){
        $('.group-chk:visible').prop('checked', true);
        updateGroupCounter();
    });

    $('#btnUncheckAllGroups').on('click', function(){
        $('.group-chk:visible').prop('checked', false);
        updateGroupCounter();
    });

    $(document).on('change', '.group-chk', function(){
        updateGroupCounter();
    });

    function updateGroupCounter(){
        const count = $('.group-chk:checked').length;
        $('#selectedGroupsCount').text(`${count} Selected`);
    }

    // Filter groups search
    $('#filterGroupsSearch').on('keyup', function(){
        const term = $(this).val().toLowerCase().trim();
        $('.group-item-col').each(function(){
            const text = $(this).find('.group-name-label').text().toLowerCase();
            if(text.includes(term)){
                $(this).removeClass('d-none');
            } else {
                $(this).addClass('d-none');
            }
        });
    });

    // Template application
    $('.btnApplyTpl').on('click', function(e){
        e.preventDefault();
        const msg = $(this).data('msg');
        $('#campaign_message').val(msg);
    });

    $('.btn-tag').on('click', function(){
        const tag = $(this).data('tag');
        const textarea = $('#campaign_message');
        const pos = textarea.prop('selectionStart');
        const val = textarea.val();
        textarea.val(val.substring(0, pos) + tag + val.substring(pos));
        textarea.focus();
    });

})(jQuery);
</script>
@endpush
