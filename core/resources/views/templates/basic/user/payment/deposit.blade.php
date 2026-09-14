@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        @if(isset($selectedPlan) && $selectedPlan)
            <div class="alert alert-primary d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 p-3 shadow-sm border-0 rounded-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; font-size: 24px;">
                        <i class="las la-crown"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-white fw-bold">Direct Plan Subscription: {{ $selectedPlan->name }}</h5>
                        <small class="text-white-50">Select your preferred payment gateway below to complete payment and activate your plan automatically.</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-white text-dark fs-6 px-3 py-2 fw-bold shadow-sm">{{ showAmount($selectedPlan->price) }} {{ gs('cur_text') }} / {{ $selectedPlan->duration_days }} Days</span>
                </div>
            </div>
        @else
            <div class="text-center mb-4">
                <h3 class="fw-bold mb-1">Deposit Funds</h3>
                <p class="text-muted">Recharge your account wallet instantly using any of our verified secure payment methods.</p>
            </div>
        @endif

        <form class="deposit-form" action="{{ route('user.deposit.insert') }}" method="POST">
            @csrf
            <input name="currency" type="hidden" value="PKR">
            @if(isset($selectedPlan) && $selectedPlan)
                <input name="plan_id" type="hidden" value="{{ $selectedPlan->id }}">
            @endif

            <div class="row gy-4">
                {{-- Payment Gateway Selection Column --}}
                <div class="col-lg-7">
                    <div class="card custom--card border shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0 fw-bold fs-6 text-dark">
                                <i class="las la-wallet text-success me-1"></i> 1. Select Payment Method
                            </h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                {{ $gatewayCurrency->count() }} Available Gateways
                            </span>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <div class="gateway-list-grid d-flex flex-column gap-3">
                                @forelse ($gatewayCurrency as $data)
                                    <label class="gateway-card-item p-3 border rounded-3 d-flex align-items-center justify-content-between @if($loop->first) active-gateway border-success bg-light @endif" 
                                           for="gateway_{{ $data->id }}" 
                                           style="cursor: pointer; transition: all 0.2s ease;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input gateway-input" 
                                                       type="radio" 
                                                       name="gateway" 
                                                       id="gateway_{{ $data->id }}" 
                                                       value="{{ $data->method_code }}" 
                                                       data-gateway='@json($data)' 
                                                       data-min-amount="{{ showAmount($data->min_amount) }}" 
                                                       data-max-amount="{{ showAmount($data->max_amount) }}" 
                                                       @if(old('gateway')) @checked(old('gateway') == $data->method_code) @else @checked($loop->first) @endif>
                                            </div>
                                            <div class="gateway-thumb-wrap bg-white p-1 rounded border d-flex align-items-center justify-content-center shadow-xs" style="width: 60px; height: 45px;">
                                                <img src="{{ getImage(getFilePath('gateway') . '/' . $data->method->image) }}" 
                                                     alt="{{ $data->name }}" 
                                                     class="img-fluid" 
                                                     style="max-height: 38px; max-width: 52px; object-fit: contain;"
                                                     onerror="this.onerror=null; this.src='{{ asset('assets/images/default.png') }}';">
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ __($data->name) }}</h6>
                                                <small class="text-muted d-block">
                                                    Limit: {{ showAmount($data->min_amount) }} - {{ showAmount($data->max_amount) }} {{ $data->currency }}
                                                </small>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            @if($data->percent_charge > 0 || $data->fixed_charge > 0)
                                                <span class="badge bg-light text-dark border small">Fee: {{ showAmount($data->percent_charge) }}% + {{ showAmount($data->fixed_charge) }} {{ $data->currency }}</span>
                                            @else
                                                <span class="badge bg-success text-white small fw-normal">Zero Fee</span>
                                            @endif
                                        </div>
                                    </label>
                                @empty
                                    <div class="alert alert-warning text-center">
                                        <i class="las la-exclamation-triangle fs-4 d-block mb-2"></i>
                                        No active payment methods currently available. Please contact administrator.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Amount & Payment Calculation Column --}}
                <div class="col-lg-5">
                    <div class="card custom--card border shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0 fw-bold fs-6 text-dark">
                                <i class="las la-calculator text-primary me-1"></i> 2. Payment Summary
                            </h5>
                            <span class="badge bg-light text-muted border small">
                                Balance: {{ gs('cur_sym') }}{{ showAmount(auth()->user()->balance) }}
                            </span>
                        </div>
                        <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                            <div>
                                {{-- Amount Input --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark small mb-1">
                                        @if(isset($selectedPlan) && $selectedPlan)
                                            Plan Price ({{ gs('cur_text') }})
                                        @else
                                            Enter Deposit Amount ({{ gs('cur_text') }})
                                        @endif
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light fw-bold text-muted border-end-0">{{ gs('cur_sym') }}</span>
                                        @if(isset($selectedPlan) && $selectedPlan)
                                            <input type="number" step="any" class="form-control fw-bold fs-4 amount border-start-0" name="amount" value="{{ $selectedPlan->price }}" readonly required>
                                        @elseif(session()->get('requestAmount'))
                                            <input type="number" step="any" class="form-control fw-bold fs-4 amount border-start-0" name="amount" value="{{ session()->get('requestAmount') }}" readonly required>
                                        @else
                                            <input type="number" step="any" class="form-control fw-bold fs-4 amount border-start-0" name="amount" value="{{ old('amount', request('amount', 1000)) }}" placeholder="1000" autocomplete="off" required>
                                        @endif
                                    </div>
                                    <div class="mt-1 d-flex justify-content-between align-items-center">
                                        <small class="text-muted gateway-limit-text">Limit: <span class="gateway-limit fw-bold text-dark">1.00 - 500,000.00</span> {{ gs('cur_text') }}</small>
                                        <small class="limit-status-badge text-success fw-semibold"><i class="las la-check-circle"></i> In Limit</small>
                                    </div>
                                </div>

                                {{-- Quick Presets for normal deposit --}}
                                @if(!isset($selectedPlan) || !$selectedPlan)
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Quick Select Amount:</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach([500, 1000, 2500, 5000, 10000] as $preset)
                                                <button type="button" class="btn btn-sm btn-outline-secondary quick-amt-btn px-2 py-1" data-amount="{{ $preset }}">
                                                    +{{ number_format($preset) }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Calculations Breakdown --}}
                                <div class="bg-light p-3 rounded-3 mb-4 border">
                                    <div class="d-flex justify-content-between mb-2 small">
                                        <span class="text-muted">Deposit Amount:</span>
                                        <span class="fw-bold text-dark"><span class="calc-deposit-amt">0.00</span> {{ gs('cur_text') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 small">
                                        <span class="text-muted">Processing Fee:</span>
                                        <span class="fw-bold text-dark"><span class="processing-fee">0.00</span> {{ gs('cur_text') }}</span>
                                    </div>
                                    <div class="gateway-conversion d-none mb-2 small">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Conversion Rate:</span>
                                            <span class="conversion-rate-text fw-bold text-dark"></span>
                                        </div>
                                    </div>
                                    <div class="conversion-currency d-none mb-2 small">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Payable in <span class="gateway-currency-name">PKR</span>:</span>
                                            <span class="fw-bold text-primary in-currency-amount">0.00</span>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-dark">Total Payable:</span>
                                        <h5 class="fw-bold text-success mb-0">
                                            <span class="final-amount">0.00</span> {{ gs('cur_text') }}
                                        </h5>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button class="btn btn--base w-100 py-3 fw-bold fs-6 shadow-sm deposit-submit-btn" type="submit">
                                    @if(isset($selectedPlan) && $selectedPlan)
                                        <i class="las la-check-circle me-1"></i> Pay {{ showAmount($selectedPlan->price) }} {{ gs('cur_text') }} & Activate Plan
                                    @else
                                        <i class="las la-arrow-circle-right me-1"></i> Proceed to Pay (<span class="btn-final-amt">0.00</span> {{ gs('cur_text') }})
                                    @endif
                                </button>
                                
                                <div class="d-flex align-items-center justify-content-center gap-3 mt-3 text-muted small">
                                    <span><i class="las la-lock text-success"></i> 256-bit Secure</span>
                                    <span>•</span>
                                    <span><i class="las la-bolt text-warning"></i> Instant Verification</span>
                                    <span>•</span>
                                    <span><i class="las la-headset text-primary"></i> 24/7 Support</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@push('style')
<style>
    .gateway-card-item {
        background-color: #ffffff;
        border: 2px solid #e2e8f0 !important;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .gateway-card-item:hover {
        border-color: #10b981 !important;
        background-color: #f8fafc;
        transform: translateY(-2px);
    }
    .gateway-card-item.active-gateway {
        border-color: #10b981 !important;
        background-color: #f0fdf4 !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    }
    .quick-amt-btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.15s;
    }
    .quick-amt-btn:hover {
        background-color: #10b981;
        border-color: #10b981;
        color: #fff;
    }
</style>
@endpush

@push('script')
<script>
    "use strict";
    (function($) {
        let amount = parseFloat($('.amount').val() || 0);
        let gateway, minAmount, maxAmount;

        // Quick amount buttons
        $('.quick-amt-btn').on('click', function() {
            let addVal = parseFloat($(this).data('amount'));
            $('.amount').val(addVal).trigger('input');
        });

        // Amount input listener
        $('.amount').on('input', function() {
            amount = parseFloat($(this).val()) || 0;
            calculation();
        });

        // Gateway radio item change
        $('.gateway-input').on('change', function() {
            $('.gateway-card-item').removeClass('active-gateway border-success bg-light');
            $(this).closest('.gateway-card-item').addClass('active-gateway border-success bg-light');
            gatewayChange();
        });

        // Allow clicking anywhere on card
        $('.gateway-card-item').on('click', function(e) {
            let radio = $(this).find('.gateway-input');
            if (!radio.is(':checked')) {
                radio.prop('checked', true).trigger('change');
            }
        });

        function gatewayChange() {
            let gatewayElement = $('.gateway-input:checked');
            if (!gatewayElement.length) {
                gatewayElement = $('.gateway-input').first();
                gatewayElement.prop('checked', true);
            }

            gateway = gatewayElement.data('gateway');
            minAmount = parseFloat(gatewayElement.data('min-amount')) || 1;
            maxAmount = parseFloat(gatewayElement.data('max-amount')) || 1000000;

            calculation();
        }

        function calculation() {
            if (!gateway) return;

            $(".gateway-limit").text(minAmount.toFixed(2) + " - " + maxAmount.toFixed(2));
            $("input[name=currency]").val(gateway.currency);
            $(".gateway-currency-name").text(gateway.currency);
            $(".calc-deposit-amt").text(amount.toFixed(2));

            let percentCharge = parseFloat(gateway.percent_charge || 0);
            let fixedCharge = parseFloat(gateway.fixed_charge || 0);
            let totalPercentCharge = 0;

            if (amount > 0) {
                totalPercentCharge = (amount * percentCharge) / 100;
            }

            let totalCharge = totalPercentCharge + fixedCharge;
            let totalAmount = amount + totalCharge;

            $(".processing-fee").text(totalCharge.toFixed(2));
            $(".final-amount").text(totalAmount.toFixed(2));
            $(".btn-final-amt").text(totalAmount.toFixed(2));

            // Validate Limit
            let isValid = (amount >= minAmount && amount <= maxAmount);
            if (isValid) {
                $('.limit-status-badge').html('<i class="las la-check-circle"></i> In Limit').removeClass('text-danger').addClass('text-success');
                $('.deposit-submit-btn').removeAttr('disabled').removeClass('opacity-50');
            } else {
                if (amount === 0) {
                    $('.limit-status-badge').html('<i class="las la-info-circle"></i> Enter amount').removeClass('text-success').addClass('text-muted');
                } else if (amount < minAmount) {
                    $('.limit-status-badge').html('<i class="las la-exclamation-circle"></i> Min: ' + minAmount.toFixed(2)).removeClass('text-success').addClass('text-danger');
                } else {
                    $('.limit-status-badge').html('<i class="las la-exclamation-circle"></i> Max: ' + maxAmount.toFixed(2)).removeClass('text-success').addClass('text-danger');
                }
                $('.deposit-submit-btn').attr('disabled', true).addClass('opacity-50');
            }

            // Currency conversion if gateway currency != main currency
            let curText = "{{ gs('cur_text') }}";
            if (gateway.currency !== curText && gateway.method && gateway.method.crypto != 1) {
                $(".gateway-conversion, .conversion-currency").removeClass('d-none');
                let rate = parseFloat(gateway.rate || 1);
                $(".conversion-rate-text").text(`1 ${curText} = ${rate.toFixed(2)} ${gateway.currency}`);
                $(".in-currency-amount").text((totalAmount * rate).toFixed(2) + ' ' + gateway.currency);
            } else {
                $(".gateway-conversion, .conversion-currency").addClass('d-none');
            }
        }

        // Initialize on load
        gatewayChange();
        if (amount > 0) {
            calculation();
        }
    })(jQuery);
</script>
@endpush
