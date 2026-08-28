@extends('admin.layouts.app')

@section('panel')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--primary text-white py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-file-csv me-2 fs-4"></i> @lang('Import Contacts via CSV / Excel')
                </h5>
            </div>
            <form action="{{ route('admin.contacts.import.csv.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body p-4">
                    <div class="row gy-3">
                        
                        <!-- List Selector or New List -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Select Existing Contact List')</label>
                            <select name="contact_list_id" id="contact_list_id" class="form-control form-select">
                                <option value="">@lang('-- Create a New List Below --')</option>
                                @foreach($lists as $lst)
                                    <option value="{{ $lst->id }}" {{ request('list_id') == $lst->id ? 'selected' : '' }}>
                                        {{ $lst->name }} ({{ $lst->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Or Enter New List Name')</label>
                            <input type="text" name="new_list_name" class="form-control" placeholder="@lang('e.g. VIP Clients 2026')">
                        </div>

                        <!-- File Input -->
                        <div class="col-12 mt-2">
                            <label class="fw-bold mb-1">@lang('Upload CSV / TXT / Excel File') <span class="text--danger">*</span></label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,.txt,.xlsx,.xls" required>
                            <small class="text-muted d-block mt-1">
                                <i class="las la-info-circle me-1 text--primary"></i>@lang('Supported formats: .csv, .txt (comma separated), .xlsx, .xls (Max 10MB)')
                            </small>
                        </div>

                        <!-- Format Guideline Box -->
                        <div class="col-12 mt-3">
                            <div class="p-3 rounded bg-light border">
                                <h6 class="fw-bold text-dark mb-2">@lang('Recommended CSV Column Structure'):</h6>
                                <p class="small text-muted mb-2">@lang('Your file should have the following headers / columns'):</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white mb-0 font-monospace small">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name (Optional)</th>
                                                <th>Phone Number (Required)</th>
                                                <th>Email (Optional)</th>
                                                <th>Group/Tag (Optional)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>John Doe</td>
                                                <td>923001234567</td>
                                                <td>john@example.com</td>
                                                <td>VIP Clients</td>
                                            </tr>
                                            <tr>
                                                <td>Sarah Smith</td>
                                                <td>14155552671</td>
                                                <td>sarah@example.com</td>
                                                <td>Leads</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.contacts.lists.index') }}" class="btn btn--dark btn-sm px-3">
                        <i class="las la-arrow-left me-1"></i> @lang('Cancel')
                    </a>
                    <button type="submit" class="btn btn--primary btn-sm px-4 fw-bold">
                        <i class="las la-upload me-1"></i> @lang('Upload & Import Contacts')
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
