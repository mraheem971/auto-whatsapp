@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="text-center mb-5">
            <h3 class="fw-bold mb-2">WhatsApp Bot Subscription Plans</h3>
            <p class="text-muted">Choose a plan that fits your business needs. Subscribe directly using your wallet balance or online payment gateway.</p>
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
                            <div class="position-absolute top-0 end-0 bg-primary text-white small fw-bold px-3 py-1 rounded-bl-3 text-uppercase" style="border-bottom-left-radius: 8px; border-top-right-radius: 8px;">
                                Most Popular
                            </div>
                        @endif

                        <div class="card-body p-4 text-center d-flex flex-column">
                            <h5 class="fw-bold text-dark mb-1">{{ $plan->name }}</h5>
                            <p class="text-muted small mb-3">{{ $plan->tagline }}</p>

                            <div class="my-3 py-3 bg-light rounded">
                                <h2 class="fw-bold text-dark mb-0">
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
                                    <span><strong>{{ $plan->campaign_limit }}</strong> Marketing Campaigns</span>
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
                                @elseif($plan->price <= 0)
                                    <form action="{{ route('user.plans.subscribe', $plan->id) }}" method="POST" onsubmit="return confirm('Activate {{ $plan->name }} for Free?')">
                                        @csrf
                                        <button type="submit" class="btn btn--base w-100 py-2 fw-bold">
                                            <i class="las la-bolt me-1"></i> Start Free Plan
                                        </button>
                                    </form>
                                @else
                                    <button type="button" 
                                            class="btn {{ $plan->is_featured ? 'btn--base' : 'btn-outline--base' }} w-100 py-2 fw-bold open-checkout-modal"
                                            data-id="{{ $plan->id }}"
                                            data-name="{{ $plan->name }}"
                                            data-price="{{ showAmount($plan->price) }}"
                                            data-rawprice="{{ $plan->price }}"
                                            data-duration="{{ $plan->duration_days }}"
                                            data-url="{{ route('user.plans.subscribe', $plan->id) }}">
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

<!-- Direct Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white fw-bold" id="checkoutModalLabel">
                    <i class="las la-shield-alt text-success me-1"></i> Plan Checkout & Subscription
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="checkoutForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    
                    <!-- Plan Info Banner -->
                    <div class="d-flex align-items-center justify-content-between p-3 mb-4 rounded bg-light border">
                        <div>
                            <span class="text-muted small d-block">Selected Plan</span>
                            <h5 class="fw-bold text-dark mb-0" id="modalPlanName">Pro Plan</h5>
                            <small class="text-muted" id="modalPlanDuration">30 Days Validity</small>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small d-block">Total Payable</span>
                            <h4 class="fw-bold text-success mb-0" id="modalPlanPrice">$0.00</h4>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-3"><i class="las la-wallet me-1"></i> Choose Payment Method</h6>

                    <!-- Option 1: Wallet Balance -->
                    @php
                        $userBalance = auth()->user()->balance;
                    @endphp
                    <div class="form-check p-3 mb-3 border rounded payment-method-card {{ $userBalance > 0 ? '' : 'bg-light text-muted' }}" style="cursor: pointer;">
                        <input class="form-check-input ms-0 me-2" type="radio" name="payment_type" id="payWallet" value="wallet" checked>
                        <label class="form-check-label w-100 ms-1" for="payWallet" style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-success bg-opacity-10 text-success p-2 rounded">
                                        <i class="las la-wallet fs-4"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block text-dark">User Wallet Balance</strong>
                                        <small class="text-muted">Available: <strong>${{ showAmount($userBalance) }}</strong></small>
                                    </div>
                                </div>
                                <div id="walletBadge">
                                    <!-- Dynamic sufficient / insufficient badge -->
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Option 2: Direct Payment Gateways -->
                    <div class="form-check p-3 border rounded payment-method-card" style="cursor: pointer;">
                        <input class="form-check-input ms-0 me-2" type="radio" name="payment_type" id="payGateway" value="gateway">
                        <label class="form-check-label w-100 ms-1" for="payGateway" style="cursor: pointer;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded">
                                    <i class="las la-credit-card fs-4"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-dark">Direct Online Payment Gateway</strong>
                                    <small class="text-muted">Cards, EasyPaisa, JazzCash, Raast, Crypto</small>
                                </div>
                            </div>
                        </label>

                        <!-- Gateway Selection Dropdown (Expanded when selected) -->
                        <div id="gatewaySelectContainer" class="mt-3 pt-3 border-top" style="display: none;">
                            <label class="form-label small fw-bold text-dark">Select Payment Gateway</label>
                            <select name="gateway_currency_select" id="gatewaySelect" class="form-select">
                                <option value="">-- Select Payment Method --</option>
                                @foreach($gatewayCurrency as $gate)
                                    <option value="{{ $gate->method_code }}" 
                                            data-currency="{{ $gate->currency }}" 
                                            data-min="{{ $gate->min_amount }}" 
                                            data-max="{{ $gate->max_amount }}">
                                        {{ $gate->name }} ({{ $gate->currency }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="gateway" id="selectedGateway" value="">
                            <input type="hidden" name="currency" id="selectedCurrency" value="">
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base fw-bold px-4" id="confirmCheckoutBtn">
                        <i class="las la-lock me-1"></i> Complete Subscription
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script>
(function($) {
    "use strict";

    const userBalance = {{ (float)$userBalance }};
    let currentPlanRawPrice = 0;

    $('.open-checkout-modal').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const price = $(this).data('price');
        const rawPrice = parseFloat($(this).data('rawprice'));
        const duration = $(this).data('duration');
        const url = $(this).data('url');

        currentPlanRawPrice = rawPrice;

        $('#modalPlanName').text(name);
        $('#modalPlanPrice').text('$' + price);
        $('#modalPlanDuration').text(duration + ' Days Validity');
        $('#checkoutForm').attr('action', url);

        // Check balance sufficiency
        if (userBalance >= rawPrice) {
            $('#walletBadge').html('<span class="badge bg-success small"><i class="las la-check"></i> Sufficient</span>');
            $('#payWallet').prop('checked', true);
            $('#gatewaySelectContainer').slideUp();
        } else {
            $('#walletBadge').html('<span class="badge bg-danger small">Insufficient ($' + (rawPrice - userBalance).toFixed(2) + ' short)</span>');
            $('#payGateway').prop('checked', true);
            $('#gatewaySelectContainer').slideDown();
        }

        // Auto select first gateway if none selected
        if (!$('#gatewaySelect').val()) {
            $('#gatewaySelect option:eq(1)').prop('selected', true).trigger('change');
        }

        $('#checkoutModal').modal('show');
    });

    $('input[name="payment_type"]').on('change', function() {
        if ($(this).val() === 'gateway') {
            $('#gatewaySelectContainer').slideDown();
            $('#gatewaySelect').trigger('change');
        } else {
            $('#gatewaySelectContainer').slideUp();
        }
    });

    $('#gatewaySelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const methodCode = $(this).val();
        const currency = selectedOption.data('currency');

        $('#selectedGateway').val(methodCode);
        $('#selectedCurrency').val(currency);
    });

    $('#checkoutForm').on('submit', function(e) {
        const paymentType = $('input[name="payment_type"]:checked').val();

        if (paymentType === 'wallet') {
            if (userBalance < currentPlanRawPrice) {
                e.preventDefault();
                alert('Your wallet balance is insufficient. Please choose an Online Payment Gateway.');
                $('#payGateway').prop('checked', true).trigger('change');
                return false;
            }
        } else if (paymentType === 'gateway') {
            if (!$('#selectedGateway').val()) {
                e.preventDefault();
                alert('Please select a payment gateway to proceed.');
                return false;
            }
        }
    });

})(jQuery);
</script>
@endpush
@endsection

