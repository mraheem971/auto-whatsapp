@extends('admin.layouts.app')

@section('panel')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--primary text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-user-plus me-2 fs-4"></i> @lang('Add New WhatsApp Contact')
                </h5>
                <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="las la-list me-1"></i> @lang('All Contacts')
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.contacts.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold mb-1">@lang('Contact Name') <span class="text--danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="@lang('e.g. John Doe')" required value="{{ old('name') }}">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold mb-1">@lang('WhatsApp Phone Number') <span class="text--danger">*</span></label>
                                <input type="text" name="phone_number" class="form-control" placeholder="@lang('e.g. 923001234567 (with country code)')" required value="{{ old('phone_number') }}">
                                <small class="text-muted d-block mt-1"><i class="las la-globe me-1"></i>@lang('Include country code without + or dashes')</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold mb-1">@lang('Group / Tag (Optional)')</label>
                                <input type="text" name="group_name" class="form-control" placeholder="@lang('e.g. VIP Clients, Leads, Suppliers')" value="{{ old('group_name') }}">
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="form-group">
                                <label class="fw-bold mb-1">@lang('Email Address (Optional)')</label>
                                <input type="email" name="email" class="form-control" placeholder="@lang('e.g. john@example.com')" value="{{ old('email') }}">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.contacts.index') }}" class="btn btn--dark px-4">@lang('Cancel')</a>
                        <button type="submit" class="btn btn--primary px-4 fw-bold">
                            <i class="las la-save me-1"></i> @lang('Save Contact')
                        </button>
                    </div>
                </form>
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
