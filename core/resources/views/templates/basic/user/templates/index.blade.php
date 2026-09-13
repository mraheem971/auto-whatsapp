@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">My Message Templates</h4>
                <p class="text-muted mb-0">Create reusable message templates with custom personalization tags for campaigns and replies.</p>
            </div>
            <div>
                <button type="button" class="btn btn--base" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                    <i class="las la-plus-circle me-1"></i> New Template
                </button>
            </div>
        </div>

        <div class="card custom--card border shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Template Name</th>
                                <th>Type</th>
                                <th>Message Content</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $tmpl)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $tmpl->name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-capitalize">{{ $tmpl->type }}</span>
                                    </td>
                                    <td>
                                        <div class="text-muted small text-truncate" style="max-width: 350px;">
                                            {{ $tmpl->message }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-muted">{{ showDateTime($tmpl->created_at) }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm btnEditTemplate"
                                                    data-id="{{ $tmpl->id }}"
                                                    data-name="{{ $tmpl->name }}"
                                                    data-type="{{ $tmpl->type }}"
                                                    data-message="{{ $tmpl->message }}"
                                                    data-media="{{ $tmpl->media_url }}"
                                                    title="Edit Template">
                                                <i class="las la-edit"></i>
                                            </button>
                                            <form action="{{ route('user.templates.delete', $tmpl->id) }}" method="POST" onsubmit="return confirm('Delete this template?')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="las la-envelope-open-text text-muted fs-1 d-block mb-2"></i>
                                        <h6 class="text-muted">No message templates saved yet</h6>
                                        <p class="text-muted small">Create your first reusable WhatsApp template above.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($templates->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ paginateLinks($templates) }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Modal: Create Template -->
<div class="modal fade" id="createTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-plus-circle me-1"></i> New Message Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.templates.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row gy-3">
                        <div class="col-md-8">
                            <label class="fw-bold mb-1">Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Promo Offer / Order Confirmation" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold mb-1">Media Format</label>
                            <select name="type" class="form-select">
                                <option value="text">Text Only</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="document">Document</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Template Content <span class="text-danger">*</span></label>
                            <textarea name="message" rows="5" class="form-control" placeholder="Hello @name, thank you for your order! Your phone is @phone..." required></textarea>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                <span class="badge bg-light text-dark border"><code>@{{name}}</code></span>
                                <span class="badge bg-light text-dark border"><code>@{{phone}}</code></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Media URL (Optional)</label>
                            <input type="url" name="media_url" class="form-control" placeholder="https://example.com/image.jpg">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base"><i class="las la-save me-1"></i> Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Template -->
<div class="modal fade" id="editTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-edit me-1"></i> Edit Message Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTemplateForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row gy-3">
                        <div class="col-md-8">
                            <label class="fw-bold mb-1">Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editTmplName" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold mb-1">Media Format</label>
                            <select name="type" id="editTmplType" class="form-select">
                                <option value="text">Text Only</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="document">Document</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Template Content <span class="text-danger">*</span></label>
                            <textarea name="message" id="editTmplMessage" rows="5" class="form-control" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Media URL (Optional)</label>
                            <input type="url" name="media_url" id="editTmplMedia" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base"><i class="las la-save me-1"></i> Update Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function ($) {
        "use strict";

        $('.btnEditTemplate').on('click', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var type = $(this).data('type');
            var message = $(this).data('message');
            var media = $(this).data('media');

            $('#editTmplName').val(name);
            $('#editTmplType').val(type);
            $('#editTmplMessage').val(message);
            $('#editTmplMedia').val(media);

            var actionUrl = "{{ url('user/templates/update') }}/" + id;
            $('#editTemplateForm').attr('action', actionUrl);

            $('#editTemplateModal').modal('show');
        });

    })(jQuery);
</script>
@endpush
