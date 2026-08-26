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
                                <th>@lang('Account Name')</th>
                                <th>@lang('Phone Number')</th>
                                <th>@lang('JID')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Connected At')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td>
                                        <div class="user">
                                            <div class="thumb me-2">
                                                <i class="lab la-whatsapp text--success" style="font-size: 26px;"></i>
                                            </div>
                                            <span class="name fw-bold">{{ __($account->account_name) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($account->phone_number)
                                            <span class="badge badge--info fs-6">+{{ $account->phone_number }}</span>
                                        @else
                                            <span class="text-muted">@lang('Not Connected')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $account->jid ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @php echo $account->statusBadge; @endphp
                                    </td>
                                    <td>
                                        {{ $account->last_connected_at ? showDateTime($account->last_connected_at) : 'N/A' }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" 
                                            data-action="{{ route('admin.account.listing.delete', $account->id) }}"
                                            data-question="@lang('Are you sure you want to remove and disconnect this WhatsApp account?')">
                                            <i class="las la-trash me-1"></i>@lang('Delete')
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center py-4" colspan="100%">
                                        <div class="empty-state">
                                            <i class="lab la-whatsapp text--muted mb-3" style="font-size: 48px;"></i>
                                            <p class="text-muted mb-2">@lang('No WhatsApp accounts added yet.')</p>
                                            <a href="{{ route('admin.account.listing.create') }}" class="btn btn-sm btn--primary">
                                                <i class="las la-plus me-1"></i>@lang('Add First WhatsApp Account')
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($accounts->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($accounts) }}
                </div>
            @endif
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.account.listing.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus me-1"></i>@lang('Add New WhatsApp Account')
    </a>
@endpush
