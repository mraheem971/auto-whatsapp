@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-4">
    <div class="container-fluid">
        
        <div class="text-center mb-4">
            <h3 class="fw-bold mb-2">WhatsApp Bot Subscription Plans</h3>
            <p class="text-muted">Choose a plan that fits your business needs. Subscribe directly with any online payment method or wallet balance.</p>
            @if($currentPlan)
                <div class="d-inline-flex align-items-center gap-2 bg-light px-3 py-2 rounded-pill border">
                    <span class="text-muted small">Current Active Plan:</span>
                    <strong class="text-success">{{ $currentPlan->name }}</strong>
                    @if($activeSubscription && $activeSubscription->expires_at)
                        <span class="badge bg-secondary small">Expires {{ showDateTime($activeSubscription->expires_at) }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="row gy-4 justify-content-center">
            @foreach($plans as $plan)
                <div class="col-lg-4 col-md-6">
                    <div class="card custom--card border shadow-sm rounded-3 h-100 position-relative {{ $plan->is_featured ? 'border-primary' : '' }}">
                        @if($plan->is_featured)
                            <div class="position-absolute top-0 end-0 bg-primary text-white small fw-bold px-3 py-1 rounded-bl-3 text-uppercase" style="border-bottom-left-radius: 8px; border-top-right-radius: 8px; z-index: 2;">
                                Most Popular
                            </div>
                        @endif

                        <div class="card-body p-4 text-center d-flex flex-column">
                            <h5 class="fw-bold mb-1">{{ $plan->name }}</h5>
                            <p class="text-muted small mb-3">{{ $plan->tagline }}</p>

                            <div class="my-3 py-3 bg-light rounded">
                                <h2 class="fw-bold mb-0">
                                    @if($plan->price == 0)
                                        Free
                                    @else
                                        ${{ showAmount($plan->price) }}
                                    @endif
                                </h2>
                                <small class="text-muted">for {{ $plan->duration_days }} days</small>
                            </div>

                            <ul class="list-unstyled text-start my-4 flex-grow-1">
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="las la-check-circle text-success fs-5 me-2"></i>
                                    <span><strong>{{ $plan->account_limit }}</strong> WhatsApp Account{{ $plan->account_limit > 1 ? 's' : '' }}</span>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="las la-check-circle text-success fs-5 me-2"></i>
                                    <span><strong>{{ $plan->autoreply_limit }}</strong> Keyword Bots</span>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="las la-check-circle text-success fs-5 me-2"></i>
                                    <span><strong>{{ $plan->template_limit }}</strong> Message Templates</span>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="las la-check-circle text-success fs-5 me-2"></i>
                                    <span><strong>{{ $plan->campaign_limit }}</strong> Run Campaigns</span>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="las la-check-circle text-success fs-5 me-2"></i>
                                    <span><strong>{{ number_format($plan->message_limit) }}</strong> Outgoing Messages</span>
                                </li>
                                @if(!empty($plan->features))
                                    @foreach($plan->features as $feat)
                                        <li class="mb-2 d-flex align-items-center">
                                            <i class="las la-check-circle text-success fs-5 me-2"></i>
                                            <span>{{ $feat }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>

                            <div>
                                @if($currentPlan && $currentPlan->id == $plan->id && $activeSubscription && $activeSubscription->isValid())
                                    <button class="btn btn-secondary w-100 py-2 disabled" disabled>
                                        <i class="las la-check me-1"></i> Currently Active
                                    </button>
                                @elseif($plan->price == 0)
                                    <form action="{{ route('user.plans.subscribe', $plan->id) }}" method="POST" onsubmit="return confirm('Activate Free Trial for {{ $plan->name }}?')">
                                        @csrf
                                        <input type="hidden" name="payment_type" value="free">
                                        <button type="submit" class="btn btn-outline--base w-100 py-2 fw-bold">
                                            <i class="las la-bolt me-1"></i> Start Free Trial
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn {{ $plan->is_featured ? 'btn--base' : 'btn-outline--base' }} w-100 py-2 fw-bold btn-open-checkout" 
                                            data-id="{{ $plan->id }}" 
                                            data-name="{{ $plan->name }}" 
                                            data-price="{{ $plan->price }}" 
                                            data-duration="{{ $plan->duration_days }}">
                                        <i class="las la-shopping-cart me-1"></i> Subscribe Now
                                    </button>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

<!-- Direct Subscription & Checkout Modal -->
<div class="modal fade" id="planCheckoutModal" tabindex="-1" aria-labelledby="planCheckoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white fw-bold" id="planCheckoutModalLabel">
                    <i class="las la-crown text-warning me-1"></i> Direct Subscription Checkout
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="checkoutForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <!-- Plan Info Banner -->
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between mb-4 checkout-info-card">
                        <div>
                            <h5 class="fw-bold mb-0 modal-plan-name">Plan Name</h5>
                            <small class="text-muted modal-plan-duration">30 Days Validity</small>
                        </div>
                        <div class="text-end">
                            <span class="fs-4 fw-bold text--base modal-plan-price">$0.00</span>
                        </div>
                    </div>

                    <!-- Payment Source Selection -->
                    <h6 class="fw-bold mb-3">Select Payment Method</h6>

                    <div class="row g-3 mb-3">
                        <!-- Direct Online Gateway Option -->
                        <div class="col-md-6">
                            <label class="payment-method-card border rounded-3 p-3 d-flex align-items-start gap-3 w-100 cursor-pointer h-100 selected-method" for="pay_type_gateway">
                                <input type="radio" name="payment_type" id="pay_type_gateway" value="gateway" class="form-check-input mt-1" checked>
                                <div>
                                    <span class="fw-bold d-block">
                                        <i class="las la-credit-card text-primary me-1"></i> Direct Online Payment
                                    </span>
                                    <small class="text-muted">Pay directly via JazzCash, EasyPaisa, Cards, Stripe, etc.</small>
                                </div>
                            </label>
                        </div>

                        <!-- Wallet Balance Option -->
                        <div class="col-md-6">
                            <label class="payment-method-card border rounded-3 p-3 d-flex align-items-start gap-3 w-100 cursor-pointer h-100" for="pay_type_balance">
                                <input type="radio" name="payment_type" id="pay_type_balance" value="balance" class="form-check-input mt-1">
                                <div>
                                    <span class="fw-bold d-block">
                                        <i class="las la-wallet text-success me-1"></i> Wallet Balance
                                    </span>
                                    <small class="text-muted d-block">Available: <strong class="text-success">${{ showAmount(auth()->user()->balance) }}</strong></small>
                                    @if(auth()->user()->balance <= 0)
                                        <span class="badge bg-danger mt-1" style="font-size: 10px;">Insufficient Balance</span>
                                    @endif
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Gateway Selector Panel (Shown when Gateway is selected) -->
                    <div id="gatewaySelectSection" class="mt-3">
                        <label class="form-label fw-semibold">Select Payment Gateway:</label>
                        <select name="gateway_select" id="gatewaySelect" class="form-select mb-3">
                            <option value="">-- Choose Payment Gateway --</option>
                            @foreach($gatewayCurrency as $gate)
                                <option value="{{ $gate->method_code }}" 
                                        data-currency="{{ $gate->currency }}"
                                        data-min="{{ $gate->min_amount }}"
                                        data-max="{{ $gate->max_amount }}"
                                        data-fixed="{{ $gate->fixed_charge }}"
                                        data-percent="{{ $gate->percent_charge }}"
                                        data-rate="{{ $gate->rate }}">
                                    {{ __($gate->name) }} ({{ strtoupper($gate->currency) }})
                                </option>
                            @endforeach
                        </select>

                        <input type="hidden" name="gateway" id="selectedGateway">
                        <input type="hidden" name="currency" id="selectedCurrency">

                        <!-- Gateway Fee & Total Calculation Preview -->
                        <div id="gatewayCalculation" class="bg-light p-3 rounded-3 border d-none checkout-calc-card">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Plan Price:</span>
                                <span class="fw-semibold calc-base-price">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Gateway Processing Fee:</span>
                                <span class="fw-semibold calc-charge">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                <strong>Total Amount:</strong>
                                <strong class="text--base fs-5 calc-payable">$0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-1 calc-conversion-row d-none">
                                <span>In Gateway Currency:</span>
                                <span class="fw-bold calc-final-curr">0.00 USD</span>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Notice (Shown when Balance is selected) -->
                    <div id="balanceSection" class="mt-3 d-none">
                        <div class="alert alert-info d-flex align-items-center mb-0">
                            <i class="las la-info-circle fs-4 me-2"></i>
                            <div>
                                Amount will be instantly deducted from your wallet balance of <strong>${{ showAmount(auth()->user()->balance) }}</strong>. No gateway redirect required.
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitCheckoutBtn" class="btn btn--base px-4 fw-bold">
                        <i class="las la-lock me-1"></i> Proceed to Pay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
.cursor-pointer {
    cursor: pointer;
}
.payment-method-card {
    transition: all 0.2s ease;
    border-color: rgba(0, 0, 0, 0.12);
}
.payment-method-card:hover {
    border-color: #25d366 !important;
    background-color: rgba(37, 211, 102, 0.05);
}
.payment-method-card.selected-method {
    border-color: #25d366 !important;
    background-color: rgba(37, 211, 102, 0.08);
}

body.dark-mode .payment-method-card {
    border-color: rgba(255, 255, 255, 0.15) !important;
    background-color: #111b21 !important;
}
body.dark-mode .payment-method-card:hover,
body.dark-mode .payment-method-card.selected-method {
    border-color: #25d366 !important;
    background-color: rgba(37, 211, 102, 0.12) !important;
}
body.dark-mode .modal-content {
    background-color: #182229 !important;
    color: #e9edef !important;
}
body.dark-mode .modal-header,
body.dark-mode .modal-footer {
    background-color: #111b21 !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}
body.dark-mode .checkout-info-card,
body.dark-mode .checkout-calc-card {
    background-color: #111b21 !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}
</style>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";

        var currentPrice = 0;
        var userBalance = {{ (float) auth()->user()->balance }};

        // Open Modal when Subscribe Now is clicked
        $(document).on('click', '.btn-open-checkout', function() {
            var planId = $(this).data('id');
            var planName = $(this).data('name');
            currentPrice = parseFloat($(this).data('price')) || 0;
            var duration = $(this).data('duration');

            $('.modal-plan-name').text(planName);
            $('.modal-plan-duration').text(duration + ' Days Validity');
            $('.modal-plan-price').text('$' + currentPrice.toFixed(2));
            $('.calc-base-price').text('$' + currentPrice.toFixed(2));

            var actionUrl = "{{ url('user/plans/subscribe') }}/" + planId;
            $('#checkoutForm').attr('action', actionUrl);

            // Default to gateway payment
            $('#pay_type_gateway').prop('checked', true);
            togglePaymentMethod('gateway');

            // Select first gateway if none selected
            var $gateSelect = $('#gatewaySelect');
            if ($gateSelect.find('option').length > 1) {
                $gateSelect.prop('selectedIndex', 1).trigger('change');
            }

            $('#planCheckoutModal').modal('show');
        });

        // Payment Type Toggle
        $('input[name="payment_type"]').on('change', function() {
            togglePaymentMethod($(this).val());
        });

        function togglePaymentMethod(type) {
            $('.payment-method-card').removeClass('selected-method');
            if (type === 'gateway') {
                $('label[for="pay_type_gateway"]').addClass('selected-method');
                $('#gatewaySelectSection').removeClass('d-none');
                $('#balanceSection').addClass('d-none');
                $('#submitCheckoutBtn').html('<i class="las la-lock me-1"></i> Proceed to Pay').prop('disabled', false);
            } else {
                $('label[for="pay_type_balance"]').addClass('selected-method');
                $('#gatewaySelectSection').addClass('d-none');
                $('#balanceSection').removeClass('d-none');
                $('#submitCheckoutBtn').html('<i class="las la-check-circle me-1"></i> Confirm & Activate');

                if (userBalance < currentPrice) {
                    $('#submitCheckoutBtn').prop('disabled', true).text('Insufficient Balance');
                } else {
                    $('#submitCheckoutBtn').prop('disabled', false);
                }
            }
        }

        // Gateway Selection & Calculation
        $('#gatewaySelect').on('change', function() {
            var $selected = $(this).find('option:selected');
            var val = $selected.val();

            if (!val) {
                $('#gatewayCalculation').addClass('d-none');
                $('#selectedGateway').val('');
                $('#selectedCurrency').val('');
                return;
            }

            var currency = $selected.data('currency');
            var fixed = parseFloat($selected.data('fixed')) || 0;
            var percent = parseFloat($selected.data('percent')) || 0;
            var rate = parseFloat($selected.data('rate')) || 1;

            $('#selectedGateway').val(val);
            $('#selectedCurrency').val(currency);

            var charge = fixed + (currentPrice * percent / 100);
            var payable = currentPrice + charge;
            var finalCurrAmount = payable * rate;

            $('.calc-charge').text('$' + charge.toFixed(2));
            $('.calc-payable').text('$' + payable.toFixed(2));

            if (rate !== 1 || (currency && currency.toUpperCase() !== 'USD')) {
                $('.calc-conversion-row').removeClass('d-none');
                $('.calc-final-curr').text(finalCurrAmount.toFixed(2) + ' ' + (currency ? currency.toUpperCase() : ''));
            } else {
                $('.calc-conversion-row').addClass('d-none');
            }

            $('#gatewayCalculation').removeClass('d-none');
        });

        // Form Submit Validation
        $('#checkoutForm').on('submit', function(e) {
            var type = $('input[name="payment_type"]:checked').val();
            if (type === 'gateway') {
                if (!$('#gatewaySelect').val()) {
                    e.preventDefault();
                    alert('Please select a payment gateway first.');
                    return false;
                }
            } else if (type === 'balance') {
                if (userBalance < currentPrice) {
                    e.preventDefault();
                    alert('Insufficient wallet balance. Please choose direct online payment.');
                    return false;
                }
            }
        });

    })(jQuery);
</script>
@endpush

