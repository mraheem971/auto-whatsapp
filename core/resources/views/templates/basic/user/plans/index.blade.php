@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="text-center mb-4">
            <h3 class="fw-bold mb-2">WhatsApp Bot SaaS Subscription Plans</h3>
            <p class="text-muted">Scale your WhatsApp marketing & auto-reply bots with high-volume accounts and anti-ban automation.</p>
            @if($currentPlan)
                <div class="d-inline-flex align-items-center gap-2 bg-light px-3 py-2 rounded-pill border shadow-xs mb-3">
                    <span class="text-muted small">Current Active Plan:</span>
                    <strong class="text-success fw-bold">{{ $currentPlan->name }}</strong>
                    @if($activeSubscription && $activeSubscription->expires_at)
                        <span class="badge bg-secondary small">Expires {{ showDateTime($activeSubscription->expires_at) }}</span>
                    @endif
                </div>
            @endif

            {{-- Billing Cycle Filter Tabs --}}
            <div class="d-flex justify-content-center align-items-center gap-2 mt-3">
                <div class="btn-group p-1 bg-light border rounded-pill shadow-xs" role="group" id="billingFilterGroup">
                    <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold billing-filter active" data-filter="all">
                        All Plans
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold billing-filter" data-filter="monthly">
                        Monthly Plans
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold billing-filter position-relative" data-filter="yearly">
                        Yearly Plans
                        <span class="badge bg-danger rounded-pill ms-1" style="font-size: 10px;">SAVE 20%</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="row gy-4 justify-content-center" id="plansContainer">
            @foreach($plans as $plan)
                @php
                    $isYearly = $plan->duration_days >= 300;
                    $isMonthly = $plan->duration_days >= 25 && $plan->duration_days < 300;
                    $isTrial = $plan->duration_days < 25;
                    $filterClass = $isYearly ? 'plan-yearly' : ($isMonthly ? 'plan-monthly' : 'plan-trial');
                @endphp
                <div class="col-lg-4 col-md-6 plan-card-item {{ $filterClass }}">
                    <div class="card custom--card border shadow-sm rounded-3 h-100 position-relative {{ $plan->is_featured ? 'border-primary' : '' }}">
                        @if($plan->is_featured)
                            <div class="position-absolute top-0 end-0 bg-primary text-white small fw-bold px-3 py-1 text-uppercase shadow-xs" style="border-bottom-left-radius: 8px; border-top-right-radius: 8px;">
                                Most Popular
                            </div>
                        @elseif($isYearly)
                            <div class="position-absolute top-0 end-0 bg-danger text-white small fw-bold px-3 py-1 text-uppercase shadow-xs" style="border-bottom-left-radius: 8px; border-top-right-radius: 8px;">
                                Best Value (Annual)
                            </div>
                        @endif

                        <div class="card-body p-4 text-center d-flex flex-column">
                            <div class="mb-2">
                                <h5 class="fw-bold text-dark mb-1">{{ $plan->name }}</h5>
                                <p class="text-muted small mb-0">{{ $plan->tagline }}</p>
                            </div>

                            <div class="my-3 py-3 bg-light rounded-3 border">
                                <h2 class="fw-bold text-dark mb-0">
                                    @if($plan->price == 0)
                                        Free
                                    @else
                                        {{ showAmount($plan->price) }}
                                    @endif
                                </h2>
                                <span class="badge {{ $isYearly ? 'bg-danger' : ($isMonthly ? 'bg-primary' : 'bg-secondary') }} text-white small mt-1">
                                    @if($isYearly)
                                        <i class="las la-calendar-check me-1"></i> Annual Plan ({{ $plan->duration_days }} Days)
                                    @elseif($isMonthly)
                                        <i class="las la-calendar me-1"></i> Monthly Plan ({{ $plan->duration_days }} Days)
                                    @else
                                        <i class="las la-stopwatch me-1"></i> Trial ({{ $plan->duration_days }} Days)
                                    @endif
                                </span>
                            </div>

                            <ul class="list-unstyled text-start my-3 flex-grow-1">
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

                            <div class="mt-auto pt-3 border-top">
                                @if($currentPlan && $currentPlan->id == $plan->id && $activeSubscription && $activeSubscription->isValid())
                                    <button class="btn btn-secondary w-100 py-2 disabled" disabled>
                                        <i class="las la-check me-1"></i> Currently Active
                                    </button>
                                @elseif($plan->price == 0)
                                    <form action="{{ route('user.plans.subscribe', $plan->id) }}" method="POST" onsubmit="return confirm('Start Free Trial for {{ $plan->name }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline--base w-100 py-2 fw-bold">
                                            <i class="las la-rocket me-1"></i> Start Free Trial
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn {{ $plan->is_featured ? 'btn--base' : ($isYearly ? 'btn-danger' : 'btn-outline--base') }} w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#subscribeModal_{{ $plan->id }}">
                                        <i class="las la-bolt me-1"></i> Subscribe Now
                                    </button>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Subscription & Checkout Modal for Paid Plans --}}
                @if($plan->price > 0)
                    <div class="modal fade" id="subscribeModal_{{ $plan->id }}" tabindex="-1" aria-labelledby="subscribeModalLabel_{{ $plan->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header border-bottom bg-light py-3">
                                    <h5 class="modal-title fw-bold text-dark fs-6" id="subscribeModalLabel_{{ $plan->id }}">
                                        <i class="las la-shopping-cart text-success me-1"></i> Subscribe to {{ $plan->name }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-3 border">
                                        <div>
                                            <span class="text-muted small d-block">Plan Price:</span>
                                            <h4 class="fw-bold text-dark mb-0">{{ showAmount($plan->price) }}</h4>
                                            <small class="text-muted">Duration: {{ $plan->duration_days }} Days</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted small d-block">Your Wallet Balance:</span>
                                            <h5 class="fw-bold {{ auth()->user()->balance >= $plan->price ? 'text-success' : 'text-danger' }} mb-0">
                                                {{ showAmount(auth()->user()->balance) }}
                                            </h5>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-3 small text-uppercase">Choose Payment Method:</h6>

                                    <div class="d-flex flex-column gap-3">
                                        {{-- Option 1: Wallet Balance --}}
                                        <div class="border rounded-3 p-3 {{ auth()->user()->balance >= $plan->price ? 'border-success bg-light' : 'opacity-75' }}">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="las la-wallet text-success fs-4"></i>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">Option 1: Pay with Wallet Balance</h6>
                                                        <small class="text-muted">Instant activation from your existing balance</small>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(auth()->user()->balance >= $plan->price)
                                                <form action="{{ route('user.plans.subscribe', $plan->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100 py-2 mt-2 fw-bold">
                                                        <i class="las la-check-circle me-1"></i> Confirm & Pay {{ showAmount($plan->price) }} from Wallet
                                                    </button>
                                                </form>
                                            @else
                                                <div class="alert alert-warning py-2 px-3 mb-0 mt-2 small d-flex align-items-center justify-content-between">
                                                    <span><i class="las la-exclamation-circle"></i> Insufficient balance (Need {{ showAmount($plan->price - auth()->user()->balance) }} more)</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Option 2: Direct Payment Gateway --}}
                                        <div class="border rounded-3 p-3 border-primary" style="background: rgba(59, 130, 246, 0.03);">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="las la-credit-card text-primary fs-4"></i>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">Option 2: Direct Payment Gateway</h6>
                                                        <small class="text-muted">EasyPaisa, JazzCash, Raast, USDT, Stripe</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <a href="{{ route('user.deposit.index', ['plan_id' => $plan->id]) }}" class="btn btn--base w-100 py-2 mt-2 fw-bold">
                                                <i class="las la-bolt me-1"></i> Pay Directly via Gateway & Activate
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

    </div>
</div>
@endsection

@push('script')
<script>
    (function($) {
        "use strict";

        $('.billing-filter').on('click', function() {
            $('.billing-filter').removeClass('active btn--base text-white').addClass('btn-light text-dark');
            $(this).removeClass('btn-light text-dark').addClass('active btn--base text-white');

            var filter = $(this).data('filter');
            if (filter === 'all') {
                $('.plan-card-item').fadeIn(200);
            } else if (filter === 'monthly') {
                $('.plan-card-item').hide();
                $('.plan-monthly, .plan-trial').fadeIn(200);
            } else if (filter === 'yearly') {
                $('.plan-card-item').hide();
                $('.plan-yearly').fadeIn(200);
            }
        });

        // Set initial active button styling
        $('.billing-filter.active').addClass('btn--base text-white');
    })(jQuery);
</script>
@endpush
