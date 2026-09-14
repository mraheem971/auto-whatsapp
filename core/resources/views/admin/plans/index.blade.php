@extends('admin.layouts.app')
@section('panel')
    <div class="row gy-4">
        {{-- Top Action Bar --}}
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-0 fw-bold"><i class="las la-crown text--warning me-1"></i> WhatsApp SaaS Plans & Pricing Setting</h5>
                    <small class="text-muted">Create and manage pricing tiers, quotas, anti-ban limits, and feature rules for your users.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.plans.subscriptions') }}" class="btn btn-outline--primary">
                        <i class="las la-users me-1"></i> User Subscriptions
                    </a>
                    <button type="button" class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                        <i class="las la-plus-circle me-1"></i> Add New Plan
                    </button>
                </div>
            </div>
        </div>

        {{-- Plan Cards Grid Preview --}}
        @foreach($plans as $plan)
            <div class="col-xl-4 col-md-6">
                <div class="card b-radius--10 shadow-sm border h-100 position-relative {{ $plan->is_featured ? 'border--primary' : '' }}">
                    @if($plan->is_featured)
                        <span class="badge badge--primary position-absolute top-0 end-0 m-3 px-2 py-1 text-uppercase">
                            <i class="las la-star me-1"></i> Most Popular
                        </span>
                    @endif

                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h4 class="fw-bold mb-0 text--dark">{{ $plan->name }}</h4>
                                @if($plan->status == 1)
                                    <span class="badge badge--success"><i class="las la-check-circle me-1"></i> Active</span>
                                @else
                                    <span class="badge badge--dark"><i class="las la-times-circle me-1"></i> Inactive</span>
                                @endif
                            </div>
                            <p class="text-muted small mb-3">{{ $plan->tagline ?: 'No tagline provided' }}</p>

                            <div class="bg--light p-3 rounded text-center my-3 border">
                                <h2 class="fw-bold text--primary mb-0">
                                    @if($plan->price == 0)
                                        Free
                                    @else
                                        {{ showAmount($plan->price) }}
                                    @endif
                                </h2>
                                <span class="text-muted small">Valid for {{ $plan->duration_days }} Days</span>
                            </div>

                            <ul class="list-group list-group-flush mb-4 small">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span><i class="lab la-whatsapp text--success me-2"></i> WhatsApp Accounts:</span>
                                    <strong class="text--dark">{{ $plan->account_limit }} Line(s)</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span><i class="las la-robot text--primary me-2"></i> Keyword Auto-Bots:</span>
                                    <strong class="text--dark">{{ $plan->autoreply_limit }} Bots</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span><i class="las la-envelope-open-text text--info me-2"></i> Message Templates:</span>
                                    <strong class="text--dark">{{ $plan->template_limit }} Templates</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span><i class="las la-bullhorn text--warning me-2"></i> Marketing Campaigns:</span>
                                    <strong class="text--dark">{{ $plan->campaign_limit }} Campaigns</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span><i class="las la-paper-plane text--secondary me-2"></i> Outgoing Message Quota:</span>
                                    <strong class="text--dark">{{ number_format($plan->message_limit) }} Messages</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 bg-light rounded px-2 mt-2">
                                    <span><i class="las la-users text--primary me-2"></i> Active Subscribers:</span>
                                    <span class="badge badge--info">{{ $plan->subscriptions_count }} Users</span>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2 pt-3 border-top">
                            <button type="button" class="btn btn-outline--primary flex-grow-1 editPlanBtn" 
                                    data-plan='@json($plan)' 
                                    data-action="{{ route('admin.plans.update', $plan->id) }}">
                                <i class="las la-edit me-1"></i> Edit Plan
                            </button>
                            
                            <form action="{{ route('admin.plans.status', $plan->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn {{ $plan->status == 1 ? 'btn-outline--warning' : 'btn-outline--success' }}" title="{{ $plan->status == 1 ? 'Deactivate' : 'Activate' }}">
                                    <i class="las {{ $plan->status == 1 ? 'la-eye-slash' : 'la-eye' }}"></i>
                                </button>
                            </form>

                            <button type="button" class="btn btn-outline--danger deletePlanBtn" 
                                    data-id="{{ $plan->id }}" 
                                    data-name="{{ $plan->name }}" 
                                    data-action="{{ route('admin.plans.delete', $plan->id) }}">
                                <i class="las la-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if($plans->isEmpty())
            <div class="col-12">
                <div class="card p-5 text-center shadow-sm border">
                    <i class="las la-crown text--warning" style="font-size: 48px;"></i>
                    <h5 class="mt-3">No Subscription Plans Found</h5>
                    <p class="text-muted">Click the "Add New Plan" button above to create your first pricing tier.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Add Plan Modal --}}
    <div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addPlanModalLabel">
                        <i class="las la-plus-circle text--primary me-1"></i> Create New Subscription Plan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.plans.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Plan Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="e.g. Starter, Pro Business, VIP Agency" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tagline / Short Description</label>
                                <input type="text" class="form-control" name="tagline" placeholder="e.g. Perfect for growing e-commerce stores">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Price ({{ gs('cur_text') }}) <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control" name="price" placeholder="0.00 (0 for Free Trial)" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Duration (in Days) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="duration_days" placeholder="30 (e.g. 14, 30, 365)" min="1" required>
                            </div>

                            <div class="col-12"><hr class="my-2"><h6 class="fw-bold text--primary">SaaS Quotas & Feature Limits</h6></div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">WhatsApp Accounts</label>
                                <input type="number" class="form-control" name="account_limit" value="1" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Auto-Reply Bots</label>
                                <input type="number" class="form-control" name="autoreply_limit" value="5" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Message Templates</label>
                                <input type="number" class="form-control" name="template_limit" value="5" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marketing Campaigns</label>
                                <input type="number" class="form-control" name="campaign_limit" value="2" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Outgoing Messages Quota</label>
                                <input type="number" class="form-control" name="message_limit" value="1000" min="0" required>
                            </div>

                            <div class="col-12"><hr class="my-2"><h6 class="fw-bold text--primary">Custom Bullet Features</h6></div>

                            <div class="col-12">
                                <div id="addFeatureWrapper" class="d-flex flex-column gap-2 mb-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="features[]" placeholder="e.g. Anti-Ban Human Behavior Engine">
                                        <button class="btn btn--danger removeFeatureBtn" type="button"><i class="las la-times"></i></button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline--primary" id="addMoreFeatureBtn">
                                    <i class="las la-plus me-1"></i> Add Bullet Point
                                </button>
                            </div>

                            <div class="col-12"><hr class="my-2"></div>

                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="add_is_featured" value="1">
                                    <label class="form-check-label fw-bold" for="add_is_featured">
                                        Highlight as "Most Popular"
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="status" id="add_status" value="1" checked>
                                    <label class="form-check-label fw-bold" for="add_status">
                                        Status: Active / Publish
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn--primary"><i class="las la-save me-1"></i> Create Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Plan Modal --}}
    <div class="modal fade" id="editPlanModal" tabindex="-1" aria-labelledby="editPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editPlanModalLabel">
                        <i class="las la-edit text--primary me-1"></i> Edit Subscription Plan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPlanForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Plan Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tagline / Short Description</label>
                                <input type="text" class="form-control" name="tagline" id="edit_tagline">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Price ({{ gs('cur_text') }}) <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control" name="price" id="edit_price" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Duration (in Days) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="duration_days" id="edit_duration_days" min="1" required>
                            </div>

                            <div class="col-12"><hr class="my-2"><h6 class="fw-bold text--primary">SaaS Quotas & Feature Limits</h6></div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">WhatsApp Accounts</label>
                                <input type="number" class="form-control" name="account_limit" id="edit_account_limit" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Auto-Reply Bots</label>
                                <input type="number" class="form-control" name="autoreply_limit" id="edit_autoreply_limit" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Message Templates</label>
                                <input type="number" class="form-control" name="template_limit" id="edit_template_limit" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marketing Campaigns</label>
                                <input type="number" class="form-control" name="campaign_limit" id="edit_campaign_limit" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Outgoing Messages Quota</label>
                                <input type="number" class="form-control" name="message_limit" id="edit_message_limit" min="0" required>
                            </div>

                            <div class="col-12"><hr class="my-2"><h6 class="fw-bold text--primary">Custom Bullet Features</h6></div>

                            <div class="col-12">
                                <div id="editFeatureWrapper" class="d-flex flex-column gap-2 mb-2"></div>
                                <button type="button" class="btn btn-sm btn-outline--primary" id="editAddMoreFeatureBtn">
                                    <i class="las la-plus me-1"></i> Add Bullet Point
                                </button>
                            </div>

                            <div class="col-12"><hr class="my-2"></div>

                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="edit_is_featured" value="1">
                                    <label class="form-check-label fw-bold" for="edit_is_featured">
                                        Highlight as "Most Popular"
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="status" id="edit_status" value="1">
                                    <label class="form-check-label fw-bold" for="edit_status">
                                        Status: Active / Publish
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn--primary"><i class="las la-save me-1"></i> Update Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deletePlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger"><i class="las la-exclamation-triangle me-1"></i> Confirm Delete Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deletePlanForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-0">Are you sure you want to permanently delete plan <strong id="deletePlanName"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn--danger"><i class="las la-trash me-1"></i> Delete Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    (function($) {
        "use strict";

        // Dynamic Feature row adder for Add Modal
        $('#addMoreFeatureBtn').on('click', function() {
            var html = `<div class="input-group">
                <input type="text" class="form-control" name="features[]" placeholder="e.g. Dedicated Priority Server">
                <button class="btn btn--danger removeFeatureBtn" type="button"><i class="las la-times"></i></button>
            </div>`;
            $('#addFeatureWrapper').append(html);
        });

        // Dynamic Feature row adder for Edit Modal
        $('#editAddMoreFeatureBtn').on('click', function() {
            var html = `<div class="input-group">
                <input type="text" class="form-control" name="features[]" placeholder="e.g. Dedicated Priority Server">
                <button class="btn btn--danger removeFeatureBtn" type="button"><i class="las la-times"></i></button>
            </div>`;
            $('#editFeatureWrapper').append(html);
        });

        // Remove feature row
        $(document).on('click', '.removeFeatureBtn', function() {
            $(this).closest('.input-group').remove();
        });

        // Edit Plan Button Click
        $('.editPlanBtn').on('click', function() {
            var plan = $(this).data('plan');
            var action = $(this).data('action');

            $('#editPlanForm').attr('action', action);
            $('#edit_name').val(plan.name);
            $('#edit_tagline').val(plan.tagline);
            $('#edit_price').val(parseFloat(plan.price));
            $('#edit_duration_days').val(plan.duration_days);
            $('#edit_account_limit').val(plan.account_limit);
            $('#edit_autoreply_limit').val(plan.autoreply_limit);
            $('#edit_template_limit').val(plan.template_limit);
            $('#edit_campaign_limit').val(plan.campaign_limit);
            $('#edit_message_limit').val(plan.message_limit);

            $('#edit_is_featured').prop('checked', plan.is_featured == 1);
            $('#edit_status').prop('checked', plan.status == 1);

            // Populate features
            var wrapper = $('#editFeatureWrapper');
            wrapper.empty();
            if (plan.features && Array.isArray(plan.features) && plan.features.length > 0) {
                plan.features.forEach(function(feat) {
                    var html = `<div class="input-group">
                        <input type="text" class="form-control" name="features[]" value="${feat}">
                        <button class="btn btn--danger removeFeatureBtn" type="button"><i class="las la-times"></i></button>
                    </div>`;
                    wrapper.append(html);
                });
            } else {
                var html = `<div class="input-group">
                    <input type="text" class="form-control" name="features[]" placeholder="e.g. Dedicated WhatsApp Proxy">
                    <button class="btn btn--danger removeFeatureBtn" type="button"><i class="las la-times"></i></button>
                </div>`;
                wrapper.append(html);
            }

            var modal = new bootstrap.Modal(document.getElementById('editPlanModal'));
            modal.show();
        });

        // Delete Plan Click
        $('.deletePlanBtn').on('click', function() {
            var name = $(this).data('name');
            var action = $(this).data('action');

            $('#deletePlanName').text(name);
            $('#deletePlanForm').attr('action', action);

            var modal = new bootstrap.Modal(document.getElementById('deletePlanModal'));
            modal.show();
        });
    })(jQuery);
</script>
@endpush
