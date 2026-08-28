@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-md-12">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--dark text-white d-flex flex-wrap align-items-center justify-content-between py-3 gap-2">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-address-book me-2 fs-4 text--primary"></i> @lang('Contact & Audience Lists')
                </h5>

                <form action="{{ route('admin.contacts.lists.index') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="@lang('Search list name...')" value="{{ request('search') }}">
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
                                <th>@lang('List Name')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('Total Members')</th>
                                <th>@lang('Description')</th>
                                <th>@lang('Created At')</th>
                                <th class="text-end pe-4">@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lists as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('admin.contacts.lists.show', $item->id) }}" class="fw-bold text--primary fs-6">
                                            <i class="las la-folder me-1"></i> {{ __($item->name) }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($item->type === 'groups')
                                            <span class="badge badge--warning"><i class="las la-users me-1"></i> @lang('WhatsApp Groups')</span>
                                        @elseif($item->type === 'contacts')
                                            <span class="badge badge--primary"><i class="las la-user me-1"></i> @lang('Direct Contacts')</span>
                                        @else
                                            <span class="badge badge--dark"><i class="las la-layer-group me-1"></i> @lang('Mixed List')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge--info fs-6">{{ $item->contacts_count }} @lang('Items')</span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $item->description ?: 'N/A' }}</span>
                                    </td>
                                    <td>
                                        {{ showDateTime($item->created_at) }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1 flex-wrap">
                                            <a href="{{ route('admin.contacts.lists.show', $item->id) }}" class="btn btn-sm btn-outline--primary" title="@lang('View & Manage Members')">
                                                <i class="las la-eye me-1"></i> @lang('View')
                                            </a>

                                            <button type="button" class="btn btn-sm btn-outline--dark btnEditList" 
                                                data-id="{{ $item->id }}" 
                                                data-name="{{ $item->name }}" 
                                                data-description="{{ $item->description }}"
                                                title="@lang('Rename / Edit List')">
                                                <i class="las la-edit"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" 
                                                data-action="{{ route('admin.contacts.lists.delete', $item->id) }}"
                                                data-question="@lang('Are you sure you want to delete this list and all its contacts?')">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center py-5" colspan="100%">
                                        <div class="empty-state">
                                            <i class="las la-folder-open text--muted mb-3" style="font-size: 52px;"></i>
                                            <h6 class="text-muted mb-2">@lang('No contact lists created yet.')</h6>
                                            <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
                                                <button type="button" class="btn btn-sm btn--primary" data-bs-toggle="modal" data-bs-target="#createListModal">
                                                    <i class="las la-plus me-1"></i>@lang('Create Custom List')
                                                </button>
                                                <a href="{{ route('admin.contacts.import.csv.view') }}" class="btn btn-sm btn-outline--dark">
                                                    <i class="las la-file-csv me-1"></i>@lang('Import CSV / Excel')
                                                </a>
                                                <a href="{{ route('admin.contacts.sync') }}" class="btn btn-sm btn-outline--success">
                                                    <i class="lab la-whatsapp me-1"></i>@lang('Sync from WhatsApp')
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
            @if($lists->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($lists) }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create List Modal -->
<div id="createListModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="las la-folder-plus me-2 fs-4"></i> @lang('Create New Contact List')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.contacts.lists.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('List Name') <span class="text--danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="@lang('e.g. My Groups, VIP Clients, Summer Leads')" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('List Category / Type') <span class="text--danger">*</span></label>
                        <select name="type" class="form-control form-select" required>
                            <option value="contacts" selected>👤 @lang('Direct Contacts / Phone Numbers')</option>
                            <option value="groups">👥 @lang('WhatsApp Groups List')</option>
                            <option value="mixed">🌐 @lang('Mixed (Contacts & Groups)')</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">@lang('Description (Optional)')</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="@lang('Short notes about this list...')"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--primary btn-sm px-4 fw-bold">
                        <i class="las la-save me-1"></i> @lang('Save List')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit List Modal -->
<div id="editListModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--dark text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="las la-edit me-2 fs-4"></i> @lang('Rename / Edit Contact List')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form id="editListForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('List Name') <span class="text--danger">*</span></label>
                        <input type="text" name="name" id="edit_list_name" class="form-control" required>
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">@lang('Description')</label>
                        <textarea name="description" id="edit_list_description" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--primary btn-sm px-4 fw-bold">
                        <i class="las la-save me-1"></i> @lang('Update List')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn--primary me-2" data-bs-toggle="modal" data-bs-target="#createListModal">
        <i class="las la-plus me-1"></i>@lang('Create Custom List')
    </button>
    <a href="{{ route('admin.contacts.import.csv.view') }}" class="btn btn-sm btn-outline--dark me-2">
        <i class="las la-file-csv me-1"></i>@lang('Import CSV / Excel')
    </a>
    <a href="{{ route('admin.contacts.sync') }}" class="btn btn-sm btn-outline--success">
        <i class="lab la-whatsapp me-1"></i>@lang('Sync from WhatsApp')
    </a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    $('.btnEditList').on('click', function(){
        const id = $(this).data('id');
        const name = $(this).data('name');
        const description = $(this).data('description');

        $('#edit_list_name').val(name);
        $('#edit_list_description').val(description);
        $('#editListForm').attr('action', "{{ url('admin/contacts/lists/update') }}/" + id);
        $('#editListModal').modal('show');
    });

})(jQuery);
</script>
@endpush
