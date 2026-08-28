@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-12">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--primary text-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-bullhorn me-2 fs-4"></i> @lang('Create WhatsApp Marketing Campaign')
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.campaigns.cron.manual') }}" class="btn btn-xs btn--warning text-dark fw-bold" title="@lang('Trigger cron job manually on localhost')">
                        <i class="las la-clock me-1"></i> @lang('Run Cron Job')
                    </a>
                    <a href="{{ route('admin.campaigns.index') }}" class="btn btn-xs btn-outline-light">
                        <i class="las la-list me-1"></i> @lang('All Campaigns')
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.campaigns.store') }}" method="POST">
                @csrf
                <div class="card-body p-4">
                    <div class="row gy-3">
                        
                        <!-- Campaign Name -->
                        <div class="col-lg-6 col-md-12">
                            <label class="fw-bold mb-1">@lang('Campaign Name') <span class="text--danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="@lang('e.g. Weekly Deals Broadcast 2026')" value="{{ old('name') }}" required>
                        </div>

                        <!-- Sender Account -->
                        <div class="col-lg-6 col-md-12">
                            <label class="fw-bold mb-1">@lang('Sender WhatsApp Account') <span class="text--danger">*</span></label>
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

                        <!-- Dynamic Target Audience Dropdown -->
                        <div class="col-lg-6 col-md-12">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="fw-bold mb-0">@lang('Target Audience / Contact List') <span class="text--danger">*</span></label>
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

                        <!-- Anti-Ban Random Delay Time Range (Min - Max) -->
                        <div class="col-lg-6 col-md-12">
                            <label class="fw-bold mb-1">@lang('Anti-Ban Random Delay Range (Seconds)') <span class="text--danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white small fw-bold"><i class="las la-stopwatch me-1 text--primary"></i>Min</span>
                                <input type="number" name="min_delay" id="min_delay" class="form-control" value="5" min="1" max="60" required placeholder="5">
                                <span class="input-group-text bg-white small fw-bold">to</span>
                                <input type="number" name="max_delay" id="max_delay" class="form-control" value="15" min="2" max="120" required placeholder="15">
                                <span class="input-group-text bg-light small">Seconds</span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="las la-shield-alt text--success me-1"></i>@lang('Random delay between Min and Max seconds will be chosen for each message to mimic human behavior and avoid WhatsApp ban.')
                            </small>
                        </div>

                        <!-- Multiple Specific Groups Selector (Hidden by default) -->
                        <div class="col-12 d-none" id="multiple_groups_wrapper">
                            <div class="card border bg-light">
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
                        <div class="col-12 mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="fw-bold mb-0">@lang('Broadcast Message Content') <span class="text--danger">*</span></label>
                                
                                @if($templates->count() > 0)
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-outline--secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="las la-envelope-open-text me-1"></i> @lang('Load Template')
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" style="max-height: 220px; overflow-y: auto;">
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

                            <textarea name="message" id="campaign_message" rows="6" class="form-control" placeholder="@lang('Write the broadcast message to send across all members/groups of this list...')" required>{{ old('message') }}</textarea>
                            
                            <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                <span class="text-muted small fw-bold">@lang('Personalization tags'):</span>
                                <button type="button" class="btn btn-xs btn-outline--secondary btn-tag" data-tag="{name}">{name}</button>
                                <button type="button" class="btn btn-xs btn-outline--secondary btn-tag" data-tag="{phone}">{phone}</button>
                                <button type="button" class="btn btn-xs btn-outline--secondary btn-tag" data-tag="{group_name}">{group_name}</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.campaigns.index') }}" class="btn btn--dark btn-sm px-3">
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
