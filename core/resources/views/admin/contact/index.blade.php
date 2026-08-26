@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-md-12">

        <!-- Bulk Action Banner -->
        <div id="bulkActionCard" class="card b-radius--10 shadow-sm mb-3 d-none bg--light border">
            <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="las la-check-circle text--primary fs-5"></i>
                    <strong class="text-dark"><span id="selectedCountText">0</span> @lang('contacts selected')</strong>
                </div>
                <form id="bulkDeleteForm" action="{{ route('admin.contacts.bulk.delete') }}" method="POST" class="d-inline">
                    @csrf
                    <div id="bulkDeleteInputs"></div>
                    <button type="button" class="btn btn-sm btn-danger confirmationBtn"
                        data-question="@lang('Are you sure you want to delete all selected contacts?')">
                        <i class="las la-trash me-1"></i> @lang('Delete Selected')
                    </button>
                </form>
            </div>
        </div>

        <div class="card b-radius--10 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table--light style--two table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 45px;" class="ps-3">
                                    <input type="checkbox" id="checkMaster" class="form-check-input" title="@lang('Select All')">
                                </th>
                                <th>@lang('Name')</th>
                                <th>@lang('Phone Number')</th>
                                <th>@lang('Group / Tag')</th>
                                <th>@lang('Email')</th>
                                <th>@lang('Added At')</th>
                                <th class="text-end pe-4">@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contacts as $contact)
                                <tr>
                                    <td class="ps-3">
                                        <input type="checkbox" class="form-check-input contact-row-check" value="{{ $contact->id }}">
                                    </td>
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
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" 
                                            data-action="{{ route('admin.contacts.delete', $contact->id) }}"
                                            data-question="@lang('Are you sure you want to remove this contact?')"
                                            data-bs-toggle="tooltip"
                                            title="@lang('Delete Contact')">
                                            <i class="las la-trash fs-6"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center py-5" colspan="100%">
                                        <div class="empty-state">
                                            <i class="las la-address-book text--muted mb-3" style="font-size: 50px;"></i>
                                            <h6 class="text-muted mb-2">@lang('No contacts found')</h6>
                                            <div class="d-flex justify-content-center gap-2 mt-3">
                                                <a href="{{ route('admin.contacts.sync') }}" class="btn btn-sm btn-outline--success">
                                                    <i class="lab la-whatsapp me-1"></i>@lang('Sync from WhatsApp')
                                                </a>
                                                <a href="{{ route('admin.contacts.create') }}" class="btn btn-sm btn--primary">
                                                    <i class="las la-plus me-1"></i>@lang('Add First Contact')
                                                </a>
                                            </div>
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
    <x-search-form placeholder="Search name, phone, group..." />
    <a href="{{ route('admin.contacts.sync') }}" class="btn btn-sm btn-outline--success">
        <i class="lab la-whatsapp me-1"></i>@lang('Sync from WhatsApp')
    </a>
    <a href="{{ route('admin.contacts.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus me-1"></i>@lang('Add New Contact')
    </a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Checkbox master toggle
    $('#checkMaster').on('change', function(){
        const isChecked = $(this).is(':checked');
        $('.contact-row-check').prop('checked', isChecked);
        updateBulkUI();
    });

    $(document).on('change', '.contact-row-check', function(){
        updateBulkUI();
    });

    function updateBulkUI(){
        const checked = $('.contact-row-check:checked');
        const count = checked.length;
        const total = $('.contact-row-check').length;

        $('#checkMaster').prop('checked', count > 0 && count === total);

        if(count > 0){
            $('#selectedCountText').text(count);
            $('#bulkActionCard').removeClass('d-none');

            // Populate hidden inputs for bulk delete
            const inputsDiv = $('#bulkDeleteInputs');
            inputsDiv.empty();
            checked.each(function(){
                inputsDiv.append(`<input type="hidden" name="ids[]" value="${$(this).val()}">`);
            });
        } else {
            $('#bulkActionCard').addClass('d-none');
            $('#bulkDeleteInputs').empty();
        }
    }

})(jQuery);
</script>
@endpush
