@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <!-- Left Column: Account Form -->
    <div class="col-xl-5 col-lg-6">
        <div class="card b-radius--10 shadow-sm mb-4">
            <div class="card-header bg--primary text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="lab la-whatsapp me-2 fs-4"></i> @lang('Account Details')
                </h5>
                <a href="{{ route('admin.account.listing.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="las la-list me-1"></i> @lang('All Accounts')
                </a>
            </div>
            <div class="card-body p-4">
                <form id="qrGenerateForm">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="fw-bold mb-2">@lang('Account Label / Name') <span class="text--danger">*</span></label>
                        <input type="text" name="account_name" id="account_name" class="form-control form-control-lg" placeholder="@lang('e.g. My WhatsApp Number 1')" required>
                        <span class="text-muted d-block mt-2 small">
                            <i class="las la-info-circle me-1"></i> @lang('Enter a recognizable name for this WhatsApp account')
                        </span>
                    </div>

                    <button type="submit" class="btn btn--primary w-100 py-2 fs-6 fw-bold" id="btnGenerateQR" style="height: 48px;">
                        <i class="las la-qrcode me-2 fs-5"></i> @lang('Generate QR Code')
                    </button>
                </form>

                <div class="p-3 bg--light rounded border mt-4">
                    <div class="d-flex align-items-start">
                        <div class="me-3 text--primary fs-4">
                            <i class="las la-shield-alt"></i>
                        </div>
                        <div>
                            <strong class="d-block text--dark mb-1">@lang('Security & Privacy')</strong>
                            <p class="text-muted small mb-0 lh-base">
                                @lang('Your WhatsApp session keys are encrypted locally using the Baileys multi-device engine. Keep this page open while scanning the QR code.')
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
                    <i class="las la-paper-plane me-2 fs-4 text--success"></i> @lang('Test Send Message')
                </h5>
                <span class="badge badge--success" id="testSenderStatus">
                    {{ $connectedAccounts->count() > 0 ? trans('Ready') : trans('Connect an Account') }}
                </span>
            </div>
            <div class="card-body p-4">
                <form id="testMessageForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Sending From Account') <span class="text--danger">*</span></label>
                        <select name="test_session_id" id="test_session_id" class="form-control form-select" required>
                            @forelse($connectedAccounts as $acc)
                                <option value="{{ $acc->session_id }}" data-phone="{{ $acc->phone_number }}">
                                    {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Connected' }})
                                </option>
                            @empty
                                <option value="" disabled selected>@lang('No account connected yet. Scan QR code first.')</option>
                            @endforelse
                        </select>
                        <small class="text-muted d-block mt-1"><i class="las la-info-circle"></i> @lang('Select the connected WhatsApp session to send from')</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Recipient WhatsApp Number') <span class="text--danger">*</span></label>
                        <input type="text" name="receiver_number" id="receiver_number" class="form-control" placeholder="@lang('e.g. 1234567890 (with country code)')" required>
                        <small class="text-muted d-block mt-1"><i class="las la-globe me-1"></i>@lang('Include country code without + or spaces (e.g. 923001234567 or 14155552671)')</small>
                    </div>

                    <div class="form-group mb-4">
                        <label class="fw-bold mb-1">@lang('Message Text') <span class="text--danger">*</span></label>
                        <textarea name="test_message" id="test_message" rows="3" class="form-control" placeholder="@lang('Type your test message here...')" required>Hello! This is a test message sent from Auto WhatsApp.</textarea>
                    </div>

                    <button type="submit" class="btn btn--success w-100 py-2 fs-6 fw-bold" id="btnSendTestMessage" style="height: 46px;">
                        <i class="las la-paper-plane me-2 fs-5"></i> @lang('Send Test Message')
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Live QR Code & Instructions -->
    <div class="col-xl-7 col-lg-6">
        <div class="card b-radius--10 shadow-sm h-100">
            <div class="card-header bg--dark text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-qrcode me-2 fs-4"></i> @lang('Scan WhatsApp QR Code')
                </h5>
                <span class="badge badge--warning px-3 py-2" id="connectionBadge">@lang('Not Initialized')</span>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-between p-4">
                
                <div class="w-100 d-flex flex-column align-items-center justify-content-center my-auto py-3">
                    <!-- Initial Placeholder -->
                    <div id="qrPlaceholder" class="text-center py-4">
                        <div class="mb-3">
                            <i class="lab la-whatsapp text--success" style="font-size: 85px;"></i>
                        </div>
                        <h5 class="text-dark fw-bold mb-2">@lang('Click "Generate QR Code" to start')</h5>
                        <p class="text-muted small mb-0">@lang('The QR code will appear here for you to scan with your WhatsApp app.')</p>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="qrLoading" class="text-center py-4 d-none">
                        <div class="spinner-border text--primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h6 class="text-muted">@lang('Initializing WhatsApp Session & Generating QR...')</h6>
                    </div>

                    <!-- QR Display Container -->
                    <div id="qrContainer" class="text-center d-none">
                        <div class="qr-box p-3 border rounded shadow-sm bg-white mb-3 d-inline-block position-relative">
                            <img id="qrImageElement" src="" alt="WhatsApp QR Code" class="img-fluid" style="max-width: 280px; min-height: 280px;">
                            <div id="qrOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center bg-white bg-opacity-75">
                                <i class="fas fa-spinner fa-spin text--primary mb-2" style="font-size: 35px;"></i>
                                <span class="fw-bold text--primary">@lang('Connecting...')</span>
                            </div>
                        </div>

                        <div class="text-center mb-2">
                            <span class="badge badge--info fs-6 px-3 py-2" id="statusMessage">
                                <i class="fas fa-spinner fa-spin me-1"></i> @lang('Waiting for scan...')
                            </span>
                        </div>
                    </div>

                    <!-- Connected Success Container -->
                    <div id="qrSuccess" class="text-center py-4 d-none w-100">
                        <div class="mb-3">
                            <i class="las la-check-circle text--success" style="font-size: 90px;"></i>
                        </div>
                        <h4 class="text--success mb-2 fw-bold">@lang('WhatsApp Connected Successfully!')</h4>
                        <p class="text-muted mb-4 fs-6" id="connectedDetails"></p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#testMessageForm" class="btn btn--primary px-4 py-2" id="btnJumpToTest">
                                <i class="las la-paper-plane me-1"></i> @lang('Test Send Message Below')
                            </a>
                            <a href="{{ route('admin.account.listing.index') }}" class="btn btn--success px-4 py-2">
                                <i class="las la-list me-1"></i> @lang('View All Accounts')
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Instructions Section -->
                <div class="w-100 border-top pt-3 mt-3">
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="las la-list-ol me-2 text--primary fs-5"></i> @lang('How to connect your WhatsApp'):
                    </h6>
                    <div class="d-flex flex-column gap-2 small">
                        <div class="d-flex align-items-center">
                            <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">1</span>
                            <span>@lang('Open') <strong>@lang('WhatsApp')</strong> @lang('on your mobile phone').</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">2</span>
                            <span>@lang('Tap') <strong>@lang('Menu (3 dots)')</strong> @lang('or') <strong>@lang('Settings')</strong> @lang('and select') <strong>@lang('Linked Devices')</strong>.</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">3</span>
                            <span>@lang('Tap on') <strong>@lang('Link a Device')</strong>.</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg--primary rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">4</span>
                            <span>@lang('Point your phone camera at the QR code displayed above').</span>
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

    $('#qrGenerateForm').on('submit', function(e){
        e.preventDefault();

        const accountName = $('#account_name').val().trim();

        if(!accountName){
            notify('error', 'Please enter an account name');
            return;
        }

        if(pollInterval) clearInterval(pollInterval);

        // UI Updates
        $('#qrPlaceholder').addClass('d-none');
        $('#qrSuccess').addClass('d-none');
        $('#qrContainer').addClass('d-none');
        $('#qrLoading').removeClass('d-none');
        $('#connectionBadge').removeClass('badge--success badge--danger').addClass('badge--warning').text('Initializing...');
        $('#btnGenerateQR').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Generating...');

        $.ajax({
            url: "{{ route('admin.account.listing.init.session') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                account_name: accountName
            },
            success: function(response){
                $('#qrLoading').addClass('d-none');
                $('#btnGenerateQR').prop('disabled', false).html('<i class="las la-qrcode me-2"></i> Generate QR Code');

                activeSessionId = response.sessionId;

                if(response.status === 'connected'){
                    handleConnected(response);
                    return;
                }

                if(response.qrImage){
                    $('#qrImageElement').attr('src', response.qrImage);
                    $('#qrContainer').removeClass('d-none');
                    $('#connectionBadge').text('Scan QR Code');
                    $('#statusMessage').html('<i class="fas fa-spinner fa-spin me-1"></i> Waiting for scan...');
                    startPolling(response.sessionId, accountName);
                } else {
                    $('#qrContainer').removeClass('d-none');
                    $('#connectionBadge').text('Connecting...');
                    startPolling(response.sessionId, accountName);
                }
            },
            error: function(xhr){
                $('#qrLoading').addClass('d-none');
                $('#qrPlaceholder').removeClass('d-none');
                $('#btnGenerateQR').prop('disabled', false).html('<i class="las la-qrcode me-2"></i> Generate QR Code');
                $('#connectionBadge').removeClass('badge--warning').addClass('badge--danger').text('Error');

                let errMsg = 'Failed to initialize WhatsApp session.';
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

    function startPolling(sessionId, accountName){
        if(pollInterval) clearInterval(pollInterval);

        pollInterval = setInterval(function(){
            $.ajax({
                url: "{{ url('admin/account-listing/session-status') }}/" + sessionId,
                type: "GET",
                success: function(res){
                    if(res.status === 'qr_ready' && res.qrImage){
                        $('#qrImageElement').attr('src', res.qrImage);
                        $('#qrOverlay').addClass('d-none');
                        $('#connectionBadge').text('Scan QR Code');
                        $('#statusMessage').html('<i class="fas fa-spinner fa-spin me-1"></i> Waiting for scan...');
                    } else if(res.status === 'connecting'){
                        $('#qrOverlay').removeClass('d-none');
                        $('#connectionBadge').text('Connecting...');
                        $('#statusMessage').html('<i class="fas fa-spinner fa-spin me-1"></i> Connecting to WhatsApp...');
                    } else if(res.status === 'connected'){
                        clearInterval(pollInterval);
                        handleConnected(res, accountName);
                    } else if(res.status === 'disconnected'){
                        $('#connectionBadge').removeClass('badge--warning badge--success').addClass('badge--danger').text('Disconnected');
                        $('#statusMessage').html('<i class="fas fa-exclamation-circle me-1"></i> Connection closed. Please regenerate QR.');
                    }
                }
            });
        }, 2000);
    }

    function handleConnected(data, accountName){
        $('#qrContainer').addClass('d-none');
        $('#qrLoading').addClass('d-none');
        $('#qrPlaceholder').addClass('d-none');
        $('#qrSuccess').removeClass('d-none');
        $('#connectionBadge').removeClass('badge--warning badge--danger').addClass('badge--success').text('Connected');

        const phone = data.user && data.user.phone ? '+' + data.user.phone : '';
        const name = data.user && data.user.name ? data.user.name : (accountName || 'WhatsApp Account');
        $('#connectedDetails').text(`Phone: ${phone} | Name: ${name}`);

        // Add newly connected option to test message sender dropdown
        if(data.sessionId){
            const newOption = `<option value="${data.sessionId}" selected>${name} (${phone})</option>`;
            $('#test_session_id').find('option[value=""]').remove();
            if($('#test_session_id').find(`option[value="${data.sessionId}"]`).length === 0){
                $('#test_session_id').prepend(newOption);
            }
            $('#test_session_id').val(data.sessionId);
            $('#testSenderStatus').removeClass('badge--warning').addClass('badge--success').text('Ready');
        }

        notify('success', 'WhatsApp account connected successfully!');
    }

    // Handle Test Message Submission
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
                $('#btnSendTestMessage').prop('disabled', false).html('<i class="las la-paper-plane me-2 fs-5"></i> Send Test Message');
                if(res.status === 'success'){
                    notify('success', res.message || 'Test message sent successfully!');
                } else {
                    notify('error', res.error || 'Failed to send message.');
                }
            },
            error: function(xhr){
                $('#btnSendTestMessage').prop('disabled', false).html('<i class="las la-paper-plane me-2 fs-5"></i> Send Test Message');
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
