@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-md-12">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--dark text-white d-flex flex-wrap align-items-center justify-content-between py-3 gap-2">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-envelope-open-text me-2 fs-4 text--primary"></i> @lang('WhatsApp Message Templates')
                </h5>

                <form action="{{ route('admin.templates.index') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="@lang('Search title or message...')" value="{{ request('search') }}">
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
                                <th>@lang('Template Title')</th>
                                <th>@lang('Category')</th>
                                <th>@lang('Message Content')</th>
                                <th>@lang('Created At')</th>
                                <th class="text-end pe-4">@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong class="text-dark">{{ __($item->title) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge--info">{{ __($item->category) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted d-block text-truncate" style="max-width: 380px;" title="{{ $item->message }}">
                                            {{ $item->message }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ showDateTime($item->created_at) }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('admin.templates.edit', $item->id) }}" class="btn btn-sm btn-outline--primary" title="@lang('Edit Template')">
                                                <i class="las la-edit fs-6"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" 
                                                data-action="{{ route('admin.templates.delete', $item->id) }}"
                                                data-question="@lang('Are you sure you want to delete this message template?')"
                                                title="@lang('Delete Template')">
                                                <i class="las la-trash fs-6"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center py-5" colspan="100%">
                                        <div class="empty-state">
                                            <i class="las la-envelope-open-text text--muted mb-3" style="font-size: 52px;"></i>
                                            <h6 class="text-muted mb-2">@lang('No message templates found.')</h6>
                                            <a href="{{ route('admin.templates.create') }}" class="btn btn-sm btn--primary mt-2">
                                                <i class="las la-plus me-1"></i>@lang('Create First Template')
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($templates->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($templates) }}
                </div>
            @endif
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.templates.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus me-1"></i>@lang('Add New Template')
    </a>
@endpush
