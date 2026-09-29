@extends($activeTemplate . 'layouts.master')

@push('style')
<style>
    /* Enhanced text legibility and contrast on user campaign page */
    .dashboard-section label {
        color: #0f172a !important;
        font-weight: 700 !important;
        font-size: 13.5px !important;
    }
    .dashboard-section .form-control,
    .dashboard-section .form-select,
    .dashboard-section textarea {
        color: #0f172a !important;
        font-weight: 600 !important;
        border: 1.5px solid #cbd5e1 !important;
        background-color: #ffffff !important;
    }
    .dashboard-section .form-control:focus,
    .dashboard-section .form-select:focus,
    .dashboard-section textarea:focus {
        border-color: #25d366 !important;
        color: #0f172a !important;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.2) !important;
    }
    .dashboard-section .form-control::placeholder,
    .dashboard-section textarea::placeholder {
        color: #64748b !important;
        opacity: 1 !important;
        font-weight: 400 !important;
    }
    .dashboard-section .input-group-text {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border: 1.5px solid #cbd5e1 !important;
        font-weight: 700 !important;
    }
    .dashboard-section .text-muted,
    .dashboard-section small.text-muted,
    .dashboard-section p.text-muted {
        color: #334155 !important;
        font-weight: 500 !important;
        font-size: 12.5px !important;
    }
    .group-select-card {
        border-color: #cbd5e1 !important;
        background-color: #ffffff;
    }
    .group-select-card:hover {
        border-color: #25d366 !important;
        background-color: #f0fdf4 !important;
    }
    .group-select-card.selected {
        border-color: #25d366 !important;
        background-color: #f0fdf4 !important;
        box-shadow: 0 0 0 1.5px #25d366 inset;
    }
</style>
@endpush

@section('content')
<div class="dashboard-section py-2 py-sm-3">
    <div class="container-fluid px-2 px-sm-3">
        
        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="mb-1 fw-bold text-dark"><i class="las la-bullhorn text--base me-1"></i> Run WhatsApp Campaign</h5>
                <p class="text-muted small mb-0">Select your sender account, audience, message template, and anti-ban delay timing.</p>
            </div>
            <div>
                <a href="{{ route('user.campaigns.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="las la-arrow-left me-1"></i> Back to Campaigns
                </a>
            </div>
        </div>

        <div class="row justify-content-center g-3">
            <div class="col-12 col-xl-10">
                <div class="card custom--card border shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0 fw-bold"><i class="las la-edit text--base me-1"></i> Campaign Composer</h6>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                            <i class="las la-shield-alt me-1"></i> Anti-Ban Protected
                        </span>
                    </div>
                    <div class="card-body p-3 p-sm-4">
                        <form action="{{ route('user.campaigns.store') }}" method="POST" id="campaignCreateForm">
                            @csrf
                            <div class="row g-3">
                                
                                <div class="col-12 col-md-7">
                                    <label class="fw-bold mb-1 small text-dark">Campaign Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Weekend Flash Sale / Product Launch" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-12 col-md-5">
                                    <label class="fw-bold mb-1 small text-dark">Sender WhatsApp Account <span class="text-danger">*</span></label>
                                    <select name="session_id" class="form-select form-select-sm" required>
                                        @forelse($connectedAccounts as $acc)
                                            <option value="{{ $acc->session_id }}">{{ $acc->account_name }} (+{{ $acc->phone_number ?? $acc->session_id }})</option>
                                        @empty
                                            <option value="">-- No Active WhatsApp Account Found --</option>
                                        @endforelse
                                    </select>
                                    @if($connectedAccounts->isEmpty())
                                        <small class="text-danger d-block mt-1">Please <a href="{{ route('user.whatsapp.create') }}">connect a WhatsApp account</a> first.</small>
                                    @endif
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="fw-bold mb-1 small text-dark">Target Audience <span class="text-danger">*</span></label>
                                    <select name="target_type" id="targetType" class="form-select form-select-sm" required>
                                        <option value="all">All Contacts & Groups ({{ $totalAll ?? ($totalContacts + ($totalGroups ?? 0)) }} total)</option>
                                        @if(($totalContacts ?? 0) > 0)
                                            <option value="contacts">Individual Contacts Only ({{ $totalContacts }} contacts)</option>
                                        @endif
                                        @if(($totalGroups ?? 0) > 0)
                                            <option value="groups">WhatsApp Groups Only ({{ $totalGroups }} groups)</option>
                                        @endif
                                        <option value="selected_groups" {{ old('target_type') == 'selected_groups' ? 'selected' : '' }}>🎯 Specific WhatsApp Groups (Pick Targeted Groups)</option>
                                        @if(isset($contactLists) && $contactLists->count() > 0)
                                            <optgroup label="My Contact Lists">
                                                @foreach($contactLists as $list)
                                                    <option value="list_{{ $list->id }}">📁 {{ $list->name }} ({{ $list->contacts_count }} {{ $list->type === 'groups' ? 'Groups' : 'Contacts' }})</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="fw-bold mb-1 small text-dark">Load Saved Template (Optional)</label>
                                    <select id="templateSelect" class="form-select form-select-sm">
                                        <option value="">-- Write Custom Message Below --</option>
                                        @foreach($templates as $t)
                                            <option value="{{ $t->id }}" data-message="{{ $t->message }}" data-media="{{ $t->media_url }}" data-type="{{ $t->type }}">{{ $t->name }} ({{ $t->type }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Specific Target Groups Selector Box -->
                                <div class="col-12 d-none" id="specificGroupsWrapper">
                                    <div class="card border rounded-3 shadow-none" style="background-color: #f8fafc; border-color: #cbd5e1 !important;">
                                        <div class="card-header bg-white py-2 px-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-dark small"><i class="las la-tasks text-primary me-1"></i> Target WhatsApp Groups:</span>
                                                <span class="badge bg-primary text-white" id="selectedGroupsCount">0 Selected</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-xs btn-outline-primary fw-semibold" id="btnSelectAllGroups">
                                                    <i class="las la-check-double me-1"></i> Select All
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary fw-semibold" id="btnDeselectAllGroups">
                                                    <i class="las la-times me-1"></i> Deselect All
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body p-2 p-sm-3">
                                            <div class="mb-2">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white"><i class="las la-search text-muted"></i></span>
                                                    <input type="text" id="searchGroupInput" class="form-control" placeholder="Search group name or group ID...">
                                                </div>
                                            </div>
                                            <div class="row g-2" id="groupsChecklist" style="max-height: 280px; overflow-y: auto; padding-right: 2px;">
                                                @forelse($groups as $g)
                                                    <div class="col-12 col-md-6 col-lg-4 group-item-col">
                                                        <label class="group-select-card p-2 border rounded-2 bg-white w-100 d-flex align-items-center justify-content-between mb-0" for="grp_{{ $loop->index }}" style="cursor: pointer; transition: all 0.15s ease;">
                                                            <div class="d-flex align-items-center text-truncate me-2" style="min-width: 0;">
                                                                <input type="checkbox" name="target_group_ids[]" value="{{ $g->group_id }}" class="form-check-input group-chk me-2 flex-shrink-0" id="grp_{{ $loop->index }}" {{ (is_array(old('target_group_ids')) && in_array($g->group_id, old('target_group_ids'))) ? 'checked' : '' }} style="cursor: pointer; width: 1.15em; height: 1.15em;">
                                                                <div class="text-truncate">
                                                                    <strong class="text-dark d-block text-truncate group-name-label" style="font-size: 13px;" title="{{ $g->group_name }}">{{ $g->group_name }}</strong>
                                                                    <span class="font-monospace text-muted d-block text-truncate" style="font-size: 10.5px;">{{ $g->group_id }}</span>
                                                                </div>
                                                            </div>
                                                            @if(isset($g->member_count) && $g->member_count > 0)
                                                                <span class="badge bg-light text-secondary border flex-shrink-0" style="font-size: 10.5px;">
                                                                    <i class="las la-users me-0.5"></i>{{ $g->member_count }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-light text-secondary border flex-shrink-0" style="font-size: 10.5px;">
                                                                    <i class="las la-users me-0.5"></i>Group
                                                                </span>
                                                            @endif
                                                        </label>
                                                    </div>
                                                @empty
                                                    <div class="col-12 text-center text-muted py-3">
                                                        <i class="las la-users-slash fs-3 d-block mb-1 text-secondary"></i>
                                                        No WhatsApp groups found in your account.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="fw-bold mb-1 small text-dark">Broadcast Message <span class="text-danger">*</span></label>
                                    <textarea name="message" id="campaignMessage" rows="5" class="form-control" placeholder="Write your message here... Use tags like @name and @phone for automatic customer personalization." required>{{ old('message') }}</textarea>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <span class="badge bg-light text-dark border cursor-pointer" onclick="insertTag('@name')"><code>@{{name}}</code></span>
                                        <span class="badge bg-light text-dark border cursor-pointer" onclick="insertTag('@phone')"><code>@{{phone}}</code></span>
                                    </div>
                                </div>

                                <div class="col-12 col-md-8">
                                    <label class="fw-bold mb-1 small text-dark">Media Attachment URL (Optional)</label>
                                    <input type="url" name="media_url" id="mediaUrl" class="form-control form-control-sm" placeholder="https://example.com/banner.jpg" value="{{ old('media_url') }}">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="fw-bold mb-1 small text-dark">Media Type</label>
                                    <select name="media_type" id="mediaType" class="form-select form-select-sm">
                                        <option value="text">Text Only</option>
                                        <option value="image">Image (JPG/PNG)</option>
                                        <option value="video">Video (MP4)</option>
                                        <option value="document">Document (PDF/DOC)</option>
                                    </select>
                                </div>

                                <!-- Anti-Ban Human Behaviour Setting Box -->
                                <div class="col-12">
                                    <div class="card border border-danger-subtle bg-white shadow-none rounded-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold">
                                                            <i class="las la-shield-alt me-1"></i> Anti-Ban Human Behaviour
                                                        </span>
                                                        <h6 class="mb-0 fw-bold text-dark fs-6">Safety & Delay Rules</h6>
                                                    </div>
                                                    <p class="text-muted small mb-0 mt-1">
                                                        Ensure Anti-Ban settings are reviewed before launching to keep your numbers 100% safe.
                                                    </p>
                                                </div>
                                                <button type="button" class="btn btn-sm text-white" id="btnOpenAntiBanModal" data-bs-toggle="modal" data-bs-target="#antiBanSettingsModal" style="background-color: #e6535c; border-color: #e6535c; border-radius: 4px; font-weight: 600;">
                                                    <i class="las la-sliders-h me-1"></i> Set Anti-Ban Settings
                                                </button>
                                            </div>

                                            <!-- Live Rules Badges Grid -->
                                            <div class="row g-2 mt-2 pt-2 border-top">
                                                <div class="col-6 col-sm-4 col-md-auto">
                                                    <div class="p-2 bg-light rounded text-center border">
                                                        <small class="text-muted d-block" style="font-size: 11px;">Random Delay</small>
                                                        <span class="fw-bold text-dark small" id="badgeDelayRange">{{ $botSettings->min_delay_seconds ?? 30 }}s - {{ $botSettings->max_delay_seconds ?? 60 }}s</span>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-4 col-md-auto">
                                                    <div class="p-2 bg-light rounded text-center border">
                                                        <small class="text-muted d-block" style="font-size: 11px;">Delay After Count</small>
                                                        <span class="fw-bold text-dark small" id="badgeDelayCount">Every {{ $botSettings->delay_after_count ?? 50 }} msgs</span>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-4 col-md-auto">
                                                    <div class="p-2 bg-light rounded text-center border">
                                                        <small class="text-muted d-block" style="font-size: 11px;">Delay Duration</small>
                                                        <span class="fw-bold text-dark small" id="badgeDelayDuration">Pause {{ $botSettings->delay_after_duration ?? 5 }}s</span>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-4 col-md-auto">
                                                    <div class="p-2 bg-light rounded text-center border">
                                                        <small class="text-muted d-block" style="font-size: 11px;">Reset After Count</small>
                                                        <span class="fw-bold text-dark small" id="badgeResetCount">Reset at {{ $botSettings->reset_after_count ?? 100 }} msgs</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Hidden / Synced Inputs for form submission -->
                                            <input type="hidden" name="min_delay_seconds" id="inputMinDelay" value="{{ $botSettings->min_delay_seconds ?? 30 }}">
                                            <input type="hidden" name="max_delay_seconds" id="inputMaxDelay" value="{{ $botSettings->max_delay_seconds ?? 60 }}">
                                            <input type="hidden" name="delay_after_count" id="inputDelayAfterCount" value="{{ $botSettings->delay_after_count ?? 50 }}">
                                            <input type="hidden" name="delay_after_duration" id="inputDelayAfterDuration" value="{{ $botSettings->delay_after_duration ?? 5 }}">
                                            <input type="hidden" name="reset_after_count" id="inputResetAfterCount" value="{{ $botSettings->reset_after_count ?? 100 }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Broadcast Execution Mode -->
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded border">
                                        <h6 class="fw-bold text-dark mb-1 small"><i class="las la-rocket text-primary me-1"></i> Broadcast Execution Mode</h6>
                                        <select name="dispatch_mode" class="form-select form-select-sm" required>
                                            <option value="auto" selected>⚡ Automatic Background Broadcast (Server runs continuously without keeping browser open)</option>
                                            <option value="manual">🖥 Interactive In-Browser Broadcast (Real-time live progress on screen)</option>
                                        </select>
                                        <small class="text-muted d-block mt-1">In Automatic Background Mode, the campaign dispatches continuously on the server until completed or paused by you.</small>
                                    </div>
                                </div>

                                <!-- Auto-Restart Loop Option -->
                                <div class="col-12">
                                    <div class="form-check form-switch p-3 bg-light rounded border d-flex align-items-center justify-content-between m-0">
                                        <div class="pe-2">
                                            <label class="form-check-label fw-bold text-dark mb-0 small" for="autoRestartCheck" style="cursor: pointer;">
                                                <i class="las la-sync text-success me-1"></i> Auto-Restart Broadcast Loop
                                            </label>
                                            <small class="text-muted d-block mt-1">When all recipients receive the message, automatically start broadcasting again from the beginning non-stop until you pause it.</small>
                                        </div>
                                        <input class="form-check-input ms-2" type="checkbox" name="auto_restart" id="autoRestartCheck" value="1" checked style="width: 2.4em; height: 1.25em; cursor: pointer; flex-shrink: 0;">
                                    </div>
                                </div>

                                <div class="col-12 text-end mt-3">
                                    <button type="submit" id="btnSubmitCampaign" class="btn btn--base px-4 py-2 w-100 w-sm-auto" {{ $connectedAccounts->isEmpty() ? 'disabled' : '' }}>
                                        <i class="las la-check-circle me-1"></i> Launch Campaign Broadcast
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Exact Anti-Ban Human Behaviour Modal Matching Screenshot -->
<div class="modal fade" id="antiBanSettingsModal" tabindex="-1" aria-labelledby="antiBanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.18);">
            <div class="modal-header border-0 pb-0 pt-3 px-3 px-sm-4 bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold text-dark fs-6" id="antiBanModalLabel">Anti-Ban Human Behaviour</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-3 px-sm-4 pt-3 pb-2">
                <div id="modalNoticeBox" class="alert alert-info py-2 px-3 mb-3 small d-none">
                    <i class="las la-info-circle me-1"></i> Please review and save your Anti-Ban human behaviour settings before launching this campaign.
                </div>
                <form id="antiBanModalForm">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Per Message Minimum Delay (Seconds) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="modalMinDelay" class="form-control form-control-sm" value="{{ $botSettings->min_delay_seconds ?? 30 }}" min="1" max="600" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Per Message Maximum Delay (Seconds) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="modalMaxDelay" class="form-control form-control-sm" value="{{ $botSettings->max_delay_seconds ?? 60 }}" min="1" max="600" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Delay After Count <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="modalDelayAfterCount" class="form-control form-control-sm" value="{{ $botSettings->delay_after_count ?? 50 }}" min="1" max="5000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Delay After Duration (Seconds) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="modalDelayAfterDuration" class="form-control form-control-sm" value="{{ $botSettings->delay_after_duration ?? 5 }}" min="1" max="3600" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Reset After Count <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="modalResetAfterCount" class="form-control form-control-sm" value="{{ $botSettings->reset_after_count ?? 100 }}" min="1" max="10000" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-3 px-sm-4 pb-3 d-flex justify-content-end gap-2 bg-transparent">
                <button type="button" class="btn" data-bs-dismiss="modal" style="border: 1px solid #e6535c; color: #e6535c; background: #fff; border-radius: 4px; padding: 7px 22px; font-weight: 500;">Close</button>
                <button type="button" class="btn text-white" id="btnSaveAntiBan" style="background-color: #e6535c; border-color: #e6535c; border-radius: 4px; padding: 7px 24px; font-weight: 500;">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    function insertTag(tag) {
        var el = document.getElementById('campaignMessage');
        el.value += ' ' + tag;
    }

    (function ($) {
        "use strict";

        var antiBanReviewed = false;
        var pendingSubmit = false;

        // Toggle Specific Groups Selector
        function toggleTargetTypeUI() {
            var val = $('#targetType').val();
            if (val === 'selected_groups') {
                $('#specificGroupsWrapper').removeClass('d-none');
            } else {
                $('#specificGroupsWrapper').addClass('d-none');
            }
        }
        $('#targetType').on('change', toggleTargetTypeUI);
        toggleTargetTypeUI();

        // Update Group Counter & Card Selected Class
        function updateGroupCounter() {
            var count = $('.group-chk:checked').length;
            $('#selectedGroupsCount').text(count + ' Selected');
            $('.group-chk').each(function() {
                if ($(this).is(':checked')) {
                    $(this).closest('.group-select-card').addClass('selected');
                } else {
                    $(this).closest('.group-select-card').removeClass('selected');
                }
            });
        }
        $(document).on('change', '.group-chk', updateGroupCounter);
        updateGroupCounter();

        // Select All / Deselect All Groups
        $('#btnSelectAllGroups').on('click', function() {
            $('.group-item-col:visible .group-chk').prop('checked', true);
            updateGroupCounter();
        });

        $('#btnDeselectAllGroups').on('click', function() {
            $('.group-item-col:visible .group-chk').prop('checked', false);
            updateGroupCounter();
        });

        // Filter / Search Groups
        $('#searchGroupInput').on('keyup input', function() {
            var q = $(this).val().toLowerCase().trim();
            $('.group-item-col').each(function() {
                var name = $(this).find('.group-name-label').text().toLowerCase();
                var id = $(this).find('.font-monospace').text().toLowerCase();
                if (!q || name.indexOf(q) !== -1 || id.indexOf(q) !== -1) {
                    $(this).removeClass('d-none');
                } else {
                    $(this).addClass('d-none');
                }
            });
        });

        // Sync template
        $('#templateSelect').on('change', function () {
            var $opt = $(this).find(':selected');
            if ($opt.val()) {
                $('#campaignMessage').val($opt.data('message'));
                $('#mediaUrl').val($opt.data('media') || '');
                $('#mediaType').val($opt.data('type') || 'text');
            }
        });

        // When user opens modal manually
        $('#btnOpenAntiBanModal').on('click', function() {
            $('#modalNoticeBox').addClass('d-none');
            // Populate modal with current input values
            $('#modalMinDelay').val($('#inputMinDelay').val());
            $('#modalMaxDelay').val($('#inputMaxDelay').val());
            $('#modalDelayAfterCount').val($('#inputDelayAfterCount').val());
            $('#modalDelayAfterDuration').val($('#inputDelayAfterDuration').val());
            $('#modalResetAfterCount').val($('#inputResetAfterCount').val());
        });

        // Save Anti-Ban Settings via AJAX & Form sync
        $('#btnSaveAntiBan').on('click', function() {
            var minDelay = parseInt($('#modalMinDelay').val()) || 30;
            var maxDelay = parseInt($('#modalMaxDelay').val()) || 60;
            var delayCount = parseInt($('#modalDelayAfterCount').val()) || 50;
            var delayDuration = parseInt($('#modalDelayAfterDuration').val()) || 5;
            var resetCount = parseInt($('#modalResetAfterCount').val()) || 100;

            if (maxDelay < minDelay) {
                maxDelay = minDelay;
                $('#modalMaxDelay').val(maxDelay);
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('user.campaigns.anti_ban.save') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    min_delay_seconds: minDelay,
                    max_delay_seconds: maxDelay,
                    delay_after_count: delayCount,
                    delay_after_duration: delayDuration,
                    reset_after_count: resetCount
                },
                success: function(res) {
                    $btn.prop('disabled', false).text('Save');
                    // Sync inputs
                    $('#inputMinDelay').val(minDelay);
                    $('#inputMaxDelay').val(maxDelay);
                    $('#inputDelayAfterCount').val(delayCount);
                    $('#inputDelayAfterDuration').val(delayDuration);
                    $('#inputResetAfterCount').val(resetCount);

                    // Sync badges
                    $('#badgeDelayRange').text(minDelay + 's - ' + maxDelay + 's');
                    $('#badgeDelayCount').text('Every ' + delayCount + ' msgs');
                    $('#badgeDelayDuration').text('Pause ' + delayDuration + 's');
                    $('#badgeResetCount').text('Reset at ' + resetCount + ' msgs');

                    antiBanReviewed = true;
                    var modalEl = bootstrap.Modal.getInstance(document.getElementById('antiBanSettingsModal'));
                    if (modalEl) {
                        modalEl.hide();
                    }

                    notify('success', 'Anti-Ban Human Behaviour settings saved successfully!');

                    if (pendingSubmit) {
                        pendingSubmit = false;
                        $('#campaignCreateForm').off('submit').submit();
                    }
                },
                error: function(err) {
                    $btn.prop('disabled', false).text('Save');
                    var msg = 'Failed to save anti-ban settings.';
                    if (err.responseJSON && err.responseJSON.message) {
                        msg = err.responseJSON.message;
                    }
                    notify('error', msg);
                }
            });
        });

        // Before creating campaign: validate selection & ensure user has reviewed Anti-Ban
        $('#campaignCreateForm').on('submit', function(e) {
            if ($('#targetType').val() === 'selected_groups') {
                var selected = $('.group-chk:checked').length;
                if (selected === 0) {
                    e.preventDefault();
                    notify('error', 'Please select at least one WhatsApp group for your campaign.');
                    $('#searchGroupInput').focus();
                    return false;
                }
            }

            if (!antiBanReviewed) {
                e.preventDefault();
                pendingSubmit = true;
                $('#modalNoticeBox').removeClass('d-none');
                $('#modalMinDelay').val($('#inputMinDelay').val());
                $('#modalMaxDelay').val($('#inputMaxDelay').val());
                $('#modalDelayAfterCount').val($('#inputDelayAfterCount').val());
                $('#modalDelayAfterDuration').val($('#inputDelayAfterDuration').val());
                $('#modalResetAfterCount').val($('#inputResetAfterCount').val());

                var modal = new bootstrap.Modal(document.getElementById('antiBanSettingsModal'));
                modal.show();
            }
        });

    })(jQuery);
</script>
@endpush
