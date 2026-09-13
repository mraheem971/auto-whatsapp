@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">My WhatsApp Contacts</h4>
                <p class="text-muted mb-0">Manage customer phone numbers, assign to contact lists, and sync directly from your linked WhatsApp.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#syncWhatsAppModal">
                    <i class="lab la-whatsapp me-1"></i> Sync from WhatsApp
                </button>
                <a href="{{ route('user.contacts.lists') }}" class="btn btn-outline--base">
                    <i class="las la-list me-1"></i> Contact Lists
                </a>
                <button type="button" class="btn btn--base" data-bs-toggle="modal" data-bs-target="#addContactModal">
                    <i class="las la-plus-circle me-1"></i> Add Contact
                </button>
            </div>
        </div>

        <div class="card custom--card border shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <form action="{{ route('user.contacts.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or phone..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="list_id" class="form-select form-select-sm">
                            <option value="">-- All Lists --</option>
                            @foreach($contactLists as $lst)
                                <option value="{{ $lst->id }}" {{ request('list_id') == $lst->id ? 'selected' : '' }}>{{ $lst->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn--base btn-sm w-100"><i class="las la-search me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Contact List</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contacts as $c)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $c->name }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">+{{ $c->phone_number }}</span>
                                    </td>
                                    <td>
                                        @if($c->contactList)
                                            <span class="badge bg-light text-dark border">{{ $c->contactList->name }}</span>
                                        @else
                                            <span class="text-muted small">No List</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ showDateTime($c->created_at) }}</small>
                                    </td>
                                    <td>
                                        <form action="{{ route('user.contacts.delete', $c->id) }}" method="POST" onsubmit="return confirm('Delete this contact?')">
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
                                        <i class="las la-address-book text-muted fs-1 d-block mb-2"></i>
                                        <h6 class="text-muted">No contacts found</h6>
                                        <p class="text-muted small">Add contacts manually or sync them directly from your linked WhatsApp account.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($contacts->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ paginateLinks($contacts) }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Modal: Add Contact -->
<div class="modal fade" id="addContactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-user-plus me-1"></i> Add New Contact</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.contacts.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">WhatsApp Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" class="form-control" placeholder="e.g. 923216793596" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Assign to Contact List (Optional)</label>
                        <select name="contact_list_id" class="form-select">
                            <option value="">-- No List (General Contacts) --</option>
                            @foreach($contactLists as $lst)
                                <option value="{{ $lst->id }}">{{ $lst->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base"><i class="las la-save me-1"></i> Save Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Sync WhatsApp Contacts -->
<div class="modal fade" id="syncWhatsAppModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white fw-bold"><i class="lab la-whatsapp me-1"></i> Sync from WhatsApp</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.contacts.sync.whatsapp') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small">Import and synchronize saved contacts and chats directly from your connected WhatsApp account.</p>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Select WhatsApp Account</label>
                        <select name="session_id" class="form-select" required>
                            @foreach($connectedAccounts as $acc)
                                <option value="{{ $acc->session_id }}">{{ $acc->account_name }} (+{{ $acc->phone_number ?? $acc->session_id }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="las la-sync me-1"></i> Start Sync</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
