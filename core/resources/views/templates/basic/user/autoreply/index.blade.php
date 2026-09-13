@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Auto-Reply & Keyword Bots</h4>
                <p class="text-muted mb-0">Create smart bots that automatically respond to your customers on WhatsApp.</p>
            </div>
            <div>
                <button type="button" class="btn btn--base" data-bs-toggle="modal" data-bs-target="#createBotModal">
                    <i class="las la-plus-circle me-1"></i> Add Keyword Bot
                </button>
            </div>
        </div>

        <!-- 3 Metric Cards -->
        <div class="row gy-3 mb-4">
            <div class="col-md-4">
                <div class="card custom--card p-3 border shadow-sm">
                    <span class="text-muted small fw-bold">Total Bot Rules</span>
                    <h4 class="fw-bold text-dark mb-0">{{ $totalBots }} <span class="text-muted fs-6 fw-normal">/ {{ $plan->autoreply_limit ?? 10 }}</span></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom--card p-3 border shadow-sm">
                    <span class="text-muted small fw-bold">Active Rules</span>
                    <h4 class="fw-bold text-success mb-0">{{ $activeBots }}</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom--card p-3 border shadow-sm">
                    <span class="text-muted small fw-bold">Total Responses Dispatched</span>
                    <h4 class="fw-bold text-primary mb-0">{{ $totalHits }}</h4>
                </div>
            </div>
        </div>

        <!-- Bots Table -->
        <div class="card custom--card border shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bot Name & Account</th>
                                <th>Match Type</th>
                                <th>Trigger Keywords</th>
                                <th>Reply Message</th>
                                <th>Human Behavior</th>
                                <th>Hits</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($botRules as $rule)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $rule->name }}</div>
                                        <small class="text-muted">
                                            Account: {{ $rule->account ? $rule->account->account_name : 'All Accounts' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase">{{ $rule->match_type }}</span>
                                    </td>
                                    <td>
                                        @if($rule->match_type === 'fallback')
                                            <span class="badge bg-dark">Fallback (Any unmatched message)</span>
                                        @else
                                            <span class="fw-bold text-primary">{{ $rule->keywords }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted small text-truncate" style="max-width: 200px;">
                                            {{ $rule->reply_message }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1" style="font-size: 11px;">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" title="Mark as Seen Delay">
                                                <i class="las la-eye me-1"></i>Seen: {{ $rule->read_delay_seconds ?? 0 }}s
                                            </span>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" title="Typing Animation">
                                                <i class="las la-keyboard me-1"></i>Typing: {{ $rule->typing_duration_seconds ?? 0 }}s
                                            </span>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" title="Send Message Delay">
                                                <i class="las la-hourglass-half me-1"></i>Delay: {{ $rule->reply_delay_seconds ?? ($rule->delay_seconds ?? 0) }}s
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $rule->hit_count }} hits</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('user.autoreply.status', $rule->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-{{ $rule->status == 1 ? 'success' : 'secondary' }}" title="Toggle Status">
                                                <i class="las la-{{ $rule->status == 1 ? 'check-circle' : 'ban' }}"></i>
                                                {{ $rule->status == 1 ? 'Active' : 'Disabled' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm btnEditBot" 
                                                    data-id="{{ $rule->id }}"
                                                    data-name="{{ $rule->name }}"
                                                    data-match="{{ $rule->match_type }}"
                                                    data-keywords="{{ $rule->keywords }}"
                                                    data-type="{{ $rule->reply_type }}"
                                                    data-message="{{ $rule->reply_message }}"
                                                    data-media="{{ $rule->media_url }}"
                                                    data-session="{{ $rule->session_id }}"
                                                    data-seen="{{ $rule->read_delay_seconds ?? 2 }}"
                                                    data-typing="{{ $rule->typing_duration_seconds ?? 3 }}"
                                                    data-delay="{{ $rule->reply_delay_seconds ?? ($rule->delay_seconds ?? 2) }}"
                                                    title="Edit Bot">
                                                <i class="las la-edit"></i>
                                            </button>

                                            <form action="{{ route('user.autoreply.delete', $rule->id) }}" method="POST" onsubmit="return confirm('Delete this auto-reply bot?')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
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
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Assigned WhatsApp Account</label>
                            <select name="session_id" class="form-select">
                                <option value="">All My Connected Accounts</option>
                                @foreach($connectedAccounts as $acc)
                                    <option value="{{ $acc->session_id }}">{{ $acc->account_name }} (+{{ $acc->phone_number ?? $acc->session_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Assigned Account</label>
                            <select name="session_id" id="editSession" class="form-select">
                                <option value="">All My Connected Accounts</option>
                                @foreach($connectedAccounts as $acc)
                                    <option value="{{ $acc->session_id }}">{{ $acc->account_name }} (+{{ $acc->phone_number ?? $acc->session_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
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

        $('.btnEditBot').on('click', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var match = $(this).data('match');
            var keywords = $(this).data('keywords');
            var type = $(this).data('type');
            var message = $(this).data('message');
            var media = $(this).data('media');
            var session = $(this).data('session');
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
            $('#editSeenDelay').val(seen !== undefined ? seen : 2);
            $('#editTypingDuration').val(typing !== undefined ? typing : 3);
            $('#editSendDelay').val(delay !== undefined ? delay : 2);

            var actionUrl = "{{ url('user/autoreply/update') }}/" + id;
            $('#editBotForm').attr('action', actionUrl);

            $('#editBotModal').modal('show');
        });

    })(jQuery);
</script>
@endpush
