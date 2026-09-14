@extends('admin.layouts.app')
@section('panel')
    <div class="row gy-4">
        {{-- Stat Summary Widgets --}}
        @php
            $totalSubs = \App\Models\UserSubscription::count();
            $activeSubs = \App\Models\UserSubscription::where('status', 1)->where(function($q){
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->count();
            $expiredSubs = \App\Models\UserSubscription::where('expires_at', '<=', now())->count();
            $totalRevenue = \App\Models\UserSubscription::sum('paid_amount');
        @endphp

        <div class="col-xxl-3 col-sm-6">
            <div class="card bg--primary has-link overflow-hidden box--shadow2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4">
                            <i class="las la-users f-size--56 text-white"></i>
                        </div>
                        <div class="col-8 text-end">
                            <span class="text-white text--small">Total Subscriptions</span>
                            <h2 class="text-white">{{ $totalSubs }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6">
            <div class="card bg--success has-link overflow-hidden box--shadow2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4">
                            <i class="las la-check-circle f-size--56 text-white"></i>
                        </div>
                        <div class="col-8 text-end">
                            <span class="text-white text--small">Active Subscriptions</span>
                            <h2 class="text-white">{{ $activeSubs }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6">
            <div class="card bg--danger has-link overflow-hidden box--shadow2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4">
                            <i class="las la-history f-size--56 text-white"></i>
                        </div>
                        <div class="col-8 text-end">
                            <span class="text-white text--small">Expired Subscriptions</span>
                            <h2 class="text-white">{{ $expiredSubs }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6">
            <div class="card bg--info has-link overflow-hidden box--shadow2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4">
                            <i class="las la-wallet f-size--56 text-white"></i>
                        </div>
                        <div class="col-8 text-end">
                            <span class="text-white text--small">Total Plan Revenue</span>
                            <h2 class="text-white">{{ showAmount($totalRevenue) }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Subscriptions Table --}}
        <div class="col-12">
            <div class="card b-radius--10 shadow-sm border">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold"><i class="las la-list-alt text--primary me-1"></i> User Subscription Records</h5>
                    
                    <form action="{{ route('admin.plans.subscriptions') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                        <select name="status" class="form-select form-select-sm" style="width: 150px;" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <div class="input-group input-group-sm" style="width: 260px;">
                            <input type="text" name="search" class="form-control" placeholder="Search user or plan..." value="{{ request('search') }}">
                            <button class="btn btn--primary" type="submit"><i class="las la-search"></i></button>
                        </div>
                        @if(request('search') || request('status'))
                            <a href="{{ route('admin.plans.subscriptions') }}" class="btn btn-sm btn--dark" title="Clear Filters">
                                <i class="las la-times"></i>
                            </a>
                        @endif
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Subscribed Plan</th>
                                    <th>Paid Amount</th>
                                    <th>Started At</th>
                                    <th>Expires At</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscriptions as $sub)
                                    @php
                                        $isExpired = $sub->expires_at && $sub->expires_at <= now();
                                        $isActive = $sub->status == 1 && !$isExpired;
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($sub->user)
                                                <span class="fw-bold">{{ $sub->user->fullname }}</span>
                                                <br>
                                                <span class="small">
                                                    <a href="{{ route('admin.users.detail', $sub->user->id) }}">
                                                        <span>@</span>{{ $sub->user->username }}
                                                    </a>
                                                </span>
                                            @else
                                                <span class="text-muted">User Removed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sub->plan)
                                                <span class="badge badge--primary fw-bold px-2 py-1">
                                                    <i class="las la-crown me-1"></i> {{ $sub->plan->name }}
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ $sub->plan->account_limit }} Lines | {{ number_format($sub->plan->message_limit) }} Msgs</small>
                                            @else
                                                <span class="badge badge--dark">Plan Removed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text--dark">{{ showAmount($sub->paid_amount) }}</strong>
                                        </td>
                                        <td>
                                            {{ showDateTime($sub->starts_at, 'd M, Y h:i A') }}
                                            <br>
                                            <small class="text-muted">{{ diffForHumans($sub->starts_at) }}</small>
                                        </td>
                                        <td>
                                            @if($sub->expires_at)
                                                {{ showDateTime($sub->expires_at, 'd M, Y h:i A') }}
                                                <br>
                                                @if($isExpired)
                                                    <small class="text-danger fw-bold"><i class="las la-clock"></i> Expired {{ diffForHumans($sub->expires_at) }}</small>
                                                @else
                                                    <small class="text-success fw-bold"><i class="las la-clock"></i> Ends in {{ $sub->expires_at->diffForHumans(null, true) }}</small>
                                                @endif
                                            @else
                                                <span class="badge badge--success">Lifetime Access</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($isActive)
                                                <span class="badge badge--success"><i class="las la-check-circle me-1"></i> Active</span>
                                            @elseif($isExpired)
                                                <span class="badge badge--danger"><i class="las la-times-circle me-1"></i> Expired</span>
                                            @else
                                                <span class="badge badge--dark"><i class="las la-ban me-1"></i> Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sub->user)
                                                <button type="button" class="btn btn-sm btn-outline--primary assignModalBtn" 
                                                        data-user-id="{{ $sub->user_id }}" 
                                                        data-user-name="{{ $sub->user->username }}" 
                                                        data-plan-id="{{ $sub->plan_id }}"
                                                        title="Extend / Change Subscription">
                                                    <i class="las la-sync-alt me-1"></i> Manage
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center py-4" colspan="100%">
                                            <i class="las la-users f-size--36 text-muted mb-2 d-block"></i>
                                            No user subscriptions found matching your query.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($subscriptions->hasPages())
                    <div class="card-footer py-3">
                        {{ paginateLinks($subscriptions) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Assign / Extend Subscription Modal --}}
    <div class="modal fade" id="assignSubscriptionModal" tabindex="-1" aria-labelledby="assignSubscriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="assignSubscriptionModalLabel">
                        <i class="las la-user-tag text--primary me-1"></i> Assign / Extend User Subscription
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.plans.subscriptions.assign') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row gy-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Select User <span class="text-danger">*</span></label>
                                <select name="user_id" id="assign_user_id" class="form-select" required>
                                    <option value="">-- Choose User --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->fullname }} ({{ $u->username }} - {{ $u->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Select Plan <span class="text-danger">*</span></label>
                                <select name="plan_id" id="assign_plan_id" class="form-select" required>
                                    <option value="">-- Choose Plan --</option>
                                    @foreach($plans as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-duration="{{ $p->duration_days }}">
                                            {{ $p->name }} ({{ showAmount($p->price) }} / {{ $p->duration_days }} Days)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Custom Duration (Days)</label>
                                <input type="number" class="form-control" name="duration_days" id="assign_duration" placeholder="Leave empty for plan default" min="1">
                                <small class="text-muted">Defaults to plan duration</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Custom Paid ({{ gs('cur_text') }})</label>
                                <input type="number" step="any" class="form-control" name="paid_amount" id="assign_price" placeholder="0.00" min="0">
                                <small class="text-muted">Defaults to plan price</small>
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info py-2 px-3 mb-0 small">
                                    <i class="las la-info-circle me-1"></i> Note: Assigning this plan will mark previous active subscriptions for this user as upgraded/replaced and activate this plan immediately.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn--primary"><i class="las la-check-circle me-1"></i> Confirm & Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.plans.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-crown me-1"></i> Manage Plans & Pricing
    </a>
    <button type="button" class="btn btn-sm btn--primary" data-bs-toggle="modal" data-bs-target="#assignSubscriptionModal">
        <i class="las la-user-plus me-1"></i> Assign Subscription
    </button>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";

        // Auto populate duration and price when plan selected
        $('#assign_plan_id').on('change', function() {
            var selected = $(this).find(':selected');
            var duration = selected.data('duration');
            var price = selected.data('price');
            if (duration) $('#assign_duration').val(duration);
            if (price !== undefined) $('#assign_price').val(price);
        });

        // Open Modal from Table Row
        $('.assignModalBtn').on('click', function() {
            var userId = $(this).data('user-id');
            var planId = $(this).data('plan-id');

            $('#assign_user_id').val(userId).trigger('change');
            if (planId) {
                $('#assign_plan_id').val(planId).trigger('change');
            }

            var modal = new bootstrap.Modal(document.getElementById('assignSubscriptionModal'));
            modal.show();
        });
    })(jQuery);
</script>
@endpush
