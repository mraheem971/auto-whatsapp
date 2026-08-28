@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <div class="col-12">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--primary text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="lab la-whatsapp me-2 fs-4"></i> @lang('Sync Contacts & WhatsApp Groups')
                </h5>
                <a href="{{ route('admin.contacts.lists.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="las la-list me-1"></i> @lang('All Contact Lists')
                </a>
            </div>
            <div class="card-body p-4">
                
                <!-- Account Selector & Trigger -->
                <div class="row align-items-end mb-4 gy-3">
                    <div class="col-lg-5 col-md-6">
                        <label class="fw-bold mb-2">@lang('Select Connected WhatsApp Account') <span class="text--danger">*</span></label>
                        <select id="selected_session" class="form-control form-select">
                            @forelse($connectedAccounts as $acc)
                                <option value="{{ $acc->session_id }}">
                                    {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Connected' }})
                                </option>
                            @empty
                                <option value="" disabled selected>@lang('No active WhatsApp account. Please connect an account first.')</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn--warning text-dark fw-bold h-45 px-3 flex-grow-1 btnFetchAction" data-mode="groups_only" {{ $connectedAccounts->isEmpty() ? 'disabled' : '' }}>
                                <i class="las la-users me-1"></i> @lang('Fetch Groups Only')
                            </button>
                            <button type="button" class="btn btn--primary h-45 px-3 flex-grow-1 btnFetchAction" data-mode="contacts_only" {{ $connectedAccounts->isEmpty() ? 'disabled' : '' }}>
                                <i class="las la-user me-1"></i> @lang('Fetch Contacts Only')
                            </button>
                            <button type="button" class="btn btn--dark h-45 px-3 btnFetchAction" data-mode="all" {{ $connectedAccounts->isEmpty() ? 'disabled' : '' }} title="@lang('Fetch everything including group participants')">
                                <i class="las la-sync me-1"></i> @lang('Fetch All')
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="fetchLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text--primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6 class="text-muted" id="loadingText">@lang('Extracting WhatsApp data...')</h6>
                </div>

                <!-- Empty Prompt -->
                <div id="fetchPrompt" class="text-center py-5 border rounded bg--light">
                    <div class="mb-3">
                        <i class="lab la-whatsapp text--success" style="font-size: 70px;"></i>
                    </div>
                    <h5 class="text-dark fw-bold mb-1">@lang('Extract WhatsApp Groups & Contacts')</h5>
                    <p class="text-muted small mb-0">@lang('Retrieve all participating WhatsApp groups or contacts, and save them directly into a custom named Contact List.')</p>
                </div>

                <!-- Results Table -->
                <div id="fetchResults" class="d-none mt-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 p-3 bg-light rounded border">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge badge--dark fs-6" id="totalCountBadge">0 Total</span>
                            <span class="badge badge--warning text-dark fs-6" id="totalGroupsBadge">0 Groups</span>
                            <span class="badge badge--primary fs-6" id="totalContactsBadge">0 Contacts</span>
                            <span class="badge badge--success fs-6" id="selectedCountBadge">0 Selected</span>
                        </div>
                        
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline--secondary active" id="filterAll">@lang('All')</button>
                                <button type="button" class="btn btn-outline--secondary" id="filterGroups">@lang('Groups Only')</button>
                                <button type="button" class="btn btn-outline--secondary" id="filterContacts">@lang('Contacts Only')</button>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline--dark" id="btnSelectAll">@lang('Select All')</button>
                            <button type="button" class="btn btn-sm btn-outline--secondary" id="btnDeselectAll">@lang('Deselect All')</button>
                            
                            <button type="button" class="btn btn-sm btn--success fw-bold px-3" id="btnOpenImportModal">
                                <i class="las la-file-import me-1"></i> @lang('Save to Contact List')
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="checkMaster">
                                    </th>
                                    <th>#</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('WhatsApp Contact Name')</th>
                                    <th>@lang('Phone / Group JID')</th>
                                    <th>@lang('Group / Source')</th>
                                </tr>
                            </thead>
                            <tbody id="contactsTableBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal: Prompt for List Name when saving extracted contacts (No. 3 in User Request) -->
<div id="saveSyncContactsModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="las la-folder-plus me-2 fs-4"></i> @lang('Save Extracted Items to Contact List')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form id="formSaveSyncContacts">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Choose Existing Contact List')</label>
                        <select id="sync_existing_list_id" class="form-control form-select">
                            <option value="">@lang('-- Or create a new list below --')</option>
                            @foreach($lists as $lst)
                                <option value="{{ $lst->name }}">{{ $lst->name }} ({{ $lst->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">@lang('Or Enter New Contact List Name') <span class="text--danger">*</span></label>
                        <input type="text" id="sync_target_list_name" class="form-control" placeholder="@lang('e.g. My Personal Contacts')" value="My WhatsApp Contacts" required>
                        <small class="text-muted d-block mt-1">@lang('All selected items will be saved under this named list.')</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--primary btn-sm px-4 fw-bold" id="btnSubmitSaveSync">
                        <i class="las la-save me-1"></i> @lang('Save into List')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.contacts.lists.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-list me-1"></i> @lang('All Contact Lists')
    </a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    let fetchedItems = [];
    let currentFilter = 'all';

    $('.btnFetchAction').on('click', function(){
        const sessionId = $('#selected_session').val();
        const mode = $(this).data('mode');

        if(!sessionId){
            notify('error', 'Please select a connected WhatsApp account');
            return;
        }

        $('#fetchPrompt').addClass('d-none');
        $('#fetchResults').addClass('d-none');
        $('#fetchLoading').removeClass('d-none');
        
        if(mode === 'groups_only'){
            $('#loadingText').text('Extracting WhatsApp participating groups...');
        } else if(mode === 'contacts_only'){
            $('#loadingText').text('Extracting WhatsApp contacts with genuine names...');
        } else {
            $('#loadingText').text('Extracting all WhatsApp groups and contacts...');
        }

        $('.btnFetchAction').prop('disabled', true);

        $.ajax({
            url: "{{ url('admin/contacts/fetch') }}/" + sessionId + "?mode=" + mode,
            type: "GET",
            success: function(res){
                $('#fetchLoading').addClass('d-none');
                $('.btnFetchAction').prop('disabled', false);

                if(res.success && res.items && res.items.length > 0){
                    fetchedItems = res.items;
                    $('#totalCountBadge').text(`${res.totalItems || res.items.length} Total`);
                    $('#totalGroupsBadge').text(`${res.totalGroups || 0} Groups`);
                    $('#totalContactsBadge').text(`${res.totalContacts || 0} Contacts`);

                    renderItemsTable(fetchedItems);
                    $('#fetchResults').removeClass('d-none');
                    updateSelectedCounter();
                } else {
                    $('#fetchPrompt').removeClass('d-none');
                    notify('warning', 'No groups or contacts found for this session.');
                }
            },
            error: function(xhr){
                $('#fetchLoading').addClass('d-none');
                $('#fetchPrompt').removeClass('d-none');
                $('.btnFetchAction').prop('disabled', false);

                let errMsg = 'Failed to fetch WhatsApp data.';
                if(xhr.responseJSON && xhr.responseJSON.error) errMsg = xhr.responseJSON.error;
                notify('error', errMsg);
            }
        });
    });

    function renderItemsTable(items){
        const tbody = $('#contactsTableBody');
        tbody.empty();

        let filtered = items;
        if(currentFilter === 'groups'){
            filtered = items.filter(it => it.type === 'group');
        } else if(currentFilter === 'contacts'){
            filtered = items.filter(it => it.type === 'contact');
        }

        filtered.forEach((c, i) => {
            const rowJson = JSON.stringify(c).replace(/'/g, "&apos;");
            const isGroup = c.type === 'group';
            const typeBadge = isGroup 
                ? '<span class="badge badge--warning px-2 py-1"><i class="las la-users me-1"></i> Group</span>'
                : '<span class="badge badge--primary px-2 py-1"><i class="las la-user me-1"></i> Contact</span>';

            const phoneDisplay = isGroup 
                ? `<span class="font-monospace text-dark small">${c.id || c.target_jid}</span>`
                : `<span class="badge badge--info fs-6">+${c.phone}</span>`;

            const groupBadge = isGroup 
                ? `<span class="badge badge--success">${c.participantsCount || 0} Members</span>`
                : `<span class="badge badge--dark">${c.groupName || 'Direct Contact'}</span>`;

            const row = `
                <tr data-type="${c.type}">
                    <td>
                        <input type="checkbox" class="form-check-input contact-checkbox" value='${rowJson}' checked>
                    </td>
                    <td>${i + 1}</td>
                    <td>${typeBadge}</td>
                    <td>
                        <strong class="text-dark">${c.name}</strong>
                    </td>
                    <td>${phoneDisplay}</td>
                    <td>${groupBadge}</td>
                </tr>
            `;
            tbody.append(row);
        });

        $('#checkMaster').prop('checked', true);
    }

    // Filter Buttons
    $('#filterAll').on('click', function(){
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
        currentFilter = 'all';
        renderItemsTable(fetchedItems);
        updateSelectedCounter();
    });

    $('#filterGroups').on('click', function(){
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
        currentFilter = 'groups';
        renderItemsTable(fetchedItems);
        updateSelectedCounter();
    });

    $('#filterContacts').on('click', function(){
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
        currentFilter = 'contacts';
        renderItemsTable(fetchedItems);
        updateSelectedCounter();
    });

    // Checkbox toggles
    $('#checkMaster').on('change', function(){
        const isChecked = $(this).is(':checked');
        $('.contact-checkbox').prop('checked', isChecked);
        updateSelectedCounter();
    });

    $('#btnSelectAll').on('click', function(){
        $('#checkMaster').prop('checked', true);
        $('.contact-checkbox').prop('checked', true);
        updateSelectedCounter();
    });

    $('#btnDeselectAll').on('click', function(){
        $('#checkMaster').prop('checked', false);
        $('.contact-checkbox').prop('checked', false);
        updateSelectedCounter();
    });

    $(document).on('change', '.contact-checkbox', function(){
        updateSelectedCounter();
    });

    function updateSelectedCounter(){
        const totalSelected = $('.contact-checkbox:checked').length;
        $('#selectedCountBadge').text(`${totalSelected} Selected`);
    }

    // Open Save Modal
    $('#btnOpenImportModal').on('click', function(){
        const totalSelected = $('.contact-checkbox:checked').length;
        if(totalSelected === 0){
            notify('error', 'Please select at least one item to import');
            return;
        }
        $('#saveSyncContactsModal').modal('show');
    });

    $('#sync_existing_list_id').on('change', function(){
        if($(this).val()){
            $('#sync_target_list_name').val($(this).val());
        }
    });

    // Handle Save into Named List Submit
    $('#formSaveSyncContacts').on('submit', function(e){
        e.preventDefault();

        const listName = $('#sync_target_list_name').val().trim();
        const selected = [];
        $('.contact-checkbox:checked').each(function(){
            selected.push($(this).val());
        });

        if(!listName){
            notify('error', 'Please enter a name for the Contact List');
            return;
        }

        if(selected.length === 0){
            notify('error', 'No items selected');
            return;
        }

        $('#btnSubmitSaveSync').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

        $.ajax({
            url: "{{ route('admin.contacts.import.contacts.list') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                list_name: listName,
                contacts: selected
            },
            success: function(res){
                $('#btnSubmitSaveSync').prop('disabled', false).html('<i class="las la-save me-1"></i> Save into List');
                $('#saveSyncContactsModal').modal('hide');

                if(res.success){
                    notify('success', res.message);
                    if(res.list_id){
                        setTimeout(() => {
                            window.location.href = "{{ url('admin/contacts/lists') }}/" + res.list_id;
                        }, 1000);
                    }
                } else {
                    notify('error', res.error || 'Failed to save items');
                }
            },
            error: function(xhr){
                $('#btnSubmitSaveSync').prop('disabled', false).html('<i class="las la-save me-1"></i> Save into List');
                let errMsg = 'Failed to save contacts to list.';
                if(xhr.responseJSON && xhr.responseJSON.error) errMsg = xhr.responseJSON.error;
                notify('error', errMsg);
            }
        });
    });

})(jQuery);
</script>
@endpush
