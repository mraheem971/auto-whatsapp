@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold"><i class="las la-user-shield text-success me-1"></i> Anti-Ban & Human Behavior Settings</h4>
                <p class="text-muted mb-0">Configure natural delay timing, simulated typing indicators, and rate limits to keep your WhatsApp account protected.</p>
            </div>
            <div>
                <a href="{{ route('user.home') }}" class="btn btn-outline-secondary">
                    <i class="las la-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card custom--card border shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold"><i class="las la-sliders-h text--base me-1"></i> Human Emulation & Rate Limiting</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('user.settings.behavior.update') }}" method="POST">
                            @csrf
                            
                            <!-- Delay Settings -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-2">Random Message Interval (Delay)</h6>
                                <p class="text-muted small mb-3">Messages will be sent with a random randomized interval between Min and Max seconds to avoid WhatsApp spam filters.</p>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="fw-bold small mb-1">Minimum Delay (Seconds) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="min_delay_seconds" class="form-control" min="1" max="120" value="{{ $settings->min_delay_seconds }}" required>
                                            <span class="input-group-text bg-light">sec</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-bold small mb-1">Maximum Delay (Seconds) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="max_delay_seconds" class="form-control" min="1" max="300" value="{{ $settings->max_delay_seconds }}" required>
                                            <span class="input-group-text bg-light">sec</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Typing Indicator Simulation -->
                            <div class="mb-4">
                                <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="typing_simulation">
                                            <i class="las la-keyboard text-primary me-1"></i> Simulate "Typing..." Indicator
                                        </label>
                                        <p class="text-muted small mb-0">Show real-time typing status in WhatsApp chat before sending the reply or campaign message.</p>
                                    </div>
                                    <input class="form-check-input ms-3" type="checkbox" role="switch" name="typing_simulation" id="typing_simulation" value="1" {{ $settings->typing_simulation ? 'checked' : '' }}>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="fw-bold small mb-1">Typing Duration (Seconds)</label>
                                        <div class="input-group">
                                            <input type="number" name="typing_duration_seconds" class="form-control" min="1" max="30" value="{{ $settings->typing_duration_seconds }}" required>
                                            <span class="input-group-text bg-light">sec</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Presence Updates -->
                            <div class="mb-4">
                                <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center mb-0">
                                    <div>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="random_presence_update">
                                            <i class="las la-wifi text-success me-1"></i> Natural Online Presence Heartbeat
                                        </label>
                                        <p class="text-muted small mb-0">Maintains realistic online/available presence status on WhatsApp.</p>
                                    </div>
                                    <input class="form-check-input ms-3" type="checkbox" role="switch" name="random_presence_update" id="random_presence_update" value="1" {{ $settings->random_presence_update ? 'checked' : '' }}>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Rate Limiting & Sleep Mode -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-2">Safety Limits & Quiet Hours</h6>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="fw-bold small mb-1">Daily Message Send Limit</label>
                                        <input type="number" name="daily_send_limit" class="form-control" min="10" max="50000" value="{{ $settings->daily_send_limit }}" required>
                                        <small class="text-muted">Maximum outgoing messages dispatched per 24 hours.</small>
                                    </div>
                                </div>

                                <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="sleep_mode">
                                            <i class="las la-moon text-warning me-1"></i> Sleep Mode (Quiet Hours)
                                        </label>
                                        <p class="text-muted small mb-0">Pause campaign broadcasts during nighttime hours.</p>
                                    </div>
                                    <input class="form-check-input ms-3" type="checkbox" role="switch" name="sleep_mode" id="sleep_mode" value="1" {{ $settings->sleep_mode ? 'checked' : '' }}>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="fw-bold small mb-1">Sleep Start Time</label>
                                        <input type="time" name="sleep_start_time" class="form-control" value="{{ $settings->sleep_start_time }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-bold small mb-1">Sleep End Time</label>
                                        <input type="time" name="sleep_end_time" class="form-control" value="{{ $settings->sleep_end_time }}">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn--base px-4 py-2">
                                    <i class="las la-save me-1"></i> Save Anti-Ban Preferences
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
