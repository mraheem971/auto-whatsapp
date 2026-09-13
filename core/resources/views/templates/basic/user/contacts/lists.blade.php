@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">My Contact Lists</h4>
                <p class="text-muted mb-0">Organize your customers and audience into targeted contact lists for marketing broadcasts.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('user.contacts.index') }}" class="btn btn-outline-secondary">
                    <i class="las la-arrow-left me-1"></i> All Contacts
                </a>
                <button type="button" class="btn btn--base" data-bs-toggle="modal" data-bs-target="#createListModal">
                    <i class="las la-plus-circle me-1"></i> Create New List
                </button>
            </div>
        </div>

        <div class="card custom--card border shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>List Name</th>
                                <th>Description</th>
                                <th>Total Members</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lists as $lst)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $lst->name }}</div>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $lst->description ?: 'No description' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $lst->contacts_count }} contacts</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ showDateTime($lst->created_at) }}</small>
                                    </td>
                                    <td>
                                        <form action="{{ route('user.contacts.lists.delete', $lst->id) }}" method="POST" onsubmit="return confirm('Delete this contact list?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="las la-folder-open text-muted fs-1 d-block mb-2"></i>
                                        <h6 class="text-muted">No contact lists created yet</h6>
                                        <p class="text-muted small">Create your first audience group to segment your campaigns.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($lists->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ paginateLinks($lists) }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Modal: Create List -->
<div class="modal fade" id="createListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-plus-circle me-1"></i> New Contact List</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.contacts.lists.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">List Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. VIP Customers / Ramadan Leads" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Description (Optional)</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Brief note about this list..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base"><i class="las la-save me-1"></i> Create List</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
