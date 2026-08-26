@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <div class="col-12">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--primary text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="lab la-whatsapp me-2 fs-4"></i> @lang('Sync & Import Contacts from Connected WhatsApp')
                </h5>
                <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="las la-list me-1"></i> @lang('All Contacts')
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

                    <div class="col-lg-4 col-md-6">
                        <button type="button" class="btn btn--primary h-45 px-4 w-100 fw-bold" id="btnFetchWhatsAppContacts" {{ $connectedAccounts->isEmpty() ? 'disabled' : '' }}>
                            <i class="las la-sync me-1"></i> @lang('Fetch Contacts from WhatsApp')
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="fetchLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text--primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6 class="text-muted">@lang('Extracting and syncing contacts from WhatsApp...')</h6>
                </div>

                <!-- Empty Prompt -->
                <div id="fetchPrompt" class="text-center py-5 border rounded bg--light">
                    <div class="mb-3">
                        <i class="lab la-whatsapp text--success" style="font-size: 70px;"></i>
                    </div>
                    <h5 class="text-dark fw-bold mb-1">@lang('Extract Contacts Directly from WhatsApp')</h5>
                    <p class="text-muted small mb-0">@lang('Select an account above and click "Fetch Contacts" to retrieve chats, synced address book numbers, and group members.')</p>
                </div>

                <!-- Results Table -->
                <div id="fetchResults" class="d-none mt-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 p-3 bg-light rounded border">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0 fw-bold">@lang('Total Found'): <span class="badge badge--success fs-6" id="totalCountBadge">0</span></h6>
                            <span class="badge badge--info fs-6" id="selectedCountBadge">0 Selected</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline--dark" id="btnSelectAll">@lang('Select All')</button>
                            <button type="button" class="btn btn-sm btn-outline--secondary" id="btnDeselectAll">@lang('Deselect All')</button>
                            <button type="button" class="btn btn-sm btn--success fw-bold px-3" id="btnImportSelected">
                                <i class="las la-file-import me-1"></i> @lang('Import Selected to Contacts')
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="checkMaster">
                                    </th>
                                    <th>#</th>
                                    <th>@lang('Name / Label')</th>
                                    <th>@lang('Phone Number')</th>
                                    <th>@lang('Source / Group')</th>
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
    <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-list me-1"></i> @lang('All Contacts')
    </a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    let fetchedContactsList = [];

    $('#btnFetchWhatsAppContacts').on('click', function(){
        const sessionId = $('#selected_session').val();

        if(!sessionId){
            notify('error', 'Please select a connected WhatsApp account');
            return;
        }

        $('#fetchPrompt').addClass('d-none');
        $('#fetchResults').addClass('d-none');
        $('#fetchLoading').removeClass('d-none');
        $('#btnFetchWhatsAppContacts').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Fetching...');

        $.ajax({
            url: "{{ url('admin/contacts/fetch') }}/" + sessionId,
            type: "GET",
            success: function(res){
                $('#fetchLoading').addClass('d-none');
                $('#btnFetchWhatsAppContacts').prop('disabled', false).html('<i class="las la-sync me-1"></i> Fetch Contacts from WhatsApp');

                if(res.success && res.contacts && res.contacts.length > 0){
                    fetchedContactsList = res.contacts;
                    $('#totalCountBadge').text(res.contacts.length);
                    renderContactsTable(res.contacts);
                    $('#fetchResults').removeClass('d-none');
                    updateSelectedCounter();
                } else {
                    $('#fetchPrompt').removeClass('d-none');
                    notify('warning', 'No contacts found or account is still syncing.');
                }
            },
            error: function(xhr){
                $('#fetchLoading').addClass('d-none');
                $('#fetchPrompt').removeClass('d-none');
                $('#btnFetchWhatsAppContacts').prop('disabled', false).html('<i class="las la-sync me-1"></i> Fetch Contacts from WhatsApp');

                let errMsg = 'Failed to fetch WhatsApp contacts.';
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

    function renderContactsTable(contacts){
        const tbody = $('#contactsTableBody');
        tbody.empty();

        contacts.forEach((c, i) => {
            const rowJson = JSON.stringify(c).replace(/'/g, "&apos;");
            const row = `
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input contact-checkbox" value='${rowJson}' checked>
                    </td>
                    <td>${i + 1}</td>
                    <td>
                        <strong class="text-dark">${c.name || '+' + c.phone}</strong>
                    </td>
                    <td>
                        <span class="badge badge--info fs-6">+${c.phone}</span>
                    </td>
                    <td>
                        <span class="badge badge--dark">${c.groupName || 'WhatsApp Sync'}</span>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        $('#checkMaster').prop('checked', true);
    }

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

    // Import Selected Contacts
    $('#btnImportSelected').on('click', function(){
        const selected = [];
        $('.contact-checkbox:checked').each(function(){
            selected.push($(this).val());
        });

        if(selected.length === 0){
            notify('error', 'Please select at least one contact to import');
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
                $('#btnImportSelected').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Import Selected to Contacts');
                if(res.success){
                    notify('success', res.message);
                } else {
                    notify('error', res.error || 'Failed to import contacts');
                }
            },
            error: function(xhr){
                $('#btnImportSelected').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Import Selected to Contacts');
                let errMsg = 'Failed to import contacts.';
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
