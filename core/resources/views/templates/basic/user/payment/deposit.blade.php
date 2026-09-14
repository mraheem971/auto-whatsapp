@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="deposit-container py-4">
        <!-- Header & Balance Overview -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold mb-1">
                    @if (session()->get('requestAmount'))
                        @lang('Complete Payment')
                    @else
                        @lang('Deposit Funds')
                    @endif
                </h4>
                <p class="text-muted mb-0 small">
                    @lang('Add funds securely to your account balance to purchase plans, run campaigns, and activate bots.')
                </p>
            </div>
            <div class="balance-card px-4 py-2 rounded-3 border d-flex align-items-center gap-3">
                <div class="balance-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="las la-wallet fs-3 text-success"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">@lang('Current Balance')</span>
                    <h5 class="fw-bold mb-0 text-success">{{ showAmount(auth()->user()->balance) }} {{ __(gs('cur_text')) }}</h5>
                </div>
            </div>
        </div>

        <form class="deposit-form" action="{{ route('user.deposit.insert') }}" method="post">
            @csrf
            <input name="currency" type="hidden">

            <div class="row g-4">
                <!-- Left Column: Payment Methods Selection -->
                <div class="col-xl-7 col-lg-6">
                    <div class="card custom--card h-100 border shadow-sm">
                        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
                                <i class="las la-credit-card text-success fs-5"></i>
                                @lang('1. Select Payment Method')
                            </h6>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                {{ $gatewayCurrency->count() }} @lang('Available Gateways')
                            </span>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <div class="gateway-grid row g-3 gateway-option-list">
                                @forelse ($gatewayCurrency as $data)
                                    <div class="col-sm-6 gateway-col @if ($loop->index > 5) d-none extra-gateway @endif">
                                        <label class="gateway-card-item w-100 h-100 p-3 rounded-3 border position-relative d-flex flex-column justify-content-between cursor-pointer @if (old('gateway') ? old('gateway') == $data->method_code : $loop->first) active-gateway @endif" for="{{ titleToKey($data->name) }}_{{ $data->method_code }}">
                                            <!-- Checkmark Badge -->
                                            <div class="active-check-badge position-absolute top-0 end-0 m-2">
                                                <i class="las la-check-circle fs-5 text-success"></i>
                                            </div>

                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                <div class="gateway-thumb-wrapper bg-white p-2 rounded border d-flex align-items-center justify-content-center">
                                                    <img class="gateway-thumb-img img-fluid" src="{{ getImage(getFilePath('gateway') . '/' . $data->method->image) }}" alt="{{ __($data->name) }}">
                                                </div>
                                                <div class="gateway-meta overflow-hidden">
                                                    <h6 class="fw-bold mb-0 text-truncate">{{ __($data->name) }}</h6>
                                                    <span class="badge bg-secondary-subtle text-muted small mt-1">
                                                        {{ $data->currency }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="gateway-details pt-2 border-top small text-muted">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>@lang('Limit'):</span>
                                                    <span class="fw-semibold text-dark-emphasis">{{ showAmount($data->min_amount) }} - {{ showAmount($data->max_amount) }} {{ __(gs('cur_text')) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>@lang('Charge'):</span>
                                                    <span class="fw-semibold text-dark-emphasis">
                                                        @if($data->fixed_charge > 0 || $data->percent_charge > 0)
                                                            {{ showAmount($data->fixed_charge) }} {{ __(gs('cur_text')) }} + {{ showAmount($data->percent_charge) }}%
                                                        @else
                                                            <span class="text-success fw-bold">@lang('Free')</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>

                                            <input class="payment-item__radio gateway-input" id="{{ titleToKey($data->name) }}_{{ $data->method_code }}" name="gateway" data-gateway='@json($data)' data-min-amount="{{ showAmount($data->min_amount) }}" data-max-amount="{{ showAmount($data->max_amount) }}" type="radio" value="{{ $data->method_code }}" hidden @if (old('gateway')) @checked(old('gateway') == $data->method_code) @else @checked($loop->first) @endif>
                                        </label>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="las la-exclamation-triangle fs-1 text-warning mb-2"></i>
                                        <p class="text-muted">@lang('No active deposit payment methods available at the moment.')</p>
                                    </div>
                                @endforelse
                            </div>

                            @if ($gatewayCurrency->count() > 6)
                                <div class="text-center mt-3">
                                    <button class="btn btn-sm btn-outline-secondary more-gateway-btn" type="button">
                                        <i class="las la-angle-down me-1"></i> @lang('Show All Payment Options') ({{ $gatewayCurrency->count() }})
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column: Amount & Live Calculation Summary -->
                <div class="col-xl-5 col-lg-6">
                    <div class="card custom--card h-100 border shadow-sm">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h6 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
                                <i class="las la-calculator text-success fs-5"></i>
                                @lang('2. Enter Deposit Amount')
                            </h6>
                        </div>
                        <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                            <div>
                                <!-- Amount Input -->
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold small text-muted mb-2">@lang('Deposit Amount')</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text fw-bold bg-light text-muted">{{ gs('cur_sym') }}</span>
                                        @if (session()->get('requestAmount'))
                                            <input class="form-control form-control-lg fw-bold amount" name="amount" type="number" step="any" value="{{ session()->get('requestAmount') }}" placeholder="0.00" autocomplete="off" @readonly(true)>
                                        @else
                                            <input class="form-control form-control-lg fw-bold amount" name="amount" type="number" step="any" value="{{ old('amount') }}" placeholder="0.00" autocomplete="off">
                                        @endif
                                        <span class="input-group-text fw-bold bg-light text-muted">{{ __(gs('cur_text')) }}</span>
                                    </div>
                                    <!-- Dynamic Limit Alert / Guidance -->
                                    <div class="d-flex justify-content-between align-items-center mt-2 small">
                                        <span class="text-muted">@lang('Allowed Limit'):</span>
                                        <span class="fw-semibold text-primary gateway-limit-text">@lang('0.00 - 0.00')</span>
                                    </div>
                                    <div class="amount-validation-msg mt-1 small d-none"></div>
                                </div>

                                <!-- Quick Amount Chips -->
                                @if (!session()->get('requestAmount'))
                                    <div class="quick-amounts mb-4">
                                        <label class="form-label fw-semibold small text-muted mb-2">@lang('Quick Select'):</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary quick-amt-btn" data-amt="50">+{{ gs('cur_sym') }}50</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary quick-amt-btn" data-amt="100">+{{ gs('cur_sym') }}100</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary quick-amt-btn" data-amt="500">+{{ gs('cur_sym') }}500</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary quick-amt-btn" data-amt="1000">+{{ gs('cur_sym') }}1,000</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary quick-amt-btn" data-amt="5000">+{{ gs('cur_sym') }}5,000</button>
                                        </div>
                                    </div>
                                @endif

                                <!-- Summary Breakdown Card -->
                                <div class="summary-box p-3 rounded-3 bg-light border mb-4">
                                    <h6 class="fw-bold mb-3 small text-uppercase text-muted letter-spacing-1">
                                        <i class="las la-file-invoice me-1"></i> @lang('Payment Summary')
                                    </h6>
                                    
                                    <div class="summary-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span class="text-muted small">@lang('Selected Gateway')</span>
                                        <span class="fw-bold selected-gateway-name text-dark-emphasis">-</span>
                                    </div>

                                    <div class="summary-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span class="text-muted small">@lang('Deposit Amount')</span>
                                        <span class="fw-semibold deposit-amount-preview">{{ gs('cur_sym') }}0.00</span>
                                    </div>

                                    <div class="summary-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span class="text-muted small d-flex align-items-center gap-1">
                                            @lang('Gateway Charge')
                                            <i class="las la-info-circle text-muted proccessing-fee-info" data-bs-toggle="tooltip" title="@lang('Gateway processing fee')"></i>
                                        </span>
                                        <span class="fw-semibold text-danger processing-fee-preview">{{ gs('cur_sym') }}0.00</span>
                                    </div>

                                    <div class="summary-item d-flex justify-content-between align-items-center py-2 border-bottom total-row">
                                        <span class="fw-bold text-dark-emphasis">@lang('Total Payable')</span>
                                        <h5 class="fw-bold text-success mb-0 final-amount-preview">{{ gs('cur_sym') }}0.00</h5>
                                    </div>

                                    <!-- Currency Conversion Row (Conditional) -->
                                    <div class="gateway-conversion-box d-none pt-2 mt-2 border-top">
                                        <div class="d-flex justify-content-between align-items-center py-1 small">
                                            <span class="text-muted">@lang('Exchange Rate')</span>
                                            <span class="fw-semibold conversion-rate-text">-</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center py-1">
                                            <span class="fw-bold text-dark-emphasis small">@lang('Payable in Gateway Currency')</span>
                                            <h6 class="fw-bold text-primary mb-0 in-currency-text">-</h6>
                                        </div>
                                    </div>

                                    <div class="crypto-message alert alert-info py-2 px-3 mt-3 mb-0 small d-none">
                                        <i class="las la-coins me-1"></i>
                                        @lang('Conversion with') <strong class="gateway-currency"></strong> @lang('and wallet address will be provided on the next confirmation step.')
                                    </div>
                                </div>
                            </div>

                            <!-- Action & Security Badges -->
                            <div>
                                <button class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2 deposit-submit-btn" type="submit" disabled>
                                    <i class="las la-lock"></i>
                                    @if (session()->get('requestAmount'))
                                        @lang('Pay Now') ({{ showAmount(session()->get('requestAmount')) }} {{ gs('cur_text') }})
                                    @else
                                        @lang('Proceed to Payment')
                                    @endif
                                </button>

                                <div class="d-flex justify-content-center align-items-center gap-3 text-muted small mt-3 pt-2">
                                    <span class="d-flex align-items-center gap-1"><i class="las la-shield-alt text-success fs-5"></i> 256-bit SSL</span>
                                    <span>&bull;</span>
                                    <span class="d-flex align-items-center gap-1"><i class="las la-bolt text-warning fs-5"></i> Instant Deposit</span>
                                    <span>&bull;</span>
                                    <span class="d-flex align-items-center gap-1"><i class="las la-check-circle text-primary fs-5"></i> 100% Verified</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('style')
<style>
    .cursor-pointer {
        cursor: pointer;
    }
    .balance-card {
        background: rgba(37, 211, 102, 0.08);
        border-color: rgba(37, 211, 102, 0.25) !important;
    }
    .balance-icon {
        width: 46px;
        height: 46px;
        background: rgba(37, 211, 102, 0.15);
    }
    .gateway-thumb-wrapper {
        width: 60px;
        height: 44px;
        flex-shrink: 0;
    }
    .gateway-thumb-img {
        max-height: 36px;
        max-width: 100%;
        object-fit: contain;
    }
    .gateway-card-item {
        background: var(--card-bg-light, #ffffff);
        border: 2px solid rgba(0, 0, 0, 0.08) !important;
        transition: all 0.25s ease-in-out;
    }
    .gateway-card-item:hover {
        border-color: #25d366 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .gateway-card-item.active-gateway {
        border-color: #25d366 !important;
        background: rgba(37, 211, 102, 0.05);
        box-shadow: 0 0 0 1px #25d366, 0 4px 14px rgba(37, 211, 102, 0.12);
    }
    .active-check-badge {
        display: none;
    }
    .gateway-card-item.active-gateway .active-check-badge {
        display: block;
    }
    .quick-amt-btn {
        font-size: 12px;
        font-weight: 600;
        border-radius: 20px;
        padding: 4px 12px;
        transition: all 0.2s;
    }
    .quick-amt-btn:hover {
        background-color: #25d366;
        border-color: #25d366;
        color: #fff;
    }
    .letter-spacing-1 {
        letter-spacing: 0.5px;
    }

    /* Dark Mode specific enhancements */
    body.dark-mode .balance-card {
        background: rgba(37, 211, 102, 0.1);
        border-color: rgba(37, 211, 102, 0.3) !important;
    }
    body.dark-mode .gateway-card-item {
        background: #182229;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: #e9edef;
    }
    body.dark-mode .gateway-card-item.active-gateway {
        border-color: #25d366 !important;
        background: rgba(37, 211, 102, 0.12);
    }
    body.dark-mode .gateway-thumb-wrapper {
        background: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.2) !important;
    }
    body.dark-mode .summary-box {
        background: #111b21 !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    body.dark-mode .quick-amt-btn {
        background: #111b21;
        border-color: rgba(255, 255, 255, 0.15);
        color: #e9edef;
    }
    body.dark-mode .quick-amt-btn:hover {
        background: #25d366;
        border-color: #25d366;
        color: #ffffff;
    }
    body.dark-mode .input-group-text {
        background: #111b21 !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
        color: #8696a0 !important;
    }
    body.dark-mode .text-dark-emphasis {
        color: #e9edef !important;
    }
</style>
@endpush

@push('script')
    <script>
        "use strict";
        (function($) {
            var amount = parseFloat($('.amount').val() || 0);
            var gateway, minAmount, maxAmount;

            // Gateway Card selection click
            $('.gateway-card-item').on('click', function() {
                $('.gateway-card-item').removeClass('active-gateway');
                $(this).addClass('active-gateway');
                var radio = $(this).find('.gateway-input');
                radio.prop('checked', true);
                gatewayChange();
            });

            // Quick amount buttons
            $('.quick-amt-btn').on('click', function(e) {
                e.preventDefault();
                var addAmt = parseFloat($(this).data('amt') || 0);
                var currentAmt = parseFloat($('.amount').val() || 0);
                var newAmt = currentAmt + addAmt;
                $('.amount').val(newAmt);
                amount = newAmt;
                calculation();
            });

            // Amount input change
            $('.amount').on('input keyup', function(e) {
                amount = parseFloat($(this).val() || 0);
                calculation();
            });

            function gatewayChange() {
                var gatewayElement = $('.gateway-input:checked');
                if (!gatewayElement.length) {
                    gatewayElement = $('.gateway-input').first();
                    gatewayElement.prop('checked', true);
                    gatewayElement.closest('.gateway-card-item').addClass('active-gateway');
                }

                gateway = gatewayElement.data('gateway');
                minAmount = parseFloat(gatewayElement.data('min-amount') || 0);
                maxAmount = parseFloat(gatewayElement.data('max-amount') || 0);

                $('.selected-gateway-name').text(gateway.name || '-');
                $('.gateway-limit-text').text(`${minAmount.toFixed(2)} - ${maxAmount.toFixed(2)} {{ __(gs('cur_text')) }}`);

                var percentCharge = parseFloat(gateway.percent_charge || 0);
                var fixedCharge = parseFloat(gateway.fixed_charge || 0);
                var feeInfo = `${percentCharge.toFixed(2)}% + ${fixedCharge.toFixed(2)} {{ __(gs('cur_text')) }}`;
                $(".proccessing-fee-info").attr("data-bs-original-title", `@lang('Processing fee:') ${feeInfo}`);

                calculation();
            }

            // Show more gateways toggle
            $(".more-gateway-btn").on("click", function(e) {
                e.preventDefault();
                $(".extra-gateway").removeClass("d-none");
                $(this).hide();
            });

            function calculation() {
                if (!gateway) return;

                var percentCharge = parseFloat(gateway.percent_charge || 0);
                var fixedCharge = parseFloat(gateway.fixed_charge || 0);
                var totalPercentCharge = 0;

                if (amount > 0) {
                    totalPercentCharge = (amount / 100) * percentCharge;
                }

                var totalCharge = totalPercentCharge + fixedCharge;
                var totalAmount = (amount > 0 ? amount : 0) + totalCharge;

                // Update summary texts
                $('.deposit-amount-preview').text(`{{ gs('cur_sym') }}${(amount || 0).toFixed(2)}`);
                $('.processing-fee-preview').text(`{{ gs('cur_sym') }}${totalCharge.toFixed(2)}`);
                $('.final-amount-preview').text(`{{ gs('cur_sym') }}${totalAmount.toFixed(2)}`);
                $('input[name=currency]').val(gateway.currency);
                $('.gateway-currency').text(gateway.currency);

                // Validation messaging & button state
                var submitBtn = $('.deposit-submit-btn');
                var valMsg = $('.amount-validation-msg');

                if (!amount || amount <= 0) {
                    submitBtn.prop('disabled', true);
                    valMsg.addClass('d-none').removeClass('text-danger text-success');
                } else if (amount < minAmount) {
                    submitBtn.prop('disabled', true);
                    valMsg.removeClass('d-none text-success').addClass('text-danger')
                          .html(`<i class="las la-exclamation-circle me-1"></i> @lang('Amount is below minimum limit of') ${minAmount.toFixed(2)} {{ __(gs('cur_text')) }}`);
                } else if (amount > maxAmount) {
                    submitBtn.prop('disabled', true);
                    valMsg.removeClass('d-none text-success').addClass('text-danger')
                          .html(`<i class="las la-exclamation-circle me-1"></i> @lang('Amount exceeds maximum limit of') ${maxAmount.toFixed(2)} {{ __(gs('cur_text')) }}`);
                } else {
                    submitBtn.prop('disabled', false);
                    valMsg.removeClass('d-none text-danger').addClass('text-success')
                          .html(`<i class="las la-check-circle me-1"></i> @lang('Valid deposit amount')`);
                }

                // Currency conversion display
                if (gateway.currency !== "{{ gs('cur_text') }}" && gateway.method.crypto != 1) {
                    var rate = parseFloat(gateway.rate || 1);
                    var inCurrency = (totalAmount * rate).toFixed(2);
                    $('.gateway-conversion-box').removeClass('d-none');
                    $('.conversion-rate-text').text(`1 {{ __(gs('cur_text')) }} = ${rate.toFixed(2)} ${gateway.currency}`);
                    $('.in-currency-text').text(`${inCurrency} ${gateway.currency}`);
                } else {
                    $('.gateway-conversion-box').addClass('d-none');
                }

                // Crypto message
                if (gateway.method.crypto == 1) {
                    $('.crypto-message').removeClass('d-none');
                } else {
                    $('.crypto-message').addClass('d-none');
                }
            }

            // Init Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize on load
            gatewayChange();
        })(jQuery);
    </script>
@endpush
