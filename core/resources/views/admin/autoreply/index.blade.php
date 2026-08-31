@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <!-- Stat Counters -->
    <div class="col-12 mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <div class="card p-3 border shadow-sm d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold">@lang('Total Auto-Reply Bots')</span>
                        <h4 class="mb-0 fw-bold text--primary">{{ $totalBots }}</h4>
                    </div>
                    <div class="rounded-circle bg-light p-3 text--primary fs-3">
                        <i class="las la-robot"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 border shadow-sm d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold">@lang('Active Bots')</span>
                        <h4 class="mb-0 fw-bold text--success">{{ $activeBots }}</h4>
                    </div>
                    <div class="rounded-circle bg-light p-3 text--success fs-3">
                        <i class="las la-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 border shadow-sm d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold">@lang('Total Triggered Hits')</span>
                        <h4 class="mb-0 fw-bold text--info">{{ number_format($totalHits) }}</h4>
                    </div>
                    <div class="rounded-circle bg-light p-3 text--info fs-3">
                        <i class="las la-paper-plane"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main List Card -->
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <!-- Filter Header -->
            <div class="card-header bg-white py-3">
                <form action="" method="GET">
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <!-- Account Filter -->
                            <select name="session_id" class="form-control form-control-sm form-select" onchange="this.form.submit()" style="max-width: 220px;">
                                <option value="">🌐 @lang('All Accounts')</option>
                                <option value="all" {{ request('session_id') == 'all' ? 'selected' : '' }}>🌐 @lang('Universal (All Accounts)')</option>
                                @foreach($connectedAccounts as $acc)
                                    <option value="{{ $acc->session_id }}" {{ request('session_id') == $acc->session_id ? 'selected' : '' }}>
                                        {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Active' }})
                                    </option>
                                @endforeach
                            </select>

                            <!-- Status Filter -->
                            <select name="status" class="form-control form-control-sm form-select" onchange="this.form.submit()" style="max-width: 140px;">
                                <option value="">@lang('All Status')</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>@lang('Active')</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>@lang('Inactive')</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="input-group input-group-sm" style="max-width: 280px;">
                            <input type="text" name="search" class="form-control bg-white" placeholder="@lang('Search keyword or rule...')" value="{{ request('search') }}">
                            <button class="btn btn--primary" type="submit"><i class="la la-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th class="ps-3 py-2">@lang('Bot Name & Account')</th>
                                <th class="py-2">@lang('Target Audience')</th>
                                <th class="py-2">@lang('Match Type & Trigger Keywords')</th>
                                <th class="py-2">@lang('Human-Like Flow Sequence')</th>
                                <th class="py-2">@lang('Reply Message Preview')</th>
                                <th class="py-2 text-center">@lang('Hits')</th>
                                <th class="py-2 text-center">@lang('Status')</th>
                                <th class="text-end pe-3 py-2">@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($botRules as $bot)
                                <tr>
                                    <td class="ps-3 py-2">
                                        <div class="fw-bold text-dark">{{ $bot->name }}</div>
                                        <div class="text-muted small">
                                            @if($bot->account)
                                                <i class="lab la-whatsapp text--success me-1"></i>{{ $bot->account->account_name }} ({{ $bot->account->phone_number ? '+' . $bot->account->phone_number : 'Active' }})
                                            @else
                                                <i class="las la-globe text--primary me-1"></i>@lang('Universal (All Accounts)')
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        @if($bot->target_type === 'all' || empty($bot->target_type))
                                            <span class="badge badge--dark px-2 py-1"><i class="las la-globe me-1"></i>@lang('All Chats (Everyone)')</span>
                                        @elseif($bot->target_type === 'all_individual')
                                            <span class="badge badge--primary px-2 py-1"><i class="las la-user me-1"></i>@lang('1-on-1 Direct Chats')</span>
                                        @elseif($bot->target_type === 'saved_contacts')
                                            <span class="badge badge--success px-2 py-1"><i class="las la-address-book me-1"></i>@lang('Saved Contacts Only')</span>
                                        @elseif($bot->target_type === 'unsaved_contacts')
                                            <span class="badge badge--warning px-2 py-1"><i class="las la-user-plus me-1"></i>@lang('Non-Saved Numbers Only')</span>
                                        @elseif($bot->target_type === 'all_group')
                                            <span class="badge badge--info px-2 py-1"><i class="las la-users me-1"></i>@lang('All Groups')</span>
                                        @elseif($bot->target_type === 'specific_contacts')
                                            <span class="badge badge--success px-2 py-1" title="{{ is_array($bot->target_contacts_array) ? implode(', ', $bot->target_contacts_array) : '' }}">
                                                <i class="las la-phone-volume me-1"></i>{{ count($bot->target_contacts_array) }} @lang('Specific Contacts')
                                            </span>
                                        @elseif($bot->target_type === 'specific_groups')
                                            <span class="badge badge--warning px-2 py-1">
                                                <i class="las la-layer-group me-1"></i>{{ count($bot->target_group_ids_array) }} @lang('Specific Groups')
                                            </span>
                                        @elseif($bot->target_type === 'contact_list')
                                            <span class="badge badge--secondary px-2 py-1">
                                                <i class="las la-folder me-1"></i>{{ $bot->contactList ? $bot->contactList->name : 'Contact List' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        <div class="mb-1">
                                            @if($bot->match_type === 'exact')
                                                <span class="badge badge--danger px-2 py-1">@lang('Exact Match')</span>
                                            @elseif($bot->match_type === 'contains')
                                                <span class="badge badge--primary px-2 py-1">@lang('Contains Keyword')</span>
                                            @elseif($bot->match_type === 'starts_with')
                                                <span class="badge badge--info px-2 py-1">@lang('Starts With')</span>
                                            @elseif($bot->match_type === 'ends_with')
                                                <span class="badge badge--warning px-2 py-1">@lang('Ends With')</span>
                                            @elseif($bot->match_type === 'first_words_2')
                                                <span class="badge badge--info px-2 py-1">⚡ @lang('First 2 Words')</span>
                                            @elseif($bot->match_type === 'first_words_3')
                                                <span class="badge badge--info px-2 py-1">⚡ @lang('First 3 Words')</span>
                                            @elseif($bot->match_type === 'last_words_2')
                                                <span class="badge badge--warning px-2 py-1">🏁 @lang('Last 2 Words')</span>
                                            @elseif($bot->match_type === 'last_words_3')
                                                <span class="badge badge--warning px-2 py-1">🏁 @lang('Last 3 Words')</span>
                                            @elseif($bot->match_type === 'fallback')
                                                <span class="badge badge--dark px-2 py-1">@lang('Fallback / Default')</span>
                                            @endif
                                        </div>

                                        @if($bot->match_type !== 'fallback' && !empty($bot->keywords_array))
                                            <div class="d-flex gap-1 flex-wrap">
                                                @foreach($bot->keywords_array as $kw)
                                                    <span class="badge bg-light text-dark border font-monospace text-xs px-2 py-1">{{ $kw }}</span>
                                                @endforeach
                                            </div>
                                        @elseif($bot->match_type === 'fallback')
                                            <small class="text-muted fst-italic">@lang('Triggered when no other keyword matches')</small>
                                        @else
                                            <span class="text-muted small">@lang('No keywords set')</span>
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        <div class="d-flex flex-column gap-1 small">
                                            <div class="text-nowrap"><i class="las la-eye text--info me-1"></i><strong>Seen:</strong> {{ $bot->read_delay_seconds ? $bot->read_delay_seconds . 's' : 'Instant' }}</div>
                                            <div class="text-nowrap"><i class="las la-keyboard text--success me-1"></i><strong>Typing:</strong> {{ $bot->typing_duration_seconds ? $bot->typing_duration_seconds . 's' : 'None' }}</div>
                                            <div class="text-nowrap"><i class="las la-hourglass-half text--warning me-1"></i><strong>Send Delay:</strong> {{ $bot->reply_delay_seconds ? $bot->reply_delay_seconds . 's' : 'Instant' }}</div>
                                        </div>
                                    </td>
                                    <td class="py-2" style="max-width: 250px;">
                                        <div class="text-truncate text-dark font-monospace small bg-light p-1 rounded border" title="{{ $bot->reply_message }}">
                                            {{ $bot->reply_message }}
                                        </div>
                                    </td>
                                    <td class="py-2 text-center">
                                        <span class="badge badge--primary">{{ $bot->hit_count }}</span>
                                    </td>
                                    <td class="py-2 text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input status-toggle" type="checkbox" data-id="{{ $bot->id }}" {{ $bot->status ? 'checked' : '' }} style="cursor: pointer;">
                                        </div>
                                    </td>
                                    <td class="text-end pe-3 py-2 text-nowrap">
                                        <button type="button" class="btn btn-xs btn-outline--primary btnEditBot px-2 py-1 me-1"
                                            data-bot="{{ json_encode($bot) }}"
                                            data-keywords="{{ is_array($bot->keywords_array) ? implode(', ', $bot->keywords_array) : $bot->keywords }}"
                                            data-contacts="{{ is_array($bot->target_contacts_array) ? implode(', ', $bot->target_contacts_array) : $bot->target_contacts }}"
                                            title="@lang('Edit Bot')">
                                            <i class="las la-edit"></i>
                                        </button>

                                        <button type="button" class="btn btn-xs btn-outline--danger confirmationBtn px-2 py-1"
                                            data-action="{{ route('admin.autoreply.delete', $bot->id) }}"
                                            data-question="@lang('Are you sure you want to delete this auto-reply bot rule?')">
                                            <i class="las la-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center py-5" colspan="100%">
                                        <div class="empty-state">
                                            <i class="las la-robot text--muted mb-2" style="font-size: 48px;"></i>
                                            <h6 class="text-muted mb-2">@lang('No auto-reply bots created yet.')</h6>
                                            <p class="text-muted small mb-3">@lang('Create keyword bots to automatically reply to customer inquiries, greetings, or pricing questions 24/7.')</p>
                                            <button type="button" class="btn btn-sm btn--primary px-4" data-bs-toggle="modal" data-bs-target="#createBotModal">
                                                <i class="las la-plus me-1"></i>@lang('Create First Bot Rule')
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($botRules->hasPages())
                <div class="card-footer py-2 px-3">
                    {{ paginateLinks($botRules) }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Create Bot Rule -->
<div id="createBotModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary text-white py-3 px-4">
                <h5 class="modal-title text-white d-flex align-items-center mb-0">
                    <i class="las la-robot me-2 fs-4"></i> @lang('Create New Auto-Reply Bot Rule')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.autoreply.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        
                        <!-- Bot Name -->
                        <div class="col-md-7">
                            <label class="fw-bold mb-1">@lang('Bot Rule Name / Purpose') <span class="text--danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="@lang('e.g. VIP Pricing Bot, Support Auto-Responder')" required>
                        </div>

                        <!-- Target Account -->
                        <div class="col-md-5">
                            <label class="fw-bold mb-1">@lang('Target WhatsApp Account')</label>
                            <select name="session_id" class="form-control form-select">
                                <option value="">🌐 @lang('All Connected WhatsApp Accounts')</option>
                                @foreach($connectedAccounts as $acc)
                                    <option value="{{ $acc->session_id }}">
                                        {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Connected' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Target Audience Type -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Target Audience / Scope') <span class="text--danger">*</span></label>
                            <select name="target_type" id="create_target_type" class="form-control form-select" required>
                                <option value="all" selected>🌐 @lang('All Chats (Everyone)')</option>
                                <option value="all_individual">👤 @lang('All Direct 1-on-1 Chats (Saved & Unsaved)')</option>
                                <option value="saved_contacts">📇 @lang('Saved Contacts Only (In Phone Address Book)')</option>
                                <option value="unsaved_contacts">🆕 @lang('Non-Saved Numbers Only (Unknown / New Leads)')</option>
                                <option value="all_group">👥 @lang('All WhatsApp Groups Only')</option>
                                <option value="specific_contacts">🎯 @lang('Specific Individual Contacts')</option>
                                <option value="specific_groups">📌 @lang('Specific WhatsApp Groups')</option>
                                <option value="contact_list">📁 @lang('Target Specific Contact List')</option>
                            </select>
                        </div>

                        <!-- Match Type -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Keyword Match Type') <span class="text--danger">*</span></label>
                            <select name="match_type" id="create_match_type" class="form-control form-select" required>
                                <option value="contains" selected>🔍 @lang('Contains Keyword (Anywhere in Message)')</option>
                                <option value="exact">🎯 @lang('Exact Match (Entire Message)')</option>
                                <option value="starts_with">⏩ @lang('Starts With (Begins with Phrase)')</option>
                                <option value="ends_with">⏪ @lang('Ends With (Ends with Phrase)')</option>
                                <option value="first_words_2">⚡ @lang('First 2 Words of Message')</option>
                                <option value="first_words_3">⚡ @lang('First 3 Words of Message')</option>
                                <option value="last_words_2">🏁 @lang('Last 2 Words of Message')</option>
                                <option value="last_words_3">🏁 @lang('Last 3 Words of Message')</option>
                                <option value="fallback">🛡️ @lang('Default / Fallback Reply (If no other matches)')</option>
                            </select>
                        </div>

                        <!-- Dynamic Specific Contacts Box -->
                        <div class="col-12 d-none" id="create_specific_contacts_wrapper">
                            <label class="fw-bold mb-1">@lang('Target Specific Contacts (Phone Numbers)') <span class="text--danger">*</span></label>
                            <input type="text" name="target_contacts" class="form-control" placeholder="@lang('e.g. 923001234567, 923219876543')">
                            <small class="text-muted d-block mt-1"><i class="las la-info-circle me-1"></i>@lang('Enter specific WhatsApp phone numbers separated by commas.')</small>
                        </div>

                        <!-- Dynamic Specific Groups Box -->
                        <div class="col-12 d-none" id="create_specific_groups_wrapper">
                            <label class="fw-bold mb-1">@lang('Select Specific WhatsApp Groups') <span class="text--danger">*</span></label>
                            <div class="p-3 border rounded bg-light" style="max-height: 180px; overflow-y: auto;">
                                @forelse($groups as $g)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="target_group_ids[]" value="{{ $g->group_id }}" id="cg_{{ $loop->index }}">
                                        <label class="form-check-label fw-bold text-dark cursor-pointer" for="cg_{{ $loop->index }}">
                                            {{ $g->group_name }} <span class="text-muted font-monospace small">({{ $g->group_id }})</span>
                                        </label>
                                    </div>
                                @empty
                                    <div class="text-muted small">@lang('No WhatsApp groups found in contacts.')</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Dynamic Contact List Selector -->
                        <div class="col-12 d-none" id="create_contact_list_wrapper">
                            <label class="fw-bold mb-1">@lang('Choose Target Contact List') <span class="text--danger">*</span></label>
                            <select name="contact_list_id" class="form-control form-select">
                                <option value="">@lang('Select Contact List')</option>
                                @foreach($contactLists as $lst)
                                    <option value="{{ $lst->id }}">📁 {{ $lst->name }} ({{ $lst->contacts_count }} {{ $lst->type }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Keywords Input -->
                        <div class="col-12" id="create_keywords_wrapper">
                            <label class="fw-bold mb-1">@lang('Trigger Keywords') <span class="text--danger">*</span></label>
                            <input type="text" name="keywords" class="form-control" placeholder="@lang('e.g. price, cost, rates, package, how much')">
                            <small class="text-muted d-block mt-1">
                                <i class="las la-info-circle me-1"></i>@lang('Separate multiple keywords with commas. Matching is case-insensitive.')
                            </small>
                        </div>

                        <!-- Human-Like Response Flow Sequence -->
                        <div class="col-12">
                            <div class="card border border-primary border-opacity-25 bg-light p-3 rounded">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="las la-magic text--primary fs-4 me-2"></i>
                                    <h6 class="mb-0 fw-bold text-dark">@lang('Human-Like Response Flow Sequence')</h6>
                                </div>
                                <p class="text-muted small mb-3">
                                    @lang('Simulate natural human behavior before sending automated replies. Set delays for read receipts, typing animations, and message dispatch.')
                                </p>

                                <div class="row g-3">
                                    <!-- Mark as Seen Delay -->
                                    <div class="col-md-4">
                                        <label class="fw-bold mb-1 small text-dark d-flex align-items-center">
                                            <i class="las la-eye text--info me-1"></i> @lang('Mark as Seen Delay')
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="read_delay_seconds" class="form-control" value="0" min="0" max="3600" placeholder="0">
                                            <span class="input-group-text">@lang('sec')</span>
                                        </div>
                                        <small class="text-muted text-xs d-block mt-1">@lang('Wait X sec before blue ticks (0 = instant)')</small>
                                    </div>

                                    <!-- Typing Presence Duration -->
                                    <div class="col-md-4">
                                        <label class="fw-bold mb-1 small text-dark d-flex align-items-center">
                                            <i class="las la-keyboard text--success me-1"></i> @lang('Typing Animation')
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="typing_duration_seconds" class="form-control" value="0" min="0" max="300" placeholder="0">
                                            <span class="input-group-text">@lang('sec')</span>
                                        </div>
                                        <small class="text-muted text-xs d-block mt-1">@lang('Shows "typing..." presence to contact')</small>
                                    </div>

                                    <!-- Reply Dispatch Delay -->
                                    <div class="col-md-4">
                                        <label class="fw-bold mb-1 small text-dark d-flex align-items-center">
                                            <i class="las la-hourglass-half text--warning me-1"></i> @lang('Send Message Delay')
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="reply_delay_seconds" class="form-control" value="0" min="0" max="3600" placeholder="0">
                                            <span class="input-group-text">@lang('sec')</span>
                                        </div>
                                        <small class="text-muted text-xs d-block mt-1">@lang('Pause before sending reply message')</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reply Message -->
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="fw-bold mb-0">@lang('Automated Reply Message') <span class="text--danger">*</span></label>
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="text-muted small me-1">@lang('Insert tag'):</span>
                                    <button type="button" class="btn btn-xs btn-outline--secondary btn-insert-tag" data-target="#create_reply_msg" data-tag="{name}">{name}</button>
                                    <button type="button" class="btn btn-xs btn-outline--secondary btn-insert-tag" data-target="#create_reply_msg" data-tag="{sender_phone}">{sender_phone}</button>
                                    <button type="button" class="btn btn-xs btn-outline--secondary btn-insert-tag" data-target="#create_reply_msg" data-tag="{time}">{time}</button>
                                    <button type="button" class="btn btn-xs btn-outline--secondary btn-insert-tag" data-target="#create_reply_msg" data-tag="{date}">{date}</button>
                                </div>
                            </div>
                            <textarea name="reply_message" id="create_reply_msg" rows="4" class="form-control" placeholder="@lang('Hi {name}! Thanks for reaching out. Here is our pricing list...')" required></textarea>
                        </div>

                    </div>
                </div>
                <div class="modal-footer p-3">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--primary btn-sm px-4 fw-bold">
                        <i class="las la-save me-1"></i> @lang('Create Bot Rule')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Bot Rule -->
<div id="editBotModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary text-white py-3 px-4">
                <h5 class="modal-title text-white d-flex align-items-center mb-0">
                    <i class="las la-edit me-2 fs-4"></i> @lang('Edit Auto-Reply Bot Rule')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form id="editBotForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        
                        <!-- Bot Name -->
                        <div class="col-md-7">
                            <label class="fw-bold mb-1">@lang('Bot Rule Name') <span class="text--danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>

                        <!-- Target Account -->
                        <div class="col-md-5">
                            <label class="fw-bold mb-1">@lang('Target WhatsApp Account')</label>
                            <select name="session_id" id="edit_session_id" class="form-control form-select">
                                <option value="">🌐 @lang('All Connected WhatsApp Accounts')</option>
                                @foreach($connectedAccounts as $acc)
                                    <option value="{{ $acc->session_id }}">
                                        {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Connected' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Target Audience -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Target Audience / Scope') <span class="text--danger">*</span></label>
                            <select name="target_type" id="edit_target_type" class="form-control form-select" required>
                                <option value="all">🌐 @lang('All Chats (Everyone)')</option>
                                <option value="all_individual">👤 @lang('All Direct 1-on-1 Chats (Saved & Unsaved)')</option>
                                <option value="saved_contacts">📇 @lang('Saved Contacts Only (In Phone Address Book)')</option>
                                <option value="unsaved_contacts">🆕 @lang('Non-Saved Numbers Only (Unknown / New Leads)')</option>
                                <option value="all_group">👥 @lang('All WhatsApp Groups Only')</option>
                                <option value="specific_contacts">🎯 @lang('Specific Individual Contacts')</option>
                                <option value="specific_groups">📌 @lang('Specific WhatsApp Groups')</option>
                                <option value="contact_list">📁 @lang('Target Specific Contact List')</option>
                            </select>
                        </div>

                        <!-- Match Type -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Keyword Match Type') <span class="text--danger">*</span></label>
                            <select name="match_type" id="edit_match_type" class="form-control form-select" required>
                                <option value="contains">🔍 @lang('Contains Keyword (Anywhere in Message)')</option>
                                <option value="exact">🎯 @lang('Exact Match (Entire Message)')</option>
                                <option value="starts_with">⏩ @lang('Starts With (Begins with Phrase)')</option>
                                <option value="ends_with">⏪ @lang('Ends With (Ends with Phrase)')</option>
                                <option value="first_words_2">⚡ @lang('First 2 Words of Message')</option>
                                <option value="first_words_3">⚡ @lang('First 3 Words of Message')</option>
                                <option value="last_words_2">🏁 @lang('Last 2 Words of Message')</option>
                                <option value="last_words_3">🏁 @lang('Last 3 Words of Message')</option>
                                <option value="fallback">🛡️ @lang('Default / Fallback Reply (If no other matches)')</option>
                            </select>
                        </div>

                        <!-- Dynamic Specific Contacts Box -->
                        <div class="col-12 d-none" id="edit_specific_contacts_wrapper">
                            <label class="fw-bold mb-1">@lang('Target Specific Contacts (Phone Numbers)') <span class="text--danger">*</span></label>
                            <input type="text" name="target_contacts" id="edit_target_contacts" class="form-control" placeholder="@lang('e.g. 923001234567, 923219876543')">
                        </div>

                        <!-- Dynamic Specific Groups Box -->
                        <div class="col-12 d-none" id="edit_specific_groups_wrapper">
                            <label class="fw-bold mb-1">@lang('Select Specific WhatsApp Groups') <span class="text--danger">*</span></label>
                            <div class="p-3 border rounded bg-light" style="max-height: 180px; overflow-y: auto;">
                                @forelse($groups as $g)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input edit-grp-chk" type="checkbox" name="target_group_ids[]" value="{{ $g->group_id }}" id="eg_{{ $loop->index }}">
                                        <label class="form-check-label fw-bold text-dark cursor-pointer" for="eg_{{ $loop->index }}">
                                            {{ $g->group_name }} <span class="text-muted font-monospace small">({{ $g->group_id }})</span>
                                        </label>
                                    </div>
                                @empty
                                    <div class="text-muted small">@lang('No WhatsApp groups found in contacts.')</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Dynamic Contact List Selector -->
                        <div class="col-12 d-none" id="edit_contact_list_wrapper">
                            <label class="fw-bold mb-1">@lang('Choose Target Contact List') <span class="text--danger">*</span></label>
                            <select name="contact_list_id" id="edit_contact_list_id" class="form-control form-select">
                                <option value="">@lang('Select Contact List')</option>
                                @foreach($contactLists as $lst)
                                    <option value="{{ $lst->id }}">📁 {{ $lst->name }} ({{ $lst->contacts_count }} {{ $lst->type }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Keywords Input -->
                        <div class="col-12" id="edit_keywords_wrapper">
                            <label class="fw-bold mb-1">@lang('Trigger Keywords') <span class="text--danger">*</span></label>
                            <input type="text" name="keywords" id="edit_keywords" class="form-control">
                            <small class="text-muted d-block mt-1">@lang('Separate multiple keywords with commas.')</small>
                        </div>

                        <!-- Human-Like Response Flow Sequence (Edit) -->
                        <div class="col-12">
                            <div class="card border border-primary border-opacity-25 bg-light p-3 rounded">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="las la-magic text--primary fs-4 me-2"></i>
                                    <h6 class="mb-0 fw-bold text-dark">@lang('Human-Like Response Flow Sequence')</h6>
                                </div>
                                <p class="text-muted small mb-3">
                                    @lang('Simulate natural human behavior before sending automated replies. Set delays for read receipts, typing animations, and message dispatch.')
                                </p>

                                <div class="row g-3">
                                    <!-- Mark as Seen Delay -->
                                    <div class="col-md-4">
                                        <label class="fw-bold mb-1 small text-dark d-flex align-items-center">
                                            <i class="las la-eye text--info me-1"></i> @lang('Mark as Seen Delay')
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="read_delay_seconds" id="edit_read_delay_seconds" class="form-control" min="0" max="3600" placeholder="0">
                                            <span class="input-group-text">@lang('sec')</span>
                                        </div>
                                        <small class="text-muted text-xs d-block mt-1">@lang('Wait X sec before blue ticks')</small>
                                    </div>

                                    <!-- Typing Presence Duration -->
                                    <div class="col-md-4">
                                        <label class="fw-bold mb-1 small text-dark d-flex align-items-center">
                                            <i class="las la-keyboard text--success me-1"></i> @lang('Typing Animation')
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="typing_duration_seconds" id="edit_typing_duration_seconds" class="form-control" min="0" max="300" placeholder="0">
                                            <span class="input-group-text">@lang('sec')</span>
                                        </div>
                                        <small class="text-muted text-xs d-block mt-1">@lang('Shows "typing..." presence')</small>
                                    </div>

                                    <!-- Reply Dispatch Delay -->
                                    <div class="col-md-4">
                                        <label class="fw-bold mb-1 small text-dark d-flex align-items-center">
                                            <i class="las la-hourglass-half text--warning me-1"></i> @lang('Send Message Delay')
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="reply_delay_seconds" id="edit_reply_delay_seconds" class="form-control" min="0" max="3600" placeholder="0">
                                            <span class="input-group-text">@lang('sec')</span>
                                        </div>
                                        <small class="text-muted text-xs d-block mt-1">@lang('Pause before sending reply')</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reply Message -->
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="fw-bold mb-0">@lang('Automated Reply Message') <span class="text--danger">*</span></label>
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="text-muted small me-1">@lang('Insert tag'):</span>
                                    <button type="button" class="btn btn-xs btn-outline--secondary btn-insert-tag" data-target="#edit_reply_msg" data-tag="{name}">{name}</button>
                                    <button type="button" class="btn btn-xs btn-outline--secondary btn-insert-tag" data-target="#edit_reply_msg" data-tag="{sender_phone}">{sender_phone}</button>
                                    <button type="button" class="btn btn-xs btn-outline--secondary btn-insert-tag" data-target="#edit_reply_msg" data-tag="{time}">{time}</button>
                                    <button type="button" class="btn btn-xs btn-outline--secondary btn-insert-tag" data-target="#edit_reply_msg" data-tag="{date}">{date}</button>
                                </div>
                            </div>
                            <textarea name="reply_message" id="edit_reply_msg" rows="4" class="form-control" required></textarea>
                        </div>

                        <!-- Status Checkbox -->
                        <div class="col-12 d-flex align-items-center pt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" id="edit_status" value="1">
                                <label class="form-check-label fw-bold ms-2" for="edit_status">@lang('Bot Rule Active')</label>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer p-3">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--primary btn-sm px-4 fw-bold">
                        <i class="las la-save me-1"></i> @lang('Update Bot Rule')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Interactive Bot Simulator -->
<div id="simulatorModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg--info text-white py-3 px-4">
                <h5 class="modal-title text-white d-flex align-items-center mb-0">
                    <i class="las la-vial me-2 fs-4"></i> @lang('Interactive Auto-Reply Bot Simulator')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    @lang('Test your keyword bots against specific word position rules, contacts, groups, saved contacts, or strangers in real time.')
                </p>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="fw-bold mb-1 small">@lang('Chat Type')</label>
                        <select id="sim_chat_type" class="form-control form-control-sm form-select">
                            <option value="individual" selected>👤 @lang('Direct 1-on-1 Chat')</option>
                            <option value="group">👥 @lang('WhatsApp Group')</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold mb-1 small">@lang('Contact Book Status')</label>
                        <select id="sim_is_saved" class="form-control form-control-sm form-select">
                            <option value="true" selected>📇 @lang('Saved in Contacts')</option>
                            <option value="false">🆕 @lang('Non-Saved (Unknown)')</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold mb-1 small">@lang('Sender Phone Number')</label>
                        <input type="text" id="sim_sender_phone" class="form-control form-control-sm" placeholder="923001234567" value="923001234567">
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold mb-1 small">@lang('Target Account')</label>
                        <select id="sim_session_id" class="form-control form-control-sm form-select">
                            <option value="">🌐 @lang('Any Connected Account')</option>
                            @foreach($connectedAccounts as $acc)
                                <option value="{{ $acc->session_id }}">{{ $acc->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold mb-1">@lang('Test Incoming Message') <span class="text--danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="sim_input_text" class="form-control" placeholder="@lang('e.g. Hello, what is the price for your packages?')" value="Hello, what is your pricing?">
                        <button class="btn btn--primary px-4 fw-bold" type="button" id="btnRunSimulation">
                            <i class="las la-play me-1"></i> @lang('Test Match')
                        </button>
                    </div>
                </div>

                <!-- Live Result Box -->
                <div id="simResultBox" class="p-3 bg-light rounded border d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="fw-bold text-dark"><i class="las la-check-circle text--success me-1 fs-5"></i> @lang('Match Result'):</span>
                        <span id="simRuleBadge" class="badge badge--success"></span>
                    </div>
                    <div class="mb-2">
                        <strong class="text-muted small d-block">@lang('Triggered Bot Rule'):</strong>
                        <span id="simRuleName" class="fw-bold text-dark fs-6"></span>
                    </div>

                    <!-- Flow sequence preview -->
                    <div class="p-2 mb-2 bg-white rounded border">
                        <strong class="text-muted small d-block mb-1">@lang('Executed Response Flow Sequence'):</strong>
                        <div class="d-flex flex-wrap gap-2 align-items-center small">
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1" id="simFlowSeen"></span>
                            <i class="las la-arrow-right text-muted"></i>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1" id="simFlowTyping"></span>
                            <i class="las la-arrow-right text-muted"></i>
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1" id="simFlowDelay"></span>
                            <i class="las la-arrow-right text-muted"></i>
                            <span class="badge bg-primary text-white px-2 py-1">🚀 @lang('Dispatch Reply')</span>
                        </div>
                    </div>

                    <div>
                        <strong class="text-muted small d-block mb-1">@lang('Processed WhatsApp Reply Message'):</strong>
                        <div id="simReplyOutput" class="p-3 bg-white rounded border font-monospace text-dark small" style="white-space: pre-wrap;"></div>
                    </div>
                </div>

                <div id="simNoMatchBox" class="alert alert-warning border-0 d-none">
                    <i class="las la-exclamation-triangle me-1"></i> <span id="simNoMatchMsg">@lang('No active bot rule matched this message or sender.')</span>
                </div>
            </div>
            <div class="modal-footer p-3">
                <button type="button" class="btn btn--dark btn-sm px-4" data-bs-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn--info text-white me-2" data-bs-toggle="modal" data-bs-target="#simulatorModal">
        <i class="las la-vial me-1"></i> @lang('Test Simulator')
    </button>
    <button type="button" class="btn btn-sm btn--primary" data-bs-toggle="modal" data-bs-target="#createBotModal">
        <i class="las la-plus me-1"></i> @lang('Add Bot Rule')
    </button>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    // Target Type Dynamic Boxes (Create)
    $('#create_target_type').on('change', function(){
        const val = $(this).val();
        $('#create_specific_contacts_wrapper').addClass('d-none');
        $('#create_specific_groups_wrapper').addClass('d-none');
        $('#create_contact_list_wrapper').addClass('d-none');

        if(val === 'specific_contacts'){
            $('#create_specific_contacts_wrapper').removeClass('d-none');
        } else if(val === 'specific_groups'){
            $('#create_specific_groups_wrapper').removeClass('d-none');
        } else if(val === 'contact_list'){
            $('#create_contact_list_wrapper').removeClass('d-none');
        }
    });

    // Target Type Dynamic Boxes (Edit)
    $('#edit_target_type').on('change', function(){
        const val = $(this).val();
        $('#edit_specific_contacts_wrapper').addClass('d-none');
        $('#edit_specific_groups_wrapper').addClass('d-none');
        $('#edit_contact_list_wrapper').addClass('d-none');

        if(val === 'specific_contacts'){
            $('#edit_specific_contacts_wrapper').removeClass('d-none');
        } else if(val === 'specific_groups'){
            $('#edit_specific_groups_wrapper').removeClass('d-none');
        } else if(val === 'contact_list'){
            $('#edit_contact_list_wrapper').removeClass('d-none');
        }
    });

    // Toggle match type keywords visibility
    $('#create_match_type').on('change', function(){
        if($(this).val() === 'fallback'){
            $('#create_keywords_wrapper').addClass('d-none');
        } else {
            $('#create_keywords_wrapper').removeClass('d-none');
        }
    });

    $('#edit_match_type').on('change', function(){
        if($(this).val() === 'fallback'){
            $('#edit_keywords_wrapper').addClass('d-none');
        } else {
            $('#edit_keywords_wrapper').removeClass('d-none');
        }
    });

    // Tag insertion helper
    $('.btn-insert-tag').on('click', function(){
        const target = $($(this).data('target'));
        const tag = $(this).data('tag');
        const pos = target.prop('selectionStart') || 0;
        const val = target.val();
        target.val(val.substring(0, pos) + tag + val.substring(pos));
        target.focus();
    });

    // Status toggle AJAX
    $('.status-toggle').on('change', function(){
        const botId = $(this).data('id');
        $.ajax({
            url: "{{ url('admin/autoreply/status') }}/" + botId,
            type: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function(res){
                if(res.success){
                    notify('success', res.message);
                }
            }
        });
    });

    // Edit modal populator
    $('.btnEditBot').on('click', function(){
        const bot = $(this).data('bot');
        const keywords = $(this).data('keywords');
        const contacts = $(this).data('contacts');

        $('#editBotForm').attr('action', "{{ url('admin/autoreply/update') }}/" + bot.id);
        $('#edit_name').val(bot.name);
        $('#edit_session_id').val(bot.session_id || '');
        
        const targetType = bot.target_type || (bot.chat_scope === 'group' ? 'all_group' : (bot.chat_scope === 'individual' ? 'all_individual' : 'all'));
        $('#edit_target_type').val(targetType).trigger('change');
        
        $('#edit_target_contacts').val(contacts || '');
        $('#edit_contact_list_id').val(bot.contact_list_id || '');

        // Uncheck all group checkboxes and check selected
        $('.edit-grp-chk').prop('checked', false);
        if(bot.target_group_ids){
            try {
                const grpArr = Array.isArray(bot.target_group_ids) ? bot.target_group_ids : JSON.parse(bot.target_group_ids);
                grpArr.forEach(gid => {
                    $(`.edit-grp-chk[value="${gid}"]`).prop('checked', true);
                });
            } catch(e){}
        }

        $('#edit_match_type').val(bot.match_type).trigger('change');
        $('#edit_keywords').val(keywords || '');
        $('#edit_read_delay_seconds').val(bot.read_delay_seconds || 0);
        $('#edit_typing_duration_seconds').val(bot.typing_duration_seconds || 0);
        $('#edit_reply_delay_seconds').val(bot.reply_delay_seconds || 0);
        $('#edit_reply_msg').val(bot.reply_message);
        $('#edit_status').prop('checked', bot.status == 1);

        $('#editBotModal').modal('show');
    });

    // Run Simulator
    $('#btnRunSimulation').on('click', function(){
        const text = $('#sim_input_text').val().trim();
        const chatType = $('#sim_chat_type').val();
        const isSaved = $('#sim_is_saved').val();
        const sessionId = $('#sim_session_id').val();
        const senderPhone = $('#sim_sender_phone').val().trim();

        if(!text){
            notify('warning', 'Please enter some text to test');
            return;
        }

        $('#simResultBox').addClass('d-none');
        $('#simNoMatchBox').addClass('d-none');

        $.ajax({
            url: "{{ route('admin.autoreply.simulate') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                text: text,
                chat_type: chatType,
                is_saved: isSaved,
                session_id: sessionId,
                sender_phone: senderPhone
            },
            success: function(res){
                if(res.matched){
                    $('#simRuleName').text(res.rule_name);
                    $('#simRuleBadge').text(res.is_fallback ? 'Fallback Trigger' : (res.match_type.toUpperCase().replace(/_/g, ' ') + ' MATCH'));
                    
                    $('#simFlowSeen').html(`<i class="las la-eye me-1"></i> Seen: <strong>${res.read_delay_seconds || 0}s</strong>`);
                    $('#simFlowTyping').html(`<i class="las la-keyboard me-1"></i> Typing: <strong>${res.typing_duration_seconds || 0}s</strong>`);
                    $('#simFlowDelay').html(`<i class="las la-hourglass-half me-1"></i> Delay: <strong>${res.reply_delay_seconds || 0}s</strong>`);
                    
                    $('#simReplyOutput').text(res.reply_output);
                    $('#simResultBox').removeClass('d-none');
                } else {
                    $('#simNoMatchMsg').text(res.message);
                    $('#simNoMatchBox').removeClass('d-none');
                }
            },
            error: function(){
                notify('error', 'Simulation request failed');
            }
        });
    });

})(jQuery);
</script>
@endpush
