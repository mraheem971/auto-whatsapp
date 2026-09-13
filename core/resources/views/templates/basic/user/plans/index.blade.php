@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="text-center mb-5">
            <h3 class="fw-bold mb-2">WhatsApp Bot Subscription Plans</h3>
            <p class="text-muted">Choose a plan that fits your business needs. Upgrade or renew anytime using your wallet balance.</p>
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
                                @else
                                    <form action="{{ route('user.plans.subscribe', $plan->id) }}" method="POST" onsubmit="return confirm('Subscribe to {{ $plan->name }} for {{ $plan->price > 0 ? '$' . showAmount($plan->price) : 'Free' }}?')">
                                        @csrf
                                        <button type="submit" class="btn {{ $plan->is_featured ? 'btn--base' : 'btn-outline--base' }} w-100 py-2 fw-bold">
                                            @if($plan->price == 0)
                                                Start Free Trial
                                            @else
                                                Subscribe Now
                                            @endif
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
