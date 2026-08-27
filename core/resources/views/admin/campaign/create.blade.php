@extends('admin.layouts.app')

@section('panel')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--primary text-white py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-bullhorn me-2 fs-4"></i> @lang('Create WhatsApp Auto Broadcast Campaign')
                </h5>
            </div>
            <form action="{{ route('admin.campaigns.store') }}" method="POST">
                @csrf
                <div class="card-body p-4">
                    <div class="row gy-3">
                        
                        <!-- Campaign Name -->
                        <div class="col-md-7">
                            <label class="fw-bold mb-1">@lang('Campaign Name') <span class="text--danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="@lang('e.g. Group Promo Announcement 2026')" value="{{ old('name') }}" required>
                        </div>

                        <!-- Sender Account -->
                        <div class="col-md-5">
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

                        <!-- Target Audience -->
                        <div class="col-md-7">
                            <label class="fw-bold mb-1">@lang('Target Audience') <span class="text--danger">*</span></label>
                            <select name="target_type" id="target_type" class="form-control form-select" required>
                                <option value="groups" selected>📢 @lang('All WhatsApp Groups') ({{ $totalGroups }} @lang('Groups'))</option>
                                <option value="contacts">👤 @lang('All Direct Contacts') ({{ $totalContacts }} @lang('Contacts'))</option>
                                <option value="all">🌐 @lang('All Groups & Contacts Combined')</option>
                                <option value="selected_group">🎯 @lang('Specific Single Group')</option>
                            </select>
                        </div>

                        <!-- Anti-Ban Delay -->
                        <div class="col-md-5">
                            <label class="fw-bold mb-1">@lang('Anti-Ban Delay (Seconds)') <span class="text--danger">*</span></label>
                            <input type="number" name="delay_seconds" class="form-control" value="5" min="2" max="60" required>
                            <small class="text-muted"><i class="las la-shield-alt me-1 text--success"></i>@lang('Recommended 5-10s between messages')</small>
                        </div>

                        <!-- Specific Group Select (Hidden by default) -->
                        <div class="col-12 d-none" id="specific_group_wrapper">
                            <label class="fw-bold mb-1">@lang('Select Specific WhatsApp Group')</label>
                            <select name="target_group_id" id="target_group_id" class="form-control form-select">
                                <option value="">@lang('-- Choose Group --')</option>
                                @foreach($groups as $g)
                                    <option value="{{ $g->group_id }}">
                                        {{ $g->group_name }} ({{ $g->member_count }} @lang('Members')) - {{ $g->group_id }}
                                    </option>
                                @endforeach
                            </select>
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

                            <textarea name="message" id="campaign_message" rows="6" class="form-control" placeholder="@lang('Write the message you want to broadcast across all target groups/contacts...')" required>{{ old('message') }}</textarea>
                            
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
                        <i class="las la-rocket me-1"></i> @lang('Create & Prepare Campaign')
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
        if($(this).val() === 'selected_group'){
            $('#specific_group_wrapper').removeClass('d-none');
            $('#target_group_id').prop('required', true);
        } else {
            $('#specific_group_wrapper').addClass('d-none');
            $('#target_group_id').prop('required', false);
        }
    });

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
