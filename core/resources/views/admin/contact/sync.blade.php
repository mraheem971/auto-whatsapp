@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <div class="col-12">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--primary text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="lab la-whatsapp me-2 fs-4"></i> @lang('Sync Contacts & WhatsApp Groups')
                </h5>
                <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="las la-list me-1"></i> @lang('Contact List')
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
                                <i class="las la-users me-1"></i> @lang('Fetch Groups Only (No Numbers)')
                            </button>
                            <button type="button" class="btn btn--primary h-45 px-3 flex-grow-1 btnFetchAction" data-mode="contacts_only" {{ $connectedAccounts->isEmpty() ? 'disabled' : '' }}>
                                <i class="las la-user me-1"></i> @lang('Fetch Contacts Only')
                            </button>
                            <button type="button" class="btn btn--dark h-45 px-3 btnFetchAction" data-mode="all" {{ $connectedAccounts->isEmpty() ? 'disabled' : '' }} title="@lang('Fetch everything including group members')">
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
                    <p class="text-muted small mb-0">@lang('Click "Fetch Groups Only" to sync all participating groups with their Group JIDs directly into your Contact List.')</p>
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
                            
                            <button type="button" class="btn btn-sm btn--success fw-bold px-3" id="btnImportSelected">
                                <i class="las la-file-import me-1"></i> @lang('Import Selected')
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
                                    <th>@lang('Name / Subject')</th>
                                    <th>@lang('Target Phone / Group JID')</th>
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
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.campaigns.create') }}" class="btn btn-sm btn--warning text-dark fw-bold me-2">
        <i class="las la-bullhorn me-1"></i> @lang('Create Campaign')
    </a>
    <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-list me-1"></i> @lang('Contact List')
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
            $('#loadingText').text('Extracting WhatsApp contacts...');
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
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
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

    // Import Selected Contacts & Groups
    $('#btnImportSelected').on('click', function(){
        const selected = [];
        $('.contact-checkbox:checked').each(function(){
            selected.push($(this).val());
        });

        if(selected.length === 0){
            notify('error', 'Please select at least one item to import');
            return;
        }

        $('#btnImportSelected').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Importing...');

        $.ajax({
            url: "{{ route('admin.contacts.import') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                contacts: selected
            },
            success: function(res){
                $('#btnImportSelected').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Import Selected');
                if(res.success){
                    notify('success', res.message);
                } else {
                    notify('error', res.error || 'Failed to import items');
                }
            },
            error: function(xhr){
                $('#btnImportSelected').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Import Selected');
                let errMsg = 'Failed to import items.';
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

})(jQuery);
</script>
@endpush
