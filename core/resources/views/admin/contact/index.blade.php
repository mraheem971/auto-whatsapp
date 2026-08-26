@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-md-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--lg table-responsive">
                    <table class="table--light style--two table">
                        <thead>
                            <tr>
                                <th>@lang('Name')</th>
                                <th>@lang('Phone Number')</th>
                                <th>@lang('Group / Tag')</th>
                                <th>@lang('Email')</th>
                                <th>@lang('Added At')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contacts as $contact)
                                <tr>
                                    <td>
                                        <div class="user">
                                            <div class="thumb me-2">
                                                <i class="las la-user-circle text--primary fs-4"></i>
                                            </div>
                                            <span class="name fw-bold">{{ __($contact->name) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge--info fs-6">+{{ $contact->phone_number }}</span>
                                    </td>
                                    <td>
                                        @if($contact->group_name)
                                            <span class="badge badge--dark">{{ __($contact->group_name) }}</span>
                                        @else
                                            <span class="text-muted">@lang('None')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $contact->email ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        {{ showDateTime($contact->created_at) }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" 
                                            data-action="{{ route('admin.contacts.delete', $contact->id) }}"
                                            data-question="@lang('Are you sure you want to remove this contact?')">
                                            <i class="las la-trash me-1"></i>@lang('Delete')
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center py-4" colspan="100%">
                                        <div class="empty-state">
                                            <i class="las la-address-book text--muted mb-3" style="font-size: 48px;"></i>
                                            <p class="text-muted mb-2">@lang('No contacts added yet.')</p>
                                            <a href="{{ route('admin.contacts.create') }}" class="btn btn-sm btn--primary">
                                                <i class="las la-plus me-1"></i>@lang('Add First Contact')
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($contacts->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($contacts) }}
                </div>
            @endif
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.contacts.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus me-1"></i>@lang('Add New Contact')
    </a>
@endpush
