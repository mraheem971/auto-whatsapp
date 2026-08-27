@extends('admin.layouts.app')

@section('panel')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--primary text-white py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-plus-circle me-2 fs-4"></i> @lang('Create WhatsApp Message Template')
                </h5>
            </div>
            <form action="{{ route('admin.templates.store') }}" method="POST">
                @csrf
                <div class="card-body p-4">
                    <div class="row gy-3">
                        <div class="col-md-8">
                            <label class="fw-bold mb-1">@lang('Template Title') <span class="text--danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="@lang('e.g. Welcome Message, Promo Offer')" value="{{ old('title') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-bold mb-1">@lang('Category')</label>
                            <input type="text" name="category" class="form-control" placeholder="@lang('e.g. Marketing, Support, Promo')" value="{{ old('category', 'General') }}">
                        </div>

                        <div class="col-12">
                            <label class="fw-bold mb-1">@lang('Message Content') <span class="text--danger">*</span></label>
                            <textarea name="message" id="template_message" rows="6" class="form-control" placeholder="@lang('Write your message template here...')" required>{{ old('message') }}</textarea>
                            <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                <span class="text-muted small fw-bold">@lang('Shortcode tags'):</span>
                                <button type="button" class="btn btn-xs btn-outline--secondary btn-tag" data-tag="{name}">{name}</button>
                                <button type="button" class="btn btn-xs btn-outline--secondary btn-tag" data-tag="{phone}">{phone}</button>
                                <button type="button" class="btn btn-xs btn-outline--secondary btn-tag" data-tag="{group_name}">{group_name}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.templates.index') }}" class="btn btn--dark btn-sm px-3">
                        <i class="las la-arrow-left me-1"></i> @lang('Back to Templates')
                    </a>
                    <button type="submit" class="btn btn--primary btn-sm px-4 fw-bold">
                        <i class="las la-save me-1"></i> @lang('Save Template')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function($){
    "use strict";
    $('.btn-tag').on('click', function(){
        const tag = $(this).data('tag');
        const textarea = $('#template_message');
        const pos = textarea.prop('selectionStart');
        const val = textarea.val();
        textarea.val(val.substring(0, pos) + tag + val.substring(pos));
        textarea.focus();
    });
})(jQuery);
</script>
@endpush
