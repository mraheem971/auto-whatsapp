@extends('admin.layouts.app')

@section('panel')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--primary text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-user-plus me-2 fs-4"></i> @lang('Add New WhatsApp Contact')
                </h5>
                <a href="{{ route('admin.contacts.lists.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="las la-list me-1"></i> @lang('All Lists')
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.contacts.store') }}" method="POST">
                    @csrf
                    <div class="row gy-3">
                        
                        <!-- Contact List Target -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Assign to Contact List')</label>
                            <select name="contact_list_id" id="contact_list_id" class="form-control form-select">
                                <option value="">@lang('-- Select Existing List --')</option>
                                @foreach($lists as $lst)
                                    <option value="{{ $lst->id }}" {{ old('contact_list_id') == $lst->id ? 'selected' : '' }}>
                                        {{ $lst->name }} ({{ $lst->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Or Create New List')</label>
                            <input type="text" name="new_list_name" class="form-control" placeholder="@lang('e.g. VIP Clients 2026')" value="{{ old('new_list_name') }}">
                        </div>

                        <!-- Contact Details -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Contact Name') <span class="text--danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="@lang('e.g. John Doe')" required value="{{ old('name') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('WhatsApp Phone Number') <span class="text--danger">*</span></label>
                            <input type="text" name="phone_number" class="form-control" placeholder="@lang('e.g. 923001234567')" required value="{{ old('phone_number') }}">
                            <small class="text-muted d-block mt-1"><i class="las la-globe me-1"></i>@lang('Include country code without + or dashes')</small>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Group / Tag (Optional)')</label>
                            <input type="text" name="group_name" class="form-control" placeholder="@lang('e.g. Suppliers, VIP, Leads')" value="{{ old('group_name') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold mb-1">@lang('Email Address (Optional)')</label>
                            <input type="email" name="email" class="form-control" placeholder="@lang('e.g. john@example.com')" value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('admin.contacts.lists.index') }}" class="btn btn--dark px-4">@lang('Cancel')</a>
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
    <a href="{{ route('admin.contacts.lists.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-list me-1"></i> @lang('All Contact Lists')
    </a>
@endpush
