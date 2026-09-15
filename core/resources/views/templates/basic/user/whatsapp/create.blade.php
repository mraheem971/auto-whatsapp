@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Link New WhatsApp Account</h4>
                <p class="text-muted mb-0">Connect using QR Code scan or direct 8-digit Phone Pairing Code.</p>
            </div>
            <div>
                <a href="{{ route('user.whatsapp.index') }}" class="btn btn-outline-secondary">
                    <i class="las la-arrow-left me-1"></i> Back to Accounts
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card custom--card border shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold"><i class="lab la-whatsapp text-success me-1"></i> Connection Setup</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Step 1: Account Details Form -->
                        <div id="setupFormSection">
                            <form id="initWhatsAppForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="fw-bold mb-1">Account Label / Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_name" class="form-control" placeholder="e.g. My Business WhatsApp / Support Line" required>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold mb-2">Connection Method <span class="text-danger">*</span></label>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="connection-method-card p-3 rounded-3 d-flex align-items-center gap-3 cursor-pointer w-100 mb-0 position-relative" for="methodQR" style="cursor: pointer;">
                                                <input class="form-check-input mt-0 flex-shrink-0" type="radio" name="pairing_method" id="methodQR" value="qr" checked>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-dark d-flex align-items-center">
                                                        <i class="las la-qrcode text-primary fs-4 me-2"></i> Scan QR Code
                                                    </div>
                                                    <small class="text-muted d-block mt-1">Instant linking via camera scan</small>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="connection-method-card p-3 rounded-3 d-flex align-items-center gap-3 cursor-pointer w-100 mb-0 position-relative" for="methodCode" style="cursor: pointer;">
                                                <input class="form-check-input mt-0 flex-shrink-0" type="radio" name="pairing_method" id="methodCode" value="code">
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-dark d-flex align-items-center">
                                                        <i class="las la-key text-success fs-4 me-2"></i> 8-Digit Pairing Code
                                                    </div>
                                                    <small class="text-muted d-block mt-1">Connect using phone number</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4 d-none" id="phoneNumberWrapper">
                                    <label class="fw-bold mb-1">WhatsApp Phone Number (with Country Code) <span class="text-danger">*</span></label>
                                    <input type="text" name="phone_number" id="phoneNumberInput" class="form-control" placeholder="e.g. 923216793596 (no dashes or spaces)">
                                    <small class="text-muted">Enter the phone number registered with your WhatsApp.</small>
                                </div>

                                <button type="submit" class="btn btn--base w-100 py-2" id="btnInitSession">
                                    <i class="las la-link me-1"></i> Generate Connection Code
                                </button>
                            </form>
                        </div>

                        <!-- Step 2: Live QR / Pairing Code Display -->
                        <div id="connectionDisplaySection" class="d-none text-center py-4">
                            
                            <!-- QR Display -->
                            <div id="qrContainer" class="d-none">
                                <h5 class="fw-bold mb-2">Scan this QR Code with WhatsApp</h5>
                                <p class="text-muted small mb-3">Open WhatsApp on your phone &rarr; Linked Devices &rarr; Link a Device &rarr; Scan this code.</p>
                                <div class="p-3 bg-white border d-inline-block rounded shadow-sm mb-3">
                                    <img id="qrImage" src="" alt="WhatsApp QR Code" style="max-width: 280px; width: 100%;">
                                </div>
                            </div>

                            <!-- Pairing Code Display -->
                            <div id="pairingCodeContainer" class="d-none">
                                <h5 class="fw-bold mb-2">Enter Pairing Code in WhatsApp</h5>
                                <p class="text-muted small mb-3">WhatsApp sent a notification or open: Linked Devices &rarr; Link with phone number instead.</p>
                                <div class="p-3 bg-light border rounded d-inline-block mb-3">
                                    <span class="fs-1 fw-bold font-monospace text-primary tracking-widest" id="pairingCodeText">------</span>
                                </div>
                            </div>

                            <!-- Live Polling Spinner -->
                            <div class="d-flex align-items-center justify-content-center gap-2 text-muted small mt-2">
                                <div class="spinner-border spinner-border-sm text--base" role="status"></div>
                                <span id="connectionStatusText">Waiting for WhatsApp authorization on your phone...</span>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCancelSession">
                                    <i class="las la-times me-1"></i> Cancel & Restart
                                </button>
                            </div>

                        </div>

                        <!-- Step 3: Success Screen -->
                        <div id="successDisplaySection" class="d-none text-center py-5">
                            <div class="avatar avatar--xl bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="las la-check fs-1"></i>
                            </div>
                            <h4 class="fw-bold text-success mb-1">WhatsApp Connected Successfully!</h4>
                            <p class="text-muted mb-4" id="successAccountDetails">Your account is now online and ready to send & receive messages.</p>
                            <a href="{{ route('user.whatsapp.index') }}" class="btn btn--base px-4">
                                View Connected Accounts
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('script')
<script>
    (function ($) {
        "use strict";

        var pollInterval = null;
        var currentSessionId = null;

        $('input[name="pairing_method"]').on('change', function () {
            if ($(this).val() === 'code') {
                $('#phoneNumberWrapper').removeClass('d-none');
                $('#phoneNumberInput').attr('required', true);
            } else {
                $('#phoneNumberWrapper').addClass('d-none');
                $('#phoneNumberInput').removeAttr('required');
            }
        });

        $('#initWhatsAppForm').on('submit', function (e) {
            e.preventDefault();
            var $btn = $('#btnInitSession');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Connecting to WhatsApp...');

            $.ajax({
                url: "{{ route('user.whatsapp.init.session') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    $btn.prop('disabled', false).html(originalText);
                    if (res.status === 'success') {
                        currentSessionId = res.sessionId;
                        $('#setupFormSection').addClass('d-none');
                        $('#connectionDisplaySection').removeClass('d-none');

                        if (res.pairingMethod === 'code' && res.pairingCode) {
                            $('#pairingCodeContainer').removeClass('d-none');
                            $('#qrContainer').addClass('d-none');
                            $('#pairingCodeText').text(res.pairingCode);
                        } else if (res.qrImage) {
                            $('#qrContainer').removeClass('d-none');
                            $('#pairingCodeContainer').addClass('d-none');
                            $('#qrImage').attr('src', res.qrImage);
                        }

                        startPolling(currentSessionId);
                    } else {
                        notify('error', res.error || 'Failed to initialize session.');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    var msg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Error initializing session.';
                    notify('error', msg);
                }
            });
        });

        function startPolling(sessionId) {
            if (pollInterval) clearInterval(pollInterval);

            pollInterval = setInterval(function () {
                $.ajax({
                    url: "{{ url('user/whatsapp/session-status') }}/" + sessionId,
                    type: "GET",
                    success: function (res) {
                        if (res.status === 'connected') {
                            clearInterval(pollInterval);
                            $('#connectionDisplaySection').addClass('d-none');
                            $('#successDisplaySection').removeClass('d-none');
                            if (res.user) {
                                $('#successAccountDetails').text('Connected as +' + (res.user.phone || '') + ' (' + (res.user.name || '') + ')');
                            }
                            notify('success', 'WhatsApp account connected successfully!');
                        } else if (res.status === 'qr_ready' && res.qrImage) {
                            $('#qrImage').attr('src', res.qrImage);
                        }
                    }
                });
            }, 3000);
        }

        $('#btnCancelSession').on('click', function () {
            if (pollInterval) clearInterval(pollInterval);
            $('#connectionDisplaySection').addClass('d-none');
            $('#setupFormSection').removeClass('d-none');
        });

    })(jQuery);
</script>
@endpush
