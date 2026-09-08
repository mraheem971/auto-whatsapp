@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <!-- Left Column: Connection Setup Form -->
    <div class="col-xl-5 col-lg-6">
        <div class="card b-radius--10 shadow-sm mb-4">
            <div class="card-header bg--primary text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="lab la-whatsapp me-2 fs-4"></i> @lang('Connect WhatsApp Account')
                </h5>
                <a href="{{ route('admin.account.listing.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="las la-list me-1"></i> @lang('All Accounts')
                </a>
            </div>
            <div class="card-body p-4">
                
                <!-- Pairing Method Toggle Tabs -->
                <div class="mb-4">
                    <label class="fw-bold small text-dark mb-2 d-block">
                        <i class="las la-link text--primary me-1"></i> @lang('Choose Connection Method'):
                    </label>
                    <div class="btn-group w-100 p-1 bg-light rounded border" role="group">
                        <input type="radio" class="btn-check" name="pairing_method_radio" id="method_code" value="code" checked autocomplete="off">
                        <label class="btn btn-sm btn-outline-primary fw-bold py-2 border-0 rounded" for="method_code">
                            <i class="las la-mobile-alt me-1 fs-5"></i> @lang('Phone Number (Code)')
                        </label>

                        <input type="radio" class="btn-check" name="pairing_method_radio" id="method_qr" value="qr" autocomplete="off">
                        <label class="btn btn-sm btn-outline-primary fw-bold py-2 border-0 rounded" for="method_qr">
                            <i class="las la-qrcode me-1 fs-5"></i> @lang('Scan QR Code')
                        </label>
                    </div>
                </div>

                <form id="connectAccountForm">
                    @csrf
                    <input type="hidden" name="pairing_method" id="pairing_method" value="code">

                    <!-- Account Name -->
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-2 small text-dark">
                            @lang('Account Label / Name') <span class="text--danger">*</span>
                        </label>
                        <input type="text" name="account_name" id="account_name" class="form-control form-control-lg" placeholder="@lang('e.g. My Primary WhatsApp')" required>
                        <span class="text-muted d-block mt-1 text-xs">
                            <i class="las la-info-circle me-1"></i> @lang('A recognizable label for this WhatsApp phone / SIM')
                        </span>
                    </div>

                    <!-- Phone Number Input (Active when Phone Code method selected) -->
                    <div class="form-group mb-4" id="phoneInputGroup">
                        <label class="fw-bold mb-2 small text-dark">
                            <i class="las la-phone text--success me-1"></i> @lang('WhatsApp Phone Number') <span class="text--danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted font-monospace"><i class="las la-globe"></i> +</span>
                            <input type="text" name="phone_number" id="phone_number" class="form-control form-control-lg font-monospace" placeholder="@lang('e.g. 923216793596 or 14155552671')">
                        </div>
                        <span class="text-muted d-block mt-1 text-xs">
                            <i class="las la-info-circle me-1"></i> @lang('Include country code without + or spaces (e.g. 92 for PK, 1 for US, 91 for IN).')
                        </span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn--primary w-100 py-2 fs-6 fw-bold shadow-sm" id="btnConnectSubmit" style="height: 48px;">
                        <i class="las la-key me-2 fs-5" id="submitIcon"></i> 
                        <span id="submitBtnText">@lang('Get 8-Digit Pairing Code')</span>
                    </button>
                </form>

                <!-- Security Box -->
                <div class="p-3 bg--light rounded border mt-4">
                    <div class="d-flex align-items-start">
                        <div class="me-3 text--primary fs-4">
                            <i class="las la-shield-alt"></i>
                        </div>
                        <div>
                            <strong class="d-block text--dark mb-1">@lang('End-to-End Encryption')</strong>
                            <p class="text-muted small mb-0 lh-base text-xs">
                                @lang('Your WhatsApp authentication credentials are encrypted and stored locally via Baileys multi-device engine. Messages flow directly through WhatsApp official multi-device servers.')
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Test Send Message Card -->
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--dark text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-paper-plane me-2 fs-4 text--success"></i> @lang('Quick Test Message')
                </h5>
                <span class="badge badge--success" id="testSenderStatus">
                    {{ $connectedAccounts->count() > 0 ? trans('Ready') : trans('Connect an Account') }}
                </span>
            </div>
            <div class="card-body p-4">
                <form id="testMessageForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1 small text-dark">@lang('Sending From Account') <span class="text--danger">*</span></label>
                        <select name="test_session_id" id="test_session_id" class="form-control form-select form-select-sm" required>
                            @forelse($connectedAccounts as $acc)
                                <option value="{{ $acc->session_id }}" data-phone="{{ $acc->phone_number }}">
                                    📱 {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Connected' }})
                                </option>
                            @empty
                                <option value="" disabled selected>@lang('No account connected yet. Pair phone first.')</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1 small text-dark">@lang('Recipient WhatsApp Number') <span class="text--danger">*</span></label>
                        <input type="text" name="receiver_number" id="receiver_number" class="form-control form-control-sm" placeholder="@lang('e.g. 923001234567')" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1 small text-dark">@lang('Message Text') <span class="text--danger">*</span></label>
                        <textarea name="test_message" id="test_message" rows="2" class="form-control form-control-sm" placeholder="@lang('Type test message...')" required>🔥 Hello! This is a test message sent from Auto-WhatsApp.</textarea>
                    </div>

                    <button type="submit" class="btn btn--success w-100 py-2 fs-6 fw-bold" id="btnSendTestMessage">
                        <i class="las la-paper-plane me-1"></i> @lang('Send Test Message')
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Live Pairing Screen & Instructions -->
    <div class="col-xl-7 col-lg-6">
        <div class="card b-radius--10 shadow-sm h-100">
            <div class="card-header bg--dark text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="lab la-whatsapp me-2 fs-4 text--success"></i> 
                    <span id="screenHeaderTitle">@lang('WhatsApp Mobile Pairing')</span>
                </h5>
                <span class="badge badge--warning px-3 py-2" id="connectionBadge">@lang('Not Initialized')</span>
            </div>
            
            <div class="card-body d-flex flex-column align-items-center justify-content-between p-4">
                
                <div class="w-100 d-flex flex-column align-items-center justify-content-center my-auto py-3">
                    
                    <!-- State 1: Initial Placeholder -->
                    <div id="initialPlaceholder" class="text-center py-4">
                        <div class="mb-3">
                            <span class="avatar avatar--xl bg--success-transparent text--success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 90px; height: 90px;">
                                <i class="lab la-whatsapp" style="font-size: 55px;"></i>
                            </span>
                        </div>
                        <h4 class="text-dark fw-bold mb-2" id="placeholderTitle">@lang('Connect WhatsApp in Seconds')</h4>
                        <p class="text-muted small mb-0 max-w-400 mx-auto" id="placeholderDesc">
                            @lang('Choose whether you want to connect by entering your phone number to receive an 8-digit Pairing Code, or by scanning a QR Code.')
                        </p>
                    </div>

                    <!-- State 2: Loading Spinner -->
                    <div id="loadingState" class="text-center py-4 d-none">
                        <div class="spinner-border text--primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="text-dark fw-bold mb-1" id="loadingTitle">@lang('Connecting to WhatsApp Servers...')</h5>
                        <p class="text-muted small mb-0" id="loadingSubtitle">@lang('Requesting authentication token and generating secure keys.')</p>
                    </div>

                    <!-- State 3: Direct Phone Number 8-Digit Pairing Code Display -->
                    <div id="pairingCodeContainer" class="text-center d-none w-100 py-3">
                        <div class="alert alert-info py-2 px-3 d-inline-block small mb-3">
                            <i class="las la-bell me-1"></i> @lang('Enter this code on your phone when prompted in WhatsApp'):
                        </div>

                        <!-- 8-Digit Code Large Visual Badge -->
                        <div class="p-4 bg-light border-2 border-primary border rounded-3 shadow-sm mb-3 mx-auto" style="max-width: 380px;">
                            <small class="text-muted d-block text-uppercase fw-bold mb-1 letter-spacing-1">@lang('Your WhatsApp Pairing Code')</small>
                            <div class="d-flex align-items-center justify-content-center gap-2 my-2">
                                <span class="display-5 font-monospace fw-bold text--primary text-tracking-widest" id="displayPairingCode">----</span>
                            </div>
                            <button type="button" class="btn btn-sm btn--primary px-4 mt-2 fw-bold" id="btnCopyPairingCode">
                                <i class="las la-copy me-1"></i> @lang('Copy Code')
                            </button>
                        </div>

                        <div class="text-center mb-2">
                            <span class="badge badge--info fs-6 px-3 py-2" id="codeStatusMessage">
                                <i class="fas fa-spinner fa-spin me-1"></i> @lang('Waiting for approval on your mobile phone...')
                            </span>
                        </div>
                    </div>

                    <!-- State 4: QR Code Display Container -->
                    <div id="qrContainer" class="text-center d-none">
                        <div class="qr-box p-3 border rounded shadow-sm bg-white mb-3 d-inline-block position-relative">
                            <img id="qrImageElement" src="" alt="WhatsApp QR Code" class="img-fluid" style="max-width: 270px; min-height: 270px;">
                            <div id="qrOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center bg-white bg-opacity-75">
                                <i class="fas fa-spinner fa-spin text--primary mb-2" style="font-size: 35px;"></i>
                                <span class="fw-bold text--primary">@lang('Connecting...')</span>
                            </div>
                        </div>

                        <div class="text-center mb-2">
                            <span class="badge badge--info fs-6 px-3 py-2" id="qrStatusMessage">
                                <i class="fas fa-spinner fa-spin me-1"></i> @lang('Waiting for scan...')
                            </span>
                        </div>
                    </div>

                    <!-- State 5: Connected Success Container -->
                    <div id="connectionSuccess" class="text-center py-4 d-none w-100">
                        <div class="mb-3">
                            <span class="avatar avatar--xl bg--success text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 80px; height: 80px;">
                                <i class="las la-check-circle" style="font-size: 50px;"></i>
                            </span>
                        </div>
                        <h4 class="text--success mb-2 fw-bold">@lang('WhatsApp Connected Successfully!')</h4>
                        <p class="text-muted mb-4 fs-6" id="connectedDetails"></p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('admin.device.sender.index') }}" class="btn btn--primary px-4 py-2">
                                <i class="las la-paper-plane me-1"></i> @lang('Send Message from Phone')
                            </a>
                            <a href="{{ route('admin.account.listing.index') }}" class="btn btn-outline--success px-4 py-2">
                                <i class="las la-list me-1"></i> @lang('View All Accounts')
                            </a>
                        </div>
                    </div>

                </div>

                <!-- Step-by-Step Mobile Instructions Box -->
                <div class="w-100 border-top pt-3 mt-3">
                    
                    <!-- Instructions for Phone Code -->
                    <div id="instructionsPhoneCode">
                        <h6 class="fw-bold mb-3 d-flex align-items-center text-dark">
                            <i class="las la-mobile me-2 text--primary fs-5"></i> @lang('How to link using Phone Pairing Code'):
                        </h6>
                        <div class="d-flex flex-column gap-2 small text-muted">
                            <div class="d-flex align-items-center">
                                <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">1</span>
                                <span>@lang('Open') <strong>@lang('WhatsApp')</strong> @lang('on your Android / iPhone').</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">2</span>
                                <span>@lang('Tap') <strong>@lang('Menu (3 dots)')</strong> @lang('or Settings > ') <strong>@lang('Linked Devices')</strong>.</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">3</span>
                                <span>@lang('Tap') <strong>@lang('Link a Device')</strong>, @lang('then tap') <strong class="text--primary">@lang('"Link with phone number instead"')</strong> @lang('at bottom').</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">4</span>
                                <span>@lang('Enter the 8-digit Pairing Code shown on this screen.')</span>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions for QR Code -->
                    <div id="instructionsQR" class="d-none">
                        <h6 class="fw-bold mb-3 d-flex align-items-center text-dark">
                            <i class="las la-qrcode me-2 text--primary fs-5"></i> @lang('How to link using QR Code'):
                        </h6>
                        <div class="d-flex flex-column gap-2 small text-muted">
                            <div class="d-flex align-items-center">
                                <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">1</span>
                                <span>@lang('Open') <strong>@lang('WhatsApp')</strong> @lang('on your phone').</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">2</span>
                                <span>@lang('Tap') <strong>@lang('Menu (3 dots)')</strong> @lang('or Settings > ') <strong>@lang('Linked Devices')</strong>.</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">3</span>
                                <span>@lang('Tap on') <strong>@lang('Link a Device')</strong> @lang('and scan the QR code above').</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.account.listing.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-undo me-1"></i> @lang('Back to Accounts')
    </a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    let activeSessionId = null;
    let pollInterval = null;
    let currentMethod = 'code';

    // Toggle between Phone Code and QR Code methods
    $('input[name="pairing_method_radio"]').on('change', function(){
        currentMethod = $(this).val();
        $('#pairing_method').val(currentMethod);

        if(currentMethod === 'code') {
            $('#phoneInputGroup').removeClass('d-none');
            $('#phone_number').prop('required', true);
            $('#submitBtnText').text("@lang('Get 8-Digit Pairing Code')");
            $('#submitIcon').removeClass('la-qrcode').addClass('la-key');
            $('#instructionsPhoneCode').removeClass('d-none');
            $('#instructionsQR').addClass('d-none');
            $('#placeholderTitle').text("@lang('Connect with Phone Number')");
            $('#placeholderDesc').text("@lang('Enter your WhatsApp phone number to generate an 8-character pairing code to link your device.')");
        } else {
            $('#phoneInputGroup').addClass('d-none');
            $('#phone_number').prop('required', false);
            $('#submitBtnText').text("@lang('Generate QR Code')");
            $('#submitIcon').removeClass('la-key').addClass('la-qrcode');
            $('#instructionsPhoneCode').addClass('d-none');
            $('#instructionsQR').removeClass('d-none');
            $('#placeholderTitle').text("@lang('Connect with QR Code')");
            $('#placeholderDesc').text("@lang('Click \"Generate QR Code\" and point your phone camera at the QR code to link your device.')");
        }
    });

    // Form Submission: Initialize Session with Code or QR
    $('#connectAccountForm').on('submit', function(e){
        e.preventDefault();

        const accountName = $('#account_name').val().trim();
        const phoneNumber = $('#phone_number').val().trim();
        const method = $('#pairing_method').val();

        if(!accountName){
            notify('error', 'Please enter an account name');
            return;
        }

        if(method === 'code' && !phoneNumber){
            notify('error', 'Please enter your WhatsApp phone number with country code');
            return;
        }

        if(pollInterval) clearInterval(pollInterval);

        // UI Updates
        $('#initialPlaceholder').addClass('d-none');
        $('#connectionSuccess').addClass('d-none');
        $('#qrContainer').addClass('d-none');
        $('#pairingCodeContainer').addClass('d-none');
        $('#loadingState').removeClass('d-none');

        if(method === 'code'){
            $('#loadingTitle').text("@lang('Requesting 8-Digit Pairing Code...')");
            $('#loadingSubtitle').text(`@lang('Connecting to WhatsApp servers for phone +') ${phoneNumber}...`);
            $('#btnConnectSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Generating Code...');
        } else {
            $('#loadingTitle').text("@lang('Generating WhatsApp QR Code...')");
            $('#loadingSubtitle').text("@lang('Initializing Baileys multi-device session...')");
            $('#btnConnectSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Generating QR...');
        }

        $('#connectionBadge').removeClass('badge--success badge--danger').addClass('badge--warning').text('Initializing...');

        $.ajax({
            url: "{{ route('admin.account.listing.init.session') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                account_name: accountName,
                pairing_method: method,
                phone_number: phoneNumber
            },
            success: function(response){
                $('#loadingState').addClass('d-none');
                $('#btnConnectSubmit').prop('disabled', false);

                if(method === 'code'){
                    $('#btnConnectSubmit').html('<i class="las la-key me-2 fs-5"></i> @lang("Get 8-Digit Pairing Code")');
                } else {
                    $('#btnConnectSubmit').html('<i class="las la-qrcode me-2 fs-5"></i> @lang("Generate QR Code")');
                }

                activeSessionId = response.sessionId;

                if(response.status === 'connected'){
                    handleConnected(response, accountName);
                    return;
                }

                if(method === 'code'){
                    if(response.pairingCode){
                        showPairingCode(response.pairingCode);
                    } else {
                        $('#pairingCodeContainer').removeClass('d-none');
                        $('#displayPairingCode').html('<i class="fas fa-spinner fa-spin text-muted" style="font-size:32px;"></i>');
                        $('#codeStatusMessage').html('<i class="fas fa-spinner fa-spin me-1"></i> Generating code...');
                    }
                    startPolling(response.sessionId, accountName, 'code');
                } else {
                    if(response.qrImage){
                        $('#qrImageElement').attr('src', response.qrImage);
                        $('#qrContainer').removeClass('d-none');
                        $('#connectionBadge').text('Scan QR Code');
                        $('#qrStatusMessage').html('<i class="fas fa-spinner fa-spin me-1"></i> Waiting for scan...');
                    } else {
                        $('#qrContainer').removeClass('d-none');
                        $('#connectionBadge').text('Connecting...');
                    }
                    startPolling(response.sessionId, accountName, 'qr');
                }
            },
            error: function(xhr){
                $('#loadingState').addClass('d-none');
                $('#initialPlaceholder').removeClass('d-none');
                $('#btnConnectSubmit').prop('disabled', false);

                if(method === 'code'){
                    $('#btnConnectSubmit').html('<i class="las la-key me-2 fs-5"></i> @lang("Get 8-Digit Pairing Code")');
                } else {
                    $('#btnConnectSubmit').html('<i class="las la-qrcode me-2 fs-5"></i> @lang("Generate QR Code")');
                }

                $('#connectionBadge').removeClass('badge--warning').addClass('badge--danger').text('Error');

                let errMsg = 'Failed to initialize WhatsApp session.';
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

    function showPairingCode(code){
        $('#pairingCodeContainer').removeClass('d-none');
        $('#displayPairingCode').text(code);
        $('#connectionBadge').removeClass('badge--danger badge--warning').addClass('badge--primary').text('Enter Code on Phone');
        $('#codeStatusMessage').html('<i class="fas fa-spinner fa-spin me-1"></i> Open WhatsApp > Linked Devices > Link with Phone Number');
    }

    let lastQrSrc = '';

    function startPolling(sessionId, accountName, method){
        if(pollInterval) clearInterval(pollInterval);
        lastQrSrc = '';

        pollInterval = setInterval(function(){
            $.ajax({
                url: "{{ url('admin/account-listing/session-status') }}/" + sessionId,
                type: "GET",
                success: function(res){
                    if(res.status === 'connected'){
                        clearInterval(pollInterval);
                        handleConnected(res, accountName);
                        return;
                    }

                    if(method === 'code'){
                        if(res.pairingCode && $('#displayPairingCode').text() !== res.pairingCode){
                            showPairingCode(res.pairingCode);
                        } else if(res.pairingError){
                            $('#codeStatusMessage').html(`<i class="las la-exclamation-circle text-danger me-1"></i> ${res.pairingError}`);
                            $('#connectionBadge').removeClass('badge--primary').addClass('badge--danger').text('Pairing Error');
                        }
                    } else {
                        if(res.status === 'qr_ready' && res.qrImage){
                            if(lastQrSrc !== res.qrImage){
                                lastQrSrc = res.qrImage;
                                $('#qrImageElement').attr('src', res.qrImage);
                            }
                            $('#qrOverlay').addClass('d-none');
                            $('#connectionBadge').text('Scan QR Code');
                            $('#qrStatusMessage').html('<i class="fas fa-spinner fa-spin me-1"></i> Waiting for scan...');
                        } else if(res.status === 'connecting'){
                            $('#qrOverlay').removeClass('d-none');
                            $('#connectionBadge').text('Connecting...');
                            $('#qrStatusMessage').html('<i class="fas fa-spinner fa-spin me-1"></i> Connecting to WhatsApp...');
                        }
                    }

                    if(res.status === 'disconnected'){
                        $('#connectionBadge').removeClass('badge--warning badge--success').addClass('badge--danger').text('Disconnected');
                    }
                }
            });
        }, 2000);
    }

    function handleConnected(data, accountName){
        $('#qrContainer').addClass('d-none');
        $('#pairingCodeContainer').addClass('d-none');
        $('#loadingState').addClass('d-none');
        $('#initialPlaceholder').addClass('d-none');
        $('#connectionSuccess').removeClass('d-none');
        $('#connectionBadge').removeClass('badge--warning badge--danger').addClass('badge--success').text('Connected');

        const phone = data.user && data.user.phone ? '+' + data.user.phone : '';
        const name = data.user && data.user.name ? data.user.name : (accountName || 'WhatsApp Account');
        $('#connectedDetails').html(`<strong>Phone:</strong> ${phone} &nbsp;|&nbsp; <strong>Profile:</strong> ${name}`);

        // Update test message session select box
        if(data.sessionId){
            const newOption = `<option value="${data.sessionId}" selected>📱 ${name} (${phone})</option>`;
            $('#test_session_id').find('option[value=""]').remove();
            if($('#test_session_id').find(`option[value="${data.sessionId}"]`).length === 0){
                $('#test_session_id').prepend(newOption);
            }
            $('#test_session_id').val(data.sessionId);
            $('#testSenderStatus').removeClass('badge--warning').addClass('badge--success').text('Ready');
        }

        notify('success', 'WhatsApp account paired and connected successfully!');
    }

    // Copy Pairing Code Button
    $('#btnCopyPairingCode').on('click', function(){
        const code = $('#displayPairingCode').text().trim();
        if(code && code !== '----'){
            navigator.clipboard.writeText(code).then(() => {
                notify('success', 'Pairing Code copied to clipboard: ' + code);
            });
        }
    });

    // Test Message Form Handler
    $('#testMessageForm').on('submit', function(e){
        e.preventDefault();

        const sessionId = $('#test_session_id').val();
        const receiver = $('#receiver_number').val().trim();
        const message = $('#test_message').val().trim();

        if(!sessionId){
            notify('error', 'Please select or connect a WhatsApp account first.');
            return;
        }

        if(!receiver){
            notify('error', 'Please enter recipient WhatsApp number.');
            return;
        }

        if(!message){
            notify('error', 'Please enter a message.');
            return;
        }

        $('#btnSendTestMessage').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Sending...');

        $.ajax({
            url: "{{ route('admin.account.listing.test.message') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                session_id: sessionId,
                receiver: receiver,
                message: message
            },
            success: function(res){
                $('#btnSendTestMessage').prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send Test Message');
                if(res.status === 'success'){
                    notify('success', res.message || 'Test message sent successfully!');
                } else {
                    notify('error', res.error || 'Failed to send message.');
                }
            },
            error: function(xhr){
                $('#btnSendTestMessage').prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send Test Message');
                let errMsg = 'Failed to send message.';
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

})(jQuery);
</script>
@endpush
