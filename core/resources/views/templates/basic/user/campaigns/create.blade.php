@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Run WhatsApp Campaign</h4>
                <p class="text-muted mb-0">Select your sender account, audience, message template, and anti-ban delay timing.</p>
            </div>
            <div>
                <a href="{{ route('user.campaigns.index') }}" class="btn btn-outline-secondary">
                    <i class="las la-arrow-left me-1"></i> Back to Campaigns
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card custom--card border shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold"><i class="las la-bullhorn text--base me-1"></i> Campaign Composer</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('user.campaigns.store') }}" method="POST">
                            @csrf
                            <div class="row gy-3">
                                
                                <div class="col-md-7">
                                    <label class="fw-bold mb-1">Campaign Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Weekend Flash Sale / Product Launch" required>
                                </div>

                                <div class="col-md-5">
                                    <label class="fw-bold mb-1">Sender WhatsApp Account <span class="text-danger">*</span></label>
                                    <select name="session_id" class="form-select" required>
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

                                <div class="col-md-6">
                                    <label class="fw-bold mb-1">Target Audience <span class="text-danger">*</span></label>
                                    <select name="target_type" id="targetType" class="form-select" required>
                                        <option value="all">All Contacts & Groups ({{ $totalAll ?? ($totalContacts + ($totalGroups ?? 0)) }} total)</option>
                                        @if(($totalContacts ?? 0) > 0)
                                            <option value="contacts">Individual Contacts Only ({{ $totalContacts }} contacts)</option>
                                        @endif
                                        @if(($totalGroups ?? 0) > 0)
                                            <option value="groups">WhatsApp Groups Only ({{ $totalGroups }} groups)</option>
                                        @endif
                                        @if(isset($contactLists) && $contactLists->count() > 0)
                                            <optgroup label="My Contact Lists">
                                                @foreach($contactLists as $list)
                                                    <option value="list_{{ $list->id }}">📁 {{ $list->name }} ({{ $list->contacts_count }} {{ $list->type === 'groups' ? 'Groups' : 'Contacts' }})</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="fw-bold mb-1">Load Saved Message Template (Optional)</label>
                                    <select id="templateSelect" class="form-select">
                                        <option value="">-- Write Custom Message Below --</option>
                                        @foreach($templates as $t)
                                            <option value="{{ $t->id }}" data-message="{{ $t->message }}" data-media="{{ $t->media_url }}" data-type="{{ $t->type }}">{{ $t->name }} ({{ $t->type }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="fw-bold mb-1">Broadcast Message <span class="text-danger">*</span></label>
                                    <textarea name="message" id="campaignMessage" rows="5" class="form-control" placeholder="Write your message here... Use tags like @name and @phone for automatic customer personalization." required></textarea>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <span class="badge bg-light text-dark border cursor-pointer" onclick="insertTag('@name')"><code>@{{name}}</code></span>
                                        <span class="badge bg-light text-dark border cursor-pointer" onclick="insertTag('@phone')"><code>@{{phone}}</code></span>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <label class="fw-bold mb-1">Media Attachment URL (Optional)</label>
                                    <input type="url" name="media_url" id="mediaUrl" class="form-control" placeholder="https://example.com/banner.jpg">
                                </div>

                                <div class="col-md-4">
                                    <label class="fw-bold mb-1">Media Type</label>
                                    <select name="media_type" id="mediaType" class="form-select">
                                        <option value="text">Text Only</option>
                                        <option value="image">Image (JPG/PNG)</option>
                                        <option value="video">Video (MP4)</option>
                                        <option value="document">Document (PDF/DOC)</option>
                                    </select>
                                </div>

                                <!-- Anti-Ban Human Behavior Timing -->
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded border">
                                        <h6 class="fw-bold text-dark mb-2"><i class="las la-shield-alt text-success me-1"></i> Anti-Ban Human Delay Settings</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="small fw-bold mb-1">Min Delay Between Messages (Seconds)</label>
                                                <input type="number" name="min_delay_seconds" class="form-control form-control-sm" min="1" max="60" value="{{ $botSettings->min_delay_seconds ?? 5 }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small fw-bold mb-1">Max Delay Between Messages (Seconds)</label>
                                                <input type="number" name="max_delay_seconds" class="form-control form-control-sm" min="1" max="120" value="{{ $botSettings->max_delay_seconds ?? 15 }}">
                                            </div>
                                        </div>
                                 <!-- Broadcast Execution Mode -->
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded border">
                                        <h6 class="fw-bold text-dark mb-2"><i class="las la-rocket text-primary me-1"></i> Broadcast Execution Mode</h6>
                                        <select name="dispatch_mode" class="form-select" required>
                                            <option value="auto" selected>⚡ Automatic Background Broadcast (Server runs continuously without keeping browser open)</option>
                                            <option value="manual">🖥 Interactive In-Browser Broadcast (Real-time live progress on screen)</option>
                                        </select>
                                        <small class="text-muted d-block mt-1">In Automatic Background Mode, the campaign dispatches continuously on the server until completed or paused by you.</small>
                                    </div>
                                </div>

                                <!-- Auto-Restart Loop Option -->
                                <div class="col-12 mt-3">
                                    <div class="form-check form-switch p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                                        <div>
                                            <label class="form-check-label fw-bold text-dark mb-0" for="autoRestartCheck" style="cursor: pointer;">
                                                <i class="las la-sync text-success me-1"></i> Auto-Restart Broadcast Loop
                                            </label>
                                            <small class="text-muted d-block mt-1">When all recipients receive the message, automatically start broadcasting again from the beginning non-stop until you pause it.</small>
                                        </div>
                                        <input class="form-check-input ms-3" type="checkbox" name="auto_restart" id="autoRestartCheck" value="1" checked style="width: 2.5em; height: 1.3em; cursor: pointer;">
                                    </div>
                                </div>

                                <div class="col-12 text-end mt-4">
                                    <button type="submit" class="btn btn--base px-4 py-2" {{ $connectedAccounts->isEmpty() ? 'disabled' : '' }}>
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
@endsection

@push('script')
<script>
    function insertTag(tag) {
        var el = document.getElementById('campaignMessage');
        el.value += ' ' + tag;
    }

    (function ($) {
        "use strict";

        $('#templateSelect').on('change', function () {
            var $opt = $(this).find(':selected');
            if ($opt.val()) {
                $('#campaignMessage').val($opt.data('message'));
                $('#mediaUrl').val($opt.data('media') || '');
                $('#mediaType').val($opt.data('type') || 'text');
            }
        });

    })(jQuery);
</script>
@endpush
