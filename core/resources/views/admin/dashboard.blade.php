@extends('admin.layouts.app')

@section('panel')

    <!-- WhatsApp & Contacts Summary Widgets -->
    <div class="row gy-4 mb-3">
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="6"
                link="{{route('admin.account.listing.index')}}"
                icon="lab la-whatsapp"
                title="Total WhatsApp Accounts"
                value="{{$whatsappAccounts['total']}}"
                bg="primary"
            />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="6"
                link="{{route('admin.account.listing.active')}}"
                icon="las la-check-circle"
                title="Active Accounts"
                value="{{$whatsappAccounts['active']}}"
                bg="success"
            />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="6"
                link="{{route('admin.account.listing.pending')}}"
                icon="las la-clock"
                title="Pending Accounts"
                value="{{$whatsappAccounts['pending']}}"
                bg="warning"
            />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="6"
                link="{{route('admin.contacts.index')}}"
                icon="las la-address-book"
                title="Total Contacts"
                value="{{$totalContacts}}"
                bg="info"
            />
        </div>
    </div>

    <!-- Active WhatsApp Accounts Showcase Card -->
    <div class="row gy-4 mb-4">
        <div class="col-12">
            <div class="card b-radius--10 shadow-sm">
                <div class="card-header bg--primary text-white d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title text-white mb-0 d-flex align-items-center">
                        <i class="lab la-whatsapp me-2 fs-4"></i> @lang('Active WhatsApp Accounts')
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.account.listing.create') }}" class="btn btn-sm btn-outline-light">
                            <i class="las la-plus me-1"></i> @lang('Add WhatsApp Account')
                        </a>
                        <a href="{{ route('admin.account.listing.index') }}" class="btn btn-sm btn-outline-light">
                            <i class="las la-list me-1"></i> @lang('All Accounts')
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover style--two table--light mb-0">
                            <thead>
                                <tr>
                                    <th>@lang('Account Name')</th>
                                    <th>@lang('WhatsApp Number')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Connected At')</th>
                                    <th class="text-end pe-4">@lang('Quick Actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeAccounts as $acc)
                                    <tr>
                                        <td>
                                            <div class="user">
                                                <div class="thumb me-2">
                                                    <i class="lab la-whatsapp text--success" style="font-size: 26px;"></i>
                                                </div>
                                                <span class="name fw-bold">{{ __($acc->account_name) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($acc->phone_number)
                                                <span class="badge badge--info fs-6">+{{ $acc->phone_number }}</span>
                                            @else
                                                <span class="text-muted">@lang('Not Connected')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php echo $acc->statusBadge; @endphp
                                        </td>
                                        <td>
                                            {{ $acc->last_connected_at ? showDateTime($acc->last_connected_at) : 'N/A' }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-inline-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline--primary btnExtractGroups"
                                                    data-session_id="{{ $acc->session_id }}"
                                                    data-name="{{ $acc->account_name }}"
                                                    data-bs-toggle="tooltip"
                                                    title="@lang('Extract WhatsApp Groups')">
                                                    <i class="las la-users-cog fs-6"></i>
                                                </button>

                                                <button type="button" class="btn btn-sm btn-outline--success btnTestMessage"
                                                    data-session_id="{{ $acc->session_id }}"
                                                    data-name="{{ $acc->account_name }}"
                                                    data-phone="{{ $acc->phone_number }}"
                                                    data-bs-toggle="tooltip"
                                                    title="@lang('Send Test Message')">
                                                    <i class="las la-paper-plane fs-6"></i>
                                                </button>

                                                <a href="{{ route('admin.contacts.sync') }}" class="btn btn-sm btn-outline--info"
                                                    data-bs-toggle="tooltip"
                                                    title="@lang('Sync Contacts')">
                                                    <i class="las la-sync fs-6"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center py-4" colspan="100%">
                                            <div class="empty-state">
                                                <i class="lab la-whatsapp text--muted mb-3" style="font-size: 48px;"></i>
                                                <p class="text-muted mb-2">@lang('No active WhatsApp accounts connected yet.')</p>
                                                <a href="{{ route('admin.account.listing.create') }}" class="btn btn-sm btn--primary">
                                                    <i class="las la-qrcode me-1"></i>@lang('Link a WhatsApp Device')
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Overview Widgets -->
    <div class="row gy-4">
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="7"
                link="{{route('admin.users.all')}}"
                icon="las la-users"
                title="Total Users"
                value="{{$widget['total_users']}}"
                bg="primary"
            />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="7"
                link="{{route('admin.users.active')}}"
                icon="las la-user-check"
                title="Active Users"
                value="{{$widget['verified_users']}}"
                bg="success"
            />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="7"
                link="{{route('admin.users.email.unverified')}}"
                icon="lar la-envelope"
                title="Email Unverified Users"
                value="{{$widget['email_unverified_users']}}"
                bg="danger"
            />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="7"
                link="{{route('admin.users.mobile.unverified')}}"
                icon="las la-comment-slash"
                title="Mobile Unverified Users"
                value="{{$widget['mobile_unverified_users']}}"
                bg="warning"
            />
        </div>
    </div>

    <!-- Deposits & Withdrawals Summary Cards -->
    <div class="row mt-2 gy-4">
        <div class="col-xxl-6">
            <div class="card box-shadow3 h-100">
                <div class="card-body">
                    <h5 class="card-title">@lang('Deposits')</h5>
                    <div class="widget-card-wrapper">
                        <div class="widget-card bg--success">
                            <a href="{{ route('admin.deposit.list') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($deposit['total_deposit_amount']) }}</h6>
                                    <p class="widget-card-title">@lang('Total Deposited')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--warning">
                            <a href="{{ route('admin.deposit.pending') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $deposit['total_deposit_pending'] }}</h6>
                                    <p class="widget-card-title">@lang('Pending Deposits')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--danger">
                            <a href="{{ route('admin.deposit.rejected') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $deposit['total_deposit_rejected'] }}</h6>
                                    <p class="widget-card-title">@lang('Rejected Deposits')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--primary">
                            <a href="{{ route('admin.deposit.list') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($deposit['total_deposit_charge']) }}</h6>
                                    <p class="widget-card-title">@lang('Deposited Charge')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-6">
            <div class="card box-shadow3 h-100">
                <div class="card-body">
                    <h5 class="card-title">@lang('Withdrawals')</h5>
                    <div class="widget-card-wrapper">
                        <div class="widget-card bg--success">
                            <a href="{{ route('admin.withdraw.data.all') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="lar la-credit-card"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($withdrawals['total_withdraw_amount']) }}</h6>
                                    <p class="widget-card-title">@lang('Total Withdrawn')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--warning">
                            <a href="{{ route('admin.withdraw.data.pending') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $withdrawals['total_withdraw_pending'] }}</h6>
                                    <p class="widget-card-title">@lang('Pending Withdrawals')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--danger">
                            <a href="{{ route('admin.withdraw.data.rejected') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="las la-ban"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $withdrawals['total_withdraw_rejected'] }}</h6>
                                    <p class="widget-card-title">@lang('Rejected Withdrawals')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--primary">
                            <a href="{{ route('admin.withdraw.data.all') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="las la-percent"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($withdrawals['total_withdraw_charge']) }}</h6>
                                    <p class="widget-card-title">@lang('Withdrawal Charge')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Chart Section -->
    <div class="row mb-none-30 mt-30">
        <div class="col-xl-6 mb-30">
            <div class="card">
              <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between">
                    <h5 class="card-title">@lang('Deposit & Withdraw Report')</h5>
                    <div id="reportrange" class="border p-1 cursor-pointer">
                        <i class="la la-calendar"></i>&nbsp;
                        <span></span> <i class="la la-caret-down"></i>
                    </div>
                </div>
                <div id="apex-bar-chart"></div>
              </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">@lang('Transactions Report')</h5>
                <div id="apex-line"></div>
              </div>
            </div>
        </div>
    </div>

    <!-- Browser & OS Counter -->
    <div class="row mb-none-30 mt-5">
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By Browser')</h5>
                    <canvas id="userBrowserChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By OS')</h5>
                    <canvas id="userOsChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By Country')</h5>
                    <canvas id="userCountryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

<!-- Test Message Modal -->
<div id="testMessageModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--dark text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="lab la-whatsapp text--success me-2 fs-4"></i> @lang('Send Test WhatsApp Message')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form id="modalTestMessageForm">
                @csrf
                <input type="hidden" name="session_id" id="modal_session_id">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Sending From')</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="lab la-whatsapp text--success fs-5"></i></span>
                            <input type="text" id="modal_sender_display" class="form-control border-start-0" style="background-color: #f8fafc !important; color: #1e293b !important; font-weight: 600; opacity: 1;" readonly>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Recipient WhatsApp Number') <span class="text--danger">*</span></label>
                        <input type="text" name="receiver" id="modal_receiver" class="form-control" placeholder="@lang('e.g. 923001234567 (with country code)')" required>
                        <small class="text-muted d-block mt-1">
                            <i class="las la-globe me-1"></i>@lang('Include country code without + or special characters')
                        </small>
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">@lang('Message') <span class="text--danger">*</span></label>
                        <textarea name="message" id="modal_message" rows="3" class="form-control" placeholder="@lang('Type your test message...')" required>Hello! This is a test message from Auto WhatsApp.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--success btn-sm px-3 fw-bold" id="btnModalSendMessage">
                        <i class="las la-paper-plane me-1"></i> @lang('Send Message')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Group Extract Modal -->
<div id="groupExtractModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="las la-users-cog me-2 fs-4"></i> @lang('Extracted WhatsApp Groups')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <div class="modal-body p-4">
                <div id="groupLoading" class="text-center py-4">
                    <div class="spinner-border text--primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6 class="text-muted">@lang('Extracting groups from WhatsApp...')</h6>
                </div>

                <div id="groupContent" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0">
                            @lang('Total Groups Found'): <span class="badge badge--success fs-6" id="totalGroupsCount">0</span>
                        </h6>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>@lang('Group Name')</th>
                                    <th>@lang('Total Members')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody id="groupTableBody">
                                <!-- Rendered via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="groupEmpty" class="text-center py-4 d-none">
                    <i class="las la-info-circle text--warning mb-2" style="font-size: 42px;"></i>
                    <p class="text-muted mb-0">@lang('No participating groups found for this WhatsApp account.')</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--dark btn-sm px-4" data-bs-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.account.listing.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus me-1"></i>@lang('Add WhatsApp Account')
    </a>
@endpush

@push('script-lib')
    <script src="{{asset('assets/admin/js/vendor/apexcharts.min.js')}}"></script>
    <script src="{{asset('assets/admin/js/vendor/chart.js.2.8.0.js')}}"></script>
    <script src="{{asset('assets/admin/js/moment.min.js')}}"></script>
    <script src="{{asset('assets/admin/js/daterangepicker.min.js')}}"></script>
    <script src="{{asset('assets/admin/js/charts.js')}}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/admin/css/daterangepicker.css')}}">
@endpush

@push('script')
<script>
(function($){
    "use strict";

    // Test Message Modal
    $('.btnTestMessage').on('click', function(){
        const sessionId = $(this).data('session_id');
        const name = $(this).data('name');
        const phone = $(this).data('phone');

        $('#modal_session_id').val(sessionId);
        $('#modal_sender_display').val(name + (phone ? ' (+' + phone + ')' : ''));
        $('#testMessageModal').modal('show');
    });

    $('#modalTestMessageForm').on('submit', function(e){
        e.preventDefault();

        const sessionId = $('#modal_session_id').val();
        const receiver = $('#modal_receiver').val().trim();
        const message = $('#modal_message').val().trim();

        if(!receiver){
            notify('error', 'Please enter recipient WhatsApp number');
            return;
        }

        $('#btnModalSendMessage').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Sending...');

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
                $('#btnModalSendMessage').prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send Message');
                if(res.status === 'success'){
                    $('#testMessageModal').modal('hide');
                    notify('success', res.message || 'Test message sent successfully!');
                } else {
                    notify('error', res.error || 'Failed to send message.');
                }
            },
            error: function(xhr){
                $('#btnModalSendMessage').prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send Message');
                let errMsg = 'Failed to send message.';
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

    // Group Extractor
    $('.btnExtractGroups').on('click', function(){
        const sessionId = $(this).data('session_id');

        $('#groupLoading').removeClass('d-none');
        $('#groupContent').addClass('d-none');
        $('#groupEmpty').addClass('d-none');
        $('#groupTableBody').empty();
        $('#groupExtractModal').modal('show');

        $.ajax({
            url: "{{ url('admin/account-listing/extract-groups') }}/" + sessionId,
            type: "GET",
            success: function(res){
                $('#groupLoading').addClass('d-none');
                if(res.success && res.groups && res.groups.length > 0){
                    $('#totalGroupsCount').text(res.groups.length);
                    $('#groupContent').removeClass('d-none');

                    res.groups.forEach((g, index) => {
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    <strong class="d-block text-dark">${g.subject}</strong>
                                    <small class="text-muted">${g.id}</small>
                                </td>
                                <td>
                                    <span class="badge badge--info fs-6">${g.participantsCount} Members</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline--success btnCopyGroupNumbers" data-numbers='${JSON.stringify(g.participants.map(p => p.phone).filter(Boolean))}' title="Copy Member Phone Numbers">
                                        <i class="las la-copy me-1"></i> Copy Numbers
                                    </button>
                                </td>
                            </tr>
                        `;
                        $('#groupTableBody').append(row);
                    });
                } else {
                    $('#groupEmpty').removeClass('d-none');
                }
            },
            error: function(xhr){
                $('#groupLoading').addClass('d-none');
                $('#groupEmpty').removeClass('d-none');
                let errMsg = 'Failed to extract WhatsApp groups.';
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

    $(document).on('click', '.btnCopyGroupNumbers', function(){
        const numbers = $(this).data('numbers');
        if(numbers && numbers.length > 0){
            const text = numbers.join(', ');
            navigator.clipboard.writeText(text).then(function(){
                notify('success', `Copied ${numbers.length} member phone numbers to clipboard!`);
            });
        }
    });

    // Initialize Charts
    const start = moment().subtract(14, 'days');
    const end = moment();

    const dateRangeOptions = {
        startDate: start,
        endDate: end,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 15 Days': [moment().subtract(14, 'days'), moment()],
            'Last 30 Days': [moment().subtract(30, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf('month')],
            'This Year': [moment().startOf('year'), moment().endOf('year')],
        },
        maxDate: moment()
    };

    const changeDatePickerText = (element, startDate, endDate) => {
        $(element).html(startDate.format('MMMM D, YYYY') + ' - ' + endDate.format('MMMM D, YYYY'));
    };

    let dwChart = barChart(
        document.querySelector("#apex-bar-chart"),
        @json(__(gs('cur_text'))),
        [{
            name: 'Downloaded',
            data: []
        }],
        [],
    );

    let trxChart = lineChart(
        document.querySelector("#apex-line"),
        [{
            name: "Plus Transactions",
            data: []
        },
        {
            name: "Minus Transactions",
            data: []
        }],
        []
    );

    const depositWithdrawChart = (startDate, endDate) => {
        const data = {
            start_date: startDate.format('YYYY-MM-DD'),
            end_date: endDate.format('YYYY-MM-DD')
        };

        const url = "{{ route('admin.chart.deposit.withdraw') }}";

        $.get(url, data,
            function (data, status) {
                if (status === 'success') {
                    dwChart.updateSeries(data.data);
                    dwChart.updateOptions({
                        xaxis: {
                            categories: data.created_on,
                        }
                    });
                }
            }
        );
    };

    const transactionChart = (startDate, endDate) => {
        const data = {
            start_date: startDate.format('YYYY-MM-DD'),
            end_date: endDate.format('YYYY-MM-DD')
        };

        const url = "{{ route('admin.chart.transaction') }}";

        $.get(url, data,
            function (data, status) {
                if (status === 'success') {
                    trxChart.updateSeries(data.data);
                    trxChart.updateOptions({
                        xaxis: {
                            categories: data.created_on,
                        }
                    });
                }
            }
        );
    };

    $('#reportrange').daterangepicker(dateRangeOptions, (start, end) => {
        changeDatePickerText('#reportrange span', start, end);
        depositWithdrawChart(start, end);
        transactionChart(start, end);
    });

    changeDatePickerText('#reportrange span', start, end);
    depositWithdrawChart(start, end);
    transactionChart(start, end);

    var ctx = document.getElementById('userBrowserChart');
    var myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($chart['user_browser_counter']->keys()),
            datasets: [{
                data: {{ $chart['user_browser_counter']->flatten() }},
                backgroundColor: [
                    '#ff7675',
                    '#6c5ce7',
                    '#ffa62b',
                    '#ffeaa7',
                    '#D980FA',
                    '#fccbcb',
                    '#45aaf2',
                    '#05c46b',
                    '#d2dae2',
                    '#f8a5c2',
                    '#ffb8b8',
                    '#63cdda',
                    '#3ae374',
                    '#6574cd',
                    '#9561e2',
                    '#f66d9b',
                    '#f6993f',
                    '#38c172',
                    '#4dc0b5',
                    '#3490dc',
                    '#e3342f',
                    '#e83e8c',
                    '#fd9644',
                    '#e056fd'
                ],
                borderColor: [
                    'rgba(231, 80, 90, 0.75)'
                ],
                borderWidth: 0,

            }]
        },
        options: {
            aspectRatio: 1,
            responsive: true,
            elements: {
                line: {
                    tension: 0 // disables bezier curves
                }
            },
            scales: {
                xAxes: [{
                    display: false
                }],
                yAxes: [{
                    display: false
                }]
            },
            legend: {
                display: false,
            }
        }
    });

    var ctx = document.getElementById('userOsChart');
    var myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($chart['user_os_counter']->keys()),
            datasets: [{
                data: {{ $chart['user_os_counter']->flatten() }},
                backgroundColor: [
                    '#ff7675',
                    '#6c5ce7',
                    '#ffa62b',
                    '#ffeaa7',
                    '#D980FA',
                    '#fccbcb',
                    '#45aaf2',
                    '#05c46b',
                    '#d2dae2',
                    '#f8a5c2',
                    '#ffb8b8',
                    '#63cdda',
                    '#3ae374',
                    '#6574cd',
                    '#9561e2',
                    '#f66d9b',
                    '#f6993f',
                    '#38c172',
                    '#4dc0b5',
                    '#3490dc',
                    '#e3342f',
                    '#e83e8c',
                    '#fd9644',
                    '#e056fd'
                ],
                borderColor: [
                    'rgba(0, 0, 0, 0.05)'
                ],
                borderWidth: 0,

            }]
        },
        options: {
            aspectRatio: 1,
            responsive: true,
            elements: {
                line: {
                    tension: 0 // disables bezier curves
                }
            },
            scales: {
                xAxes: [{
                    display: false
                }],
                yAxes: [{
                    display: false
                }]
            },
            legend: {
                display: false,
            }
        }
    });

    // Donut chart
    var ctx = document.getElementById('userCountryChart');
    var myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($chart['user_country_counter']->keys()),
            datasets: [{
                data: {{ $chart['user_country_counter']->flatten() }},
                backgroundColor: [
                    '#ff7675',
                    '#6c5ce7',
                    '#ffa62b',
                    '#ffeaa7',
                    '#D980FA',
                    '#fccbcb',
                    '#45aaf2',
                    '#05c46b',
                    '#d2dae2',
                    '#f8a5c2',
                    '#ffb8b8',
                    '#63cdda',
                    '#3ae374',
                    '#6574cd',
                    '#9561e2',
                    '#f66d9b',
                    '#f6993f',
                    '#38c172',
                    '#4dc0b5',
                    '#3490dc',
                    '#e3342f',
                    '#e83e8c',
                    '#fd9644',
                    '#e056fd'
                ],
                borderColor: [
                    'rgba(231, 80, 90, 0.75)'
                ],
                borderWidth: 0,

            }]
        },
        options: {
            aspectRatio: 1,
            responsive: true,
            elements: {
                line: {
                    tension: 0 // disables bezier curves
                }
            },
            scales: {
                xAxes: [{
                    display: false
                }],
                yAxes: [{
                    display: false
                }]
            },
            legend: {
                display: false,
            }
        }
    });

})(jQuery);
</script>
@endpush
