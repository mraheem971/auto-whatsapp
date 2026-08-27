@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-md-12">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--dark text-white d-flex flex-wrap align-items-center justify-content-between py-3 gap-2">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-bullhorn me-2 fs-4 text--primary"></i> @lang('WhatsApp Marketing Campaigns')
                </h5>

                <form action="{{ route('admin.campaigns.index') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="@lang('Search campaign...')" value="{{ request('search') }}">
                        <button class="btn btn--primary" type="submit"><i class="la la-search"></i></button>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive--lg table-responsive">
                    <table class="table--light style--two table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('Campaign Name')</th>
                                <th>@lang('Target Audience')</th>
                                <th>@lang('Progress / Delivery')</th>
                                <th>@lang('Delay')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Created At')</th>
                                <th class="text-end pe-4">@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $camp)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong class="text-dark d-block">{{ __($camp->name) }}</strong>
                                        <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $camp->message }}</small>
                                    </td>
                                    <td>
                                        @if($camp->target_type === 'groups')
                                            <span class="badge badge--warning"><i class="las la-users me-1"></i> @lang('All WhatsApp Groups')</span>
                                        @elseif($camp->target_type === 'contacts')
                                            <span class="badge badge--primary"><i class="las la-user me-1"></i> @lang('All Direct Contacts')</span>
                                        @elseif($camp->target_type === 'selected_group')
                                            <span class="badge badge--info"><i class="las la-layer-group me-1"></i> @lang('Specific Group')</span>
                                        @else
                                            <span class="badge badge--dark"><i class="las la-globe me-1"></i> @lang('All Contacts & Groups')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $pct = $camp->total_targets > 0 ? round(($camp->sent_count / $camp->total_targets) * 100) : 0;
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar bg--success" role="progressbar" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <small class="fw-bold">{{ $camp->sent_count }}/{{ $camp->total_targets }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $camp->delay_seconds }}s delay</span>
                                    </td>
                                    <td>
                                        @if($camp->status === 'completed')
                                            <span class="badge badge--success">@lang('Completed')</span>
                                        @elseif($camp->status === 'running')
                                            <span class="badge badge--warning"><i class="fas fa-spinner fa-spin me-1"></i> @lang('Running')</span>
                                        @else
                                            <span class="badge badge--info">@lang('Ready / Draft')</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ showDateTime($camp->created_at) }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('admin.campaigns.view', $camp->id) }}" class="btn btn-sm btn-outline--primary" title="@lang('Open Campaign Runner')">
                                                <i class="las la-play-circle fs-6 me-1"></i> @lang('Launch / View')
                                            </a>

                                            <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" 
                                                data-action="{{ route('admin.campaigns.delete', $camp->id) }}"
                                                data-question="@lang('Are you sure you want to delete this campaign?')"
                                                title="@lang('Delete Campaign')">
                                                <i class="las la-trash fs-6"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center py-5" colspan="100%">
                                        <div class="empty-state">
                                            <i class="las la-bullhorn text--muted mb-3" style="font-size: 52px;"></i>
                                            <h6 class="text-muted mb-2">@lang('No marketing campaigns created yet.')</h6>
                                            <a href="{{ route('admin.campaigns.create') }}" class="btn btn-sm btn--primary mt-2">
                                                <i class="las la-plus me-1"></i>@lang('Create First Campaign')
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($campaigns->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($campaigns) }}
                </div>
            @endif
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.campaigns.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus me-1"></i>@lang('Create New Campaign')
    </a>
@endpush
