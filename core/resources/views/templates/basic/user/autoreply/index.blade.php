@extends($activeTemplate . 'layouts.master')
@section('content')
@push('style')
<style>
    .custom--autoreply-table {
        table-layout: auto !important;
        border-collapse: collapse !important;
        width: 100% !important;
        min-width: 950px !important;
    }
    .custom--autoreply-table thead tr th {
        max-width: none !important;
        width: auto !important;
        white-space: nowrap !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        padding: 12px 14px !important;
        vertical-align: middle !important;
        background-color: #f8fafc !important;
        color: #334155 !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        border-radius: 0 !important;
        text-align: left !important;
    }
    .custom--autoreply-table thead tr th.text-center {
        text-align: center !important;
    }
    .custom--autoreply-table thead tr th.text-end {
        text-align: right !important;
    }
    .custom--autoreply-table tbody tr td {
        max-width: none !important;
        padding: 12px 14px !important;
        vertical-align: middle !important;
        font-size: 13.5px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        background-color: transparent !important;
    }
    .custom--keyword-badge {
        background-color: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.2px;
        display: inline-block;
        white-space: nowrap;
    }
    .custom--preview-box {
        background-color: #f8fafc;
        border-color: #e2e8f0 !important;
        color: #1e293b;
        max-width: 260px;
        line-height: 1.4;
    }
    .text-dark-mode-high {
        color: #0f172a;
    }

    /* Dark Theme Support */
    [data-theme="dark"] .custom--autoreply-table thead tr th,
    .dark-theme .custom--autoreply-table thead tr th,
    body.dark-mode .custom--autoreply-table thead tr th {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
        border-bottom: 2px solid #334155 !important;
    }
    [data-theme="dark"] .custom--autoreply-table tbody tr td,
    .dark-theme .custom--autoreply-table tbody tr td,
    body.dark-mode .custom--autoreply-table tbody tr td {
        border-bottom: 1px solid #1e293b !important;
        color: #f8fafc !important;
    }
    [data-theme="dark"] .custom--keyword-badge,
    .dark-theme .custom--keyword-badge,
    body.dark-mode .custom--keyword-badge {
        background-color: #1e293b;
        color: #38bdf8;
        border-color: #334155;
    }
    [data-theme="dark"] .custom--preview-box,
    .dark-theme .custom--preview-box,
    body.dark-mode .custom--preview-box {
        background-color: #0f172a;
        border-color: #334155 !important;
        color: #f1f5f9;
    }
    [data-theme="dark"] .text-dark-mode-high,
    .dark-theme .text-dark-mode-high,
    body.dark-mode .text-dark-mode-high {
        color: #f8fafc !important;
    }
</style>
@endpush
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-dark-mode-high">Auto-Reply & Keyword Bots</h4>
                <p class="text-muted mb-0">Create smart bots that automatically respond to your customers on WhatsApp 24/7.</p>
            </div>
            <div>
                <button type="button" class="btn btn--base fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#createBotModal">
                    <i class="las la-plus-circle me-1"></i> Add Keyword Bot
                </button>
            </div>
        </div>

        <!-- 3 Metric Cards -->
        <div class="row gy-3 mb-4">
            <div class="col-md-4">
                <div class="card custom--card p-3 border shadow-sm rounded-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Bot Rules</span>
                            <h3 class="fw-bold text-dark-mode-high mb-0">{{ $totalBots }} <span class="text-muted fs-6 fw-normal">/ {{ $plan->autoreply_limit ?? 10 }}</span></h3>
                        </div>
                        <div class="card-icon bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="las la-robot fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom--card p-3 border shadow-sm rounded-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Active Rules</span>
                            <h3 class="fw-bold text-success mb-0">{{ $activeBots }}</h3>
                        </div>
                        <div class="card-icon bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="las la-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom--card p-3 border shadow-sm rounded-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Responses Dispatched</span>
                            <h3 class="fw-bold text-primary mb-0">{{ $totalHits }}</h3>
                        </div>
                        <div class="card-icon bg-info bg-opacity-10 text-info rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="las la-paper-plane fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main List Card -->
        <div class="card custom--card border shadow-sm rounded-3 overflow-hidden">
            <!-- Filter Header -->
            <div class="card-header bg-white py-3 border-bottom">
                <form action="" method="GET">
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <!-- Account Filter -->
                            <select name="session_id" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width: 230px;">
                                <option value="">🌐 All My Accounts</option>
                                @foreach($connectedAccounts as $acc)
                                    <option value="{{ $acc->session_id }}" {{ request('session_id') == $acc->session_id ? 'selected' : '' }}>
                                        {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Active' }})
                                    </option>
                                @endforeach
                            </select>

                            <!-- Status Filter -->
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width: 140px;">
                                <option value="">All Status</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="input-group input-group-sm" style="max-width: 280px;">
                            <input type="text" name="search" class="form-control" placeholder="Search keyword or rule..." value="{{ request('search') }}">
                            <button class="btn btn--base" type="submit"><i class="las la-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom--autoreply-table">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3">Bot Name & Account</th>
                                <th class="py-3">Target Audience</th>
                                <th class="py-3">Keywords & Match</th>
                                <th class="py-3">Human Delays</th>
                                <th class="py-3">Reply Message</th>
                                <th class="py-3 text-center">Hits</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="text-end pe-3 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($botRules as $rule)
                                <tr>
                                    <td class="ps-3 py-3">
                                        <div class="fw-bold text-dark-mode-high mb-1">{{ $rule->name }}</div>
                                        <div class="text-muted small text-nowrap">
                                            @if($rule->account)
                                                <i class="lab la-whatsapp text-success me-1"></i>{{ $rule->account->account_name }} ({{ $rule->account->phone_number ? '+' . $rule->account->phone_number : 'Active' }})
                                            @else
                                                <i class="las la-globe text-primary me-1"></i>All My Accounts
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        @if($rule->target_type == 'all_individual')
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 text-nowrap">
                                                <i class="las la-user me-1"></i> Direct Chats Only
                                            </span>
                                        @elseif($rule->target_type == 'all_group')
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 text-nowrap">
                                                <i class="las la-users me-1"></i> Groups Only
                                            </span>
                                        @elseif($rule->target_type == 'saved_contacts')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 text-nowrap">
                                                <i class="las la-user-check me-1"></i> Saved Contacts
                                            </span>
                                        @elseif($rule->target_type == 'unsaved_contacts')
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 text-nowrap">
                                                <i class="las la-user-plus me-1"></i> Unsaved Numbers
                                            </span>
                                        @elseif($rule->target_type == 'specific_contacts')
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 text-nowrap">
                                                <i class="las la-phone me-1"></i> {{ count($rule->target_contacts_array) }} Specific Numbers
                                            </span>
                                        @elseif($rule->target_type == 'specific_groups')
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 text-nowrap">
                                                <i class="las la-comments me-1"></i> {{ count($rule->target_group_ids_array) }} Specific Groups
                                            </span>
                                        @elseif($rule->target_type == 'contact_list')
                                            <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-2 py-1 text-nowrap">
                                                <i class="las la-list me-1"></i> List: {{ $rule->contactList ? $rule->contactList->name : 'Audience' }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark border px-2 py-1 text-nowrap">
                                                <i class="las la-globe me-1"></i> All Chats
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="mb-2">
                                            @if($rule->match_type === 'exact')
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 text-nowrap">Exact Match</span>
                                            @elseif($rule->match_type === 'contains')
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 text-nowrap">Contains Keyword</span>
                                            @elseif($rule->match_type === 'starts_with')
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 text-nowrap">Starts With</span>
                                            @elseif($rule->match_type === 'ends_with')
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 text-nowrap">Ends With</span>
                                            @elseif($rule->match_type === 'fallback')
                                                <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-2 py-1 text-nowrap">Fallback (Default)</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 text-nowrap text-uppercase">{{ $rule->match_type }}</span>
                                            @endif
                                        </div>

                                        @if($rule->match_type !== 'fallback')
                                            <div class="d-flex gap-1 flex-wrap">
                                                @php
                                                    $kwList = [];
                                                    if (!empty($rule->keywords)) {
                                                        $decoded = json_decode($rule->keywords, true);
                                                        if (is_array($decoded)) {
                                                            $kwList = $decoded;
                                                        } else {
                                                            $kwList = array_values(array_filter(array_map('trim', explode(',', $rule->keywords))));
                                                        }
                                                    }
                                                @endphp
                                                @forelse($kwList as $kw)
                                                    <span class="badge custom--keyword-badge font-monospace px-2 py-1">{{ $kw }}</span>
                                                @empty
                                                    <span class="text-muted small">No keywords set</span>
                                                @endforelse
                                            </div>
                                        @else
                                            <small class="text-muted fst-italic">Triggered when no other keyword matches</small>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex flex-column gap-1 small">
                                            <div class="text-nowrap"><i class="las la-eye text-primary me-1"></i><strong>Seen:</strong> {{ $rule->read_delay_seconds ? $rule->read_delay_seconds . 's' : 'Instant' }}</div>
                                            <div class="text-nowrap"><i class="las la-keyboard text-success me-1"></i><strong>Typing:</strong> {{ $rule->typing_duration_seconds ? $rule->typing_duration_seconds . 's' : 'None' }}</div>
                                            <div class="text-nowrap"><i class="las la-hourglass-half text-warning me-1"></i><strong>Send Delay:</strong> {{ $rule->reply_delay_seconds ? $rule->reply_delay_seconds . 's' : ($rule->delay_seconds ? $rule->delay_seconds . 's' : 'Instant') }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3" style="max-width: 250px;">
                                        <div class="custom--preview-box text-truncate p-2 rounded border small font-monospace" title="{{ $rule->reply_message }}">
                                            @if($rule->reply_type && $rule->reply_type !== 'text')
                                                <span class="badge bg-secondary me-1 text-uppercase">{{ $rule->reply_type }}</span>
                                            @endif
                                            {{ $rule->reply_message }}
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">{{ $rule->hit_count }} hits</span>
                                    </td>
                                    <td class="py-3 text-center text-nowrap">
                                        <form action="{{ route('user.autoreply.status', $rule->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-{{ $rule->status == 1 ? 'success' : 'secondary' }} py-1 px-2" title="Toggle Status">
                                                <i class="las la-{{ $rule->status == 1 ? 'check-circle' : 'ban' }} me-1"></i>
                                                {{ $rule->status == 1 ? 'Active' : 'Disabled' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end pe-3 py-3 text-nowrap">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-outline-primary btn-sm btnEditBot py-1 px-2" 
                                                    data-id="{{ $rule->id }}"
                                                    data-name="{{ $rule->name }}"
                                                    data-match="{{ $rule->match_type }}"
                                                    data-keywords="{{ is_array($rule->keywords_array) ? implode(', ', $rule->keywords_array) : $rule->keywords }}"
                                                    data-type="{{ $rule->reply_type }}"
                                                    data-message="{{ $rule->reply_message }}"
                                                    data-media="{{ $rule->media_url }}"
                                                    data-session="{{ $rule->session_id }}"
                                                    data-target-type="{{ $rule->target_type ?: 'all' }}"
                                                    data-target-contacts="{{ is_array($rule->target_contacts_array) ? implode(',', $rule->target_contacts_array) : '' }}"
                                                    data-target-groups='{{ json_encode($rule->target_group_ids_array ?? []) }}'
                                                    data-contact-list="{{ $rule->contact_list_id }}"
                                                    data-seen="{{ $rule->read_delay_seconds ?? 2 }}"
                                                    data-typing="{{ $rule->typing_duration_seconds ?? 3 }}"
                                                    data-delay="{{ $rule->reply_delay_seconds ?? ($rule->delay_seconds ?? 2) }}"
                                                    title="Edit Bot">
                                                <i class="las la-edit"></i>
                                            </button>

                                            <form action="{{ route('user.autoreply.delete', $rule->id) }}" method="POST" onsubmit="return confirm('Delete this auto-reply bot?')" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Delete">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="las la-robot text-muted fs-1 d-block mb-2"></i>
                                        <h6 class="text-muted">No keyword bots created yet</h6>
                                        <p class="text-muted small">Set up your first automated keyword response rule above.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($botRules->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ paginateLinks($botRules) }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Modal: Create Bot -->
<div class="modal fade" id="createBotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-plus-circle me-1"></i> New Keyword Auto-Reply Bot</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.autoreply.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row gy-3">
                        <div class="col-md-7">
                            <label class="fw-bold mb-1">Bot Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Welcome Greeting / Price List Inquiry" required>
                        </div>
                        <div class="col-md-5">
                            <label class="fw-bold mb-1">Match Type <span class="text-danger">*</span></label>
                            <select name="match_type" class="form-select" required>
                                <option value="contains">Contains Keyword</option>
                                <option value="exact">Exact Match</option>
                                <option value="starts_with">Starts With</option>
                                <option value="fallback">Fallback (No match found)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Trigger Keywords (comma separated)</label>
                            <input type="text" name="keywords" class="form-control" placeholder="e.g. hello, hi, price, info, help">
                            <small class="text-muted">Comma separated words that will trigger this automated response.</small>
                        </div>

                        <!-- Target Audience Selector -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Target Audience / Chat Scope <span class="text-danger">*</span></label>
                            <select name="target_type" class="form-select targetTypeSelect" data-prefix="create" required>
                                <option value="all">All Chats (Direct & Groups)</option>
                                <option value="all_individual">Direct / 1-to-1 Chats Only</option>
                                <option value="all_group">Group Chats Only</option>
                                <option value="saved_contacts">Saved Contacts Only</option>
                                <option value="unsaved_contacts">Unsaved / New Numbers Only</option>
                                <option value="specific_contacts">Specific Phone Numbers</option>
                                <option value="specific_groups">Specific WhatsApp Groups</option>
                                <option value="contact_list">Contact List / Audience Group</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Assigned WhatsApp Account</label>
                            <select name="session_id" class="form-select">
                                <option value="">All My Connected Accounts</option>
                                @foreach($connectedAccounts as $acc)
                                    <option value="{{ $acc->session_id }}">{{ $acc->account_name }} (+{{ $acc->phone_number ?? $acc->session_id }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Conditional Target Details -->
                        <div class="col-12 conditional-target d-none" id="createSpecificContactsDiv">
                            <label class="fw-bold mb-1">Specific Target Numbers (comma separated)</label>
                            <input type="text" name="target_contacts" class="form-control" placeholder="e.g. 923001234567, 923007654321">
                            <small class="text-muted">Only these specific recipient phone numbers will trigger this auto-reply.</small>
                        </div>

                        <div class="col-12 conditional-target d-none" id="createSpecificGroupsDiv">
                            <label class="fw-bold mb-1">Select Target WhatsApp Groups</label>
                            <select name="target_group_ids[]" class="form-select" multiple style="min-height: 90px;">
                                @foreach($groups as $grp)
                                    <option value="{{ $grp->group_id }}">{{ $grp->group_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple groups.</small>
                        </div>

                        <div class="col-12 conditional-target d-none" id="createContactListDiv">
                            <label class="fw-bold mb-1">Select Contact List</label>
                            <select name="contact_list_id" class="form-select">
                                <option value="">-- Choose Contact List --</option>
                                @foreach($contactLists as $list)
                                    <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->contacts_count }} contacts)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="fw-bold mb-1">Reply Format</label>
                            <select name="reply_type" class="form-select">
                                <option value="text">Text Only</option>
                                <option value="image">Image Attachment</option>
                                <option value="video">Video Attachment</option>
                                <option value="document">Document Attachment</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="fw-bold mb-1">Reply Message Content <span class="text-danger">*</span></label>
                            <textarea name="reply_message" rows="4" class="form-control" placeholder="Type the automated response message here..." required></textarea>
                            <small class="text-muted">Tags supported: <code>@{{name}}</code>, <code>@{{sender_phone}}</code>, <code>@{{time}}</code>, <code>@{{date}}</code></small>
                        </div>

                        <div class="col-12">
                            <label class="fw-bold mb-1">Media URL (Optional)</label>
                            <input type="url" name="media_url" class="form-control" placeholder="https://example.com/banner.jpg">
                        </div>

                        <!-- Human Behavior & Anti-Ban System -->
                        <div class="col-12">
                            <div class="card border rounded-3 bg-light p-3 mt-2">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="las la-user-shield text-danger fs-4"></i>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">Human Behavior & Anti-Ban Protection</h6>
                                        <small class="text-muted">Simulate natural human interaction delays to protect your WhatsApp account from spam detection.</small>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="fw-bold small mb-1">
                                            <i class="las la-eye text-primary me-1"></i> Mark as Seen Delay
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="read_delay_seconds" class="form-control" min="0" max="60" value="2">
                                            <span class="input-group-text">sec</span>
                                        </div>
                                        <small class="text-muted fs-8 d-block mt-1">Delay before turning ticks Blue for sender.</small>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-bold small mb-1">
                                            <i class="las la-keyboard text-success me-1"></i> Typing Animation
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="typing_duration_seconds" class="form-control" min="0" max="60" value="3">
                                            <span class="input-group-text">sec</span>
                                        </div>
                                        <small class="text-muted fs-8 d-block mt-1">Shows "typing..." presence animation.</small>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-bold small mb-1">
                                            <i class="las la-hourglass-half text-warning me-1"></i> Send Message Delay
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="reply_delay_seconds" class="form-control" min="0" max="60" value="2">
                                            <span class="input-group-text">sec</span>
                                        </div>
                                        <small class="text-muted fs-8 d-block mt-1">Natural pause before final dispatch.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base"><i class="las la-save me-1"></i> Save Bot</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Bot -->
<div class="modal fade" id="editBotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-edit me-1"></i> Edit Keyword Bot</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBotForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row gy-3">
                        <div class="col-md-7">
                            <label class="fw-bold mb-1">Bot Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <label class="fw-bold mb-1">Match Type <span class="text-danger">*</span></label>
                            <select name="match_type" id="editMatch" class="form-select" required>
                                <option value="contains">Contains Keyword</option>
                                <option value="exact">Exact Match</option>
                                <option value="starts_with">Starts With</option>
                                <option value="fallback">Fallback (No match found)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Trigger Keywords</label>
                            <input type="text" name="keywords" id="editKeywords" class="form-control">
                        </div>

                        <!-- Target Audience (Edit) -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Target Audience / Chat Scope <span class="text-danger">*</span></label>
                            <select name="target_type" id="editTargetType" class="form-select targetTypeSelect" data-prefix="edit" required>
                                <option value="all">All Chats (Direct & Groups)</option>
                                <option value="all_individual">Direct / 1-to-1 Chats Only</option>
                                <option value="all_group">Group Chats Only</option>
                                <option value="saved_contacts">Saved Contacts Only</option>
                                <option value="unsaved_contacts">Unsaved / New Numbers Only</option>
                                <option value="specific_contacts">Specific Phone Numbers</option>
                                <option value="specific_groups">Specific WhatsApp Groups</option>
                                <option value="contact_list">Contact List / Audience Group</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Assigned Account</label>
                            <select name="session_id" id="editSession" class="form-select">
                                <option value="">All My Connected Accounts</option>
                                @foreach($connectedAccounts as $acc)
                                    <option value="{{ $acc->session_id }}">{{ $acc->account_name }} (+{{ $acc->phone_number ?? $acc->session_id }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Conditional Target Details (Edit) -->
                        <div class="col-12 conditional-target d-none" id="editSpecificContactsDiv">
                            <label class="fw-bold mb-1">Specific Target Numbers (comma separated)</label>
                            <input type="text" name="target_contacts" id="editTargetContacts" class="form-control" placeholder="e.g. 923001234567, 923007654321">
                        </div>

                        <div class="col-12 conditional-target d-none" id="editSpecificGroupsDiv">
                            <label class="fw-bold mb-1">Select Target WhatsApp Groups</label>
                            <select name="target_group_ids[]" id="editTargetGroupIds" class="form-select" multiple style="min-height: 90px;">
                                @foreach($groups as $grp)
                                    <option value="{{ $grp->group_id }}">{{ $grp->group_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 conditional-target d-none" id="editContactListDiv">
                            <label class="fw-bold mb-1">Select Contact List</label>
                            <select name="contact_list_id" id="editContactListId" class="form-select">
                                <option value="">-- Choose Contact List --</option>
                                @foreach($contactLists as $list)
                                    <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->contacts_count }} contacts)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="fw-bold mb-1">Reply Format</label>
                            <select name="reply_type" id="editType" class="form-select">
                                <option value="text">Text Only</option>
                                <option value="image">Image Attachment</option>
                                <option value="video">Video Attachment</option>
                                <option value="document">Document Attachment</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="fw-bold mb-1">Reply Message Content <span class="text-danger">*</span></label>
                            <textarea name="reply_message" id="editMessage" rows="4" class="form-control" required></textarea>
                            <small class="text-muted">Tags supported: <code>@{{name}}</code>, <code>@{{sender_phone}}</code>, <code>@{{time}}</code>, <code>@{{date}}</code></small>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Media URL (Optional)</label>
                            <input type="url" name="media_url" id="editMedia" class="form-control">
                        </div>

                        <!-- Human Behavior & Anti-Ban System (Edit) -->
                        <div class="col-12">
                            <div class="card border rounded-3 bg-light p-3 mt-2">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="las la-user-shield text-danger fs-4"></i>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">Human Behavior & Anti-Ban Protection</h6>
                                        <small class="text-muted">Simulate natural human interaction delays to protect your WhatsApp account from spam detection.</small>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="fw-bold small mb-1">
                                            <i class="las la-eye text-primary me-1"></i> Mark as Seen Delay
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="read_delay_seconds" id="editSeenDelay" class="form-control" min="0" max="60" value="2">
                                            <span class="input-group-text">sec</span>
                                        </div>
                                        <small class="text-muted fs-8 d-block mt-1">Delay before turning ticks Blue.</small>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-bold small mb-1">
                                            <i class="las la-keyboard text-success me-1"></i> Typing Animation
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="typing_duration_seconds" id="editTypingDuration" class="form-control" min="0" max="60" value="3">
                                            <span class="input-group-text">sec</span>
                                        </div>
                                        <small class="text-muted fs-8 d-block mt-1">Shows "typing..." presence animation.</small>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-bold small mb-1">
                                            <i class="las la-hourglass-half text-warning me-1"></i> Send Message Delay
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="reply_delay_seconds" id="editSendDelay" class="form-control" min="0" max="60" value="2">
                                            <span class="input-group-text">sec</span>
                                        </div>
                                        <small class="text-muted fs-8 d-block mt-1">Natural pause before dispatch.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base"><i class="las la-save me-1"></i> Update Bot</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function ($) {
        "use strict";

        function handleTargetTypeToggle(selectElem) {
            var val = $(selectElem).val();
            var prefix = $(selectElem).data('prefix'); // 'create' or 'edit'

            // Hide all conditional divs for this modal
            $('#' + prefix + 'SpecificContactsDiv').addClass('d-none');
            $('#' + prefix + 'SpecificGroupsDiv').addClass('d-none');
            $('#' + prefix + 'ContactListDiv').addClass('d-none');

            if (val === 'specific_contacts') {
                $('#' + prefix + 'SpecificContactsDiv').removeClass('d-none');
            } else if (val === 'specific_groups') {
                $('#' + prefix + 'SpecificGroupsDiv').removeClass('d-none');
            } else if (val === 'contact_list') {
                $('#' + prefix + 'ContactListDiv').removeClass('d-none');
            }
        }

        $('.targetTypeSelect').on('change', function () {
            handleTargetTypeToggle(this);
        });

        $('.btnEditBot').on('click', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var match = $(this).data('match');
            var keywords = $(this).data('keywords');
            var type = $(this).data('type');
            var message = $(this).data('message');
            var media = $(this).data('media');
            var session = $(this).data('session');
            var targetType = $(this).data('target-type') || 'all';
            var targetContacts = $(this).data('target-contacts') || '';
            var targetGroups = $(this).data('target-groups') || [];
            var contactList = $(this).data('contact-list') || '';
            var seen = $(this).data('seen');
            var typing = $(this).data('typing');
            var delay = $(this).data('delay');

            $('#editName').val(name);
            $('#editMatch').val(match);
            $('#editKeywords').val(keywords);
            $('#editType').val(type);
            $('#editMessage').val(message);
            $('#editMedia').val(media);
            $('#editSession').val(session);
            $('#editTargetType').val(targetType);
            $('#editTargetContacts').val(targetContacts);
            $('#editContactListId').val(contactList);

            if (Array.isArray(targetGroups)) {
                $('#editTargetGroupIds').val(targetGroups);
            }

            $('#editSeenDelay').val(seen !== undefined ? seen : 2);
            $('#editTypingDuration').val(typing !== undefined ? typing : 3);
            $('#editSendDelay').val(delay !== undefined ? delay : 2);

            handleTargetTypeToggle($('#editTargetType'));

            var actionUrl = "{{ url('user/autoreply/update') }}/" + id;
            $('#editBotForm').attr('action', actionUrl);

            $('#editBotModal').modal('show');
        });

    })(jQuery);
</script>
@endpush
