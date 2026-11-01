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
                                    <label class="fw-bold mb-1">WhatsApp Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted font-monospace"><i class="las la-globe me-1 text-primary"></i> +</span>
                                        <input type="text" name="phone_number" id="phoneNumberInput" class="form-control form-control-lg font-monospace" placeholder="e.g. 923216793596 or 03216793596" autocomplete="tel">
                                    </div>
                                    <div id="phoneHelperText" class="mt-2 small text-muted">
                                        <i class="las la-info-circle text-primary"></i> Enter your WhatsApp number with country code (e.g. <code>923216793596</code> or local <code>03216793596</code>).
                                    </div>
                                    <div id="phoneLiveBadge" class="mt-2 d-none">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                                            <i class="lab la-whatsapp me-1"></i> Linking with: <strong id="phoneFormattedPreview">+92 321 6793596</strong>
                                        </span>
                                    </div>
                                    <div id="phoneInlineError" class="mt-2 text-danger small d-none">
                                        <i class="las la-exclamation-circle me-1"></i> <span id="phoneInlineErrorText"></span>
                                    </div>
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
                                <div class="card border-0 bg-light p-3 p-md-4 mb-3 rounded-4 shadow-sm mx-auto" style="max-width: 520px;">
                                    <h5 class="fw-bold mb-1 text-dark">Enter Pairing Code in WhatsApp</h5>
                                    <p class="text-muted small mb-3">A notification may have arrived on your phone, or link manually using the code below:</p>
                                    
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                                        <div class="px-4 py-2 bg-white border border-2 border-primary rounded-3 shadow-sm">
                                            <span class="fs-1 fw-bold font-monospace text-primary tracking-widest" id="pairingCodeText">
                                                <span class="spinner-border spinner-border-sm text-primary me-2"></span>Generating...
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary px-3 py-2 d-flex align-items-center gap-1" id="btnCopyPairingCode" title="Copy Pairing Code" style="height: 58px;">
                                            <i class="las la-copy fs-4"></i> <span id="copyBtnText" class="fw-bold">Copy</span>
                                        </button>
                                    </div>

                                    <div class="text-start bg-white p-3 rounded-3 border small">
                                        <h6 class="fw-bold mb-2 text-dark"><i class="las la-mobile-alt text-primary me-1"></i> How to link on your phone:</h6>
                                        <ol class="mb-0 ps-3 text-muted">
                                            <li class="mb-1">Open <strong>WhatsApp</strong> on your phone</li>
                                            <li class="mb-1">Go to <strong>Settings</strong> &rarr; <strong>Linked Devices</strong> &rarr; <strong>Link a Device</strong></li>
                                            <li class="mb-1">Tap <strong>"Link with phone number instead"</strong> at the bottom</li>
                                            <li>Enter the 8-character pairing code shown above</li>
                                        </ol>
                                    </div>
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
        var activeMethod = 'qr';

        function normalizePhone(val) {
            if (!val) return '';
            var clean = val.toString().replace(/[^0-9]/g, '');
            if (clean.startsWith('00')) clean = clean.substring(2);
            // Auto-convert Pakistan domestic 03XXXXXXXXX (11 digits) -> 923XXXXXXXXX
            if (/^03[0-9]{9}$/.test(clean)) {
                clean = '92' + clean.substring(1);
            } else if (/^3[0-9]{9}$/.test(clean)) {
                clean = '92' + clean;
            } else {
                clean = clean.replace(/^0+/, '');
            }
            return clean;
        }

        function formatPhonePreview(clean) {
            if (!clean || clean.length < 9) return '';
            if (clean.startsWith('92') && clean.length === 12) {
                return '+92 ' + clean.substring(2, 5) + ' ' + clean.substring(5);
            }
            if (clean.startsWith('1') && clean.length === 11) {
                return '+1 (' + clean.substring(1, 4) + ') ' + clean.substring(4, 7) + '-' + clean.substring(7);
            }
            if (clean.length > 3) {
                return '+' + clean.substring(0, clean.length - 7) + ' ' + clean.substring(clean.length - 7, clean.length - 4) + ' ' + clean.substring(clean.length - 4);
            }
            return '+' + clean;
        }

        function updatePhonePreview() {
            var raw = ($('#phoneNumberInput').val() || '').trim();
            if (window.iziToast) iziToast.destroy();
            $('#phoneInlineError').addClass('d-none');

            var clean = normalizePhone(raw);
            if (clean.length >= 9) {
                var formatted = formatPhonePreview(clean);
                $('#phoneFormattedPreview').text(formatted);
                $('#phoneLiveBadge').removeClass('d-none');
            } else {
                $('#phoneLiveBadge').addClass('d-none');
            }
        }

        $('#phoneNumberInput').on('input propertychange change paste keyup', function () {
            updatePhonePreview();
        });

        $('input[name="pairing_method"]').on('change', function () {
            if ($(this).val() === 'code') {
                $('#phoneNumberWrapper').removeClass('d-none');
                $('#phoneNumberInput').attr('required', true).focus();
                updatePhonePreview();
            } else {
                $('#phoneNumberWrapper').addClass('d-none');
                $('#phoneNumberInput').removeAttr('required');
                $('#phoneInlineError').addClass('d-none');
                $('#phoneLiveBadge').addClass('d-none');
            }
        });

        $(document).on('click', '#btnCopyPairingCode', function () {
            var rawText = $('#pairingCodeText').text().trim().replace(/[^a-zA-Z0-9]/g, '');
            if (rawText && rawText.length === 8) {
                var codeText = $('#pairingCodeText').text().trim();
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(codeText).then(function () {
                        $('#copyBtnText').text('Copied!');
                        setTimeout(function () { $('#copyBtnText').text('Copy'); }, 2000);
                    });
                } else {
                    var temp = $('<input>');
                    $('body').append(temp);
                    temp.val(codeText).select();
                    document.execCommand('copy');
                    temp.remove();
                    $('#copyBtnText').text('Copied!');
                    setTimeout(function () { $('#copyBtnText').text('Copy'); }, 2000);
                }
            }
        });

        $('#initWhatsAppForm').on('submit', function (e) {
            e.preventDefault();
            if (window.iziToast) iziToast.destroy();

            var $btn = $('#btnInitSession');
            var originalText = $btn.html();

            activeMethod = $('input[name="pairing_method"]:checked').val() || 'qr';

            if (activeMethod === 'code') {
                var rawInput = ($('#phoneNumberInput').val() || '').trim();
                var clean = normalizePhone(rawInput);

                if (!clean || clean.length < 9) {
                    $('#phoneInlineError').removeClass('d-none');
                    $('#phoneInlineErrorText').text('Please enter a valid phone number with country code (e.g. 923216793596 or local 03216793596).');
                    $('#phoneNumberInput').focus();
                    return false;
                }

                // Place the clean normalized number into input so it is submitted cleanly
                $('#phoneNumberInput').val(clean);
                $('#phoneInlineError').addClass('d-none');
            }

            $btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Connecting to WhatsApp...');

            $.ajax({
                url: "{{ route('user.whatsapp.init.session') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    $btn.prop('disabled', false).html(originalText);
                    if (res.status === 'success') {
                        currentSessionId = res.sessionId;
                        activeMethod = res.pairingMethod || activeMethod;

                        $('#setupFormSection').addClass('d-none');
                        $('#connectionDisplaySection').removeClass('d-none');

                        if (activeMethod === 'code') {
                            $('#qrContainer').addClass('d-none');
                            $('#pairingCodeContainer').removeClass('d-none');
                            if (res.pairingCode) {
                                $('#pairingCodeText').text(res.pairingCode);
                                $('#connectionStatusText').text('Enter this code in WhatsApp on your phone...');
                            } else {
                                $('#pairingCodeText').html('<span class="spinner-border spinner-border-sm text-primary me-2"></span>Generating...');
                                $('#connectionStatusText').text('Requesting 8-digit pairing code from WhatsApp...');
                            }
                        } else {
                            $('#pairingCodeContainer').addClass('d-none');
                            $('#qrContainer').removeClass('d-none');
                            if (res.qrImage) {
                                $('#qrImage').attr('src', res.qrImage);
                            }
                            $('#connectionStatusText').text('Waiting for QR code scan on your phone...');
                        }

                        startPolling(currentSessionId, activeMethod);
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

        function startPolling(sessionId, method) {
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
                            return;
                        }

                        if (method === 'code') {
                            $('#qrContainer').addClass('d-none');
                            $('#pairingCodeContainer').removeClass('d-none');

                            if (res.pairingCode) {
                                $('#pairingCodeText').text(res.pairingCode);
                                $('#connectionStatusText').text('Enter code on WhatsApp: Linked Devices > Link with phone number');
                            } else if (res.status === 'pairing_error') {
                                $('#connectionStatusText').html('<span class="text-danger fw-bold"><i class="las la-exclamation-triangle me-1"></i>' + (res.pairingError || 'Pairing error. Verify your phone number.') + '</span>');
                            } else {
                                $('#connectionStatusText').text('Generating 8-digit pairing code...');
                            }
                        } else {
                            $('#pairingCodeContainer').addClass('d-none');
                            $('#qrContainer').removeClass('d-none');

                            if (res.status === 'qr_ready' && res.qrImage) {
                                $('#qrImage').attr('src', res.qrImage);
                                $('#connectionStatusText').text('Scan the QR code with WhatsApp on your phone...');
                            }
                        }
                    }
                });
            }, 2000);
        }

        $('#btnCancelSession').on('click', function () {
            if (pollInterval) clearInterval(pollInterval);
            $('#connectionDisplaySection').addClass('d-none');
            $('#setupFormSection').removeClass('d-none');
        });

    })(jQuery);
</script>
@endpush
