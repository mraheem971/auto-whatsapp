@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">WhatsApp Gateway & API Hub</h4>
                <p class="text-muted mb-0">Monitor your active WhatsApp gateway instances, test message dispatching, and view webhook integration.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('user.whatsapp.create') }}" class="btn btn--base btn-sm">
                    <i class="las la-plus-circle me-1"></i> Connect New Account
                </a>
            </div>
        </div>

        <!-- Engine Status & Summary Cards -->
        <div class="row gy-3 mb-4">
            <div class="col-xl-4 col-sm-6">
                <div class="card custom--card p-4 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Gateway Engine</span>
                        <div class="avatar avatar--sm {{ $isEngineOnline ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $isEngineOnline ? 'text-success' : 'text-danger' }} rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="las {{ $isEngineOnline ? 'la-server' : 'la-exclamation-triangle' }} fs-4"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">
                        {{ $isEngineOnline ? 'Baileys Socket Active' : 'Engine Connecting' }}
                    </h4>
                    <small class="{{ $isEngineOnline ? 'text-success' : 'text-danger' }} fw-semibold">
                        <i class="las la-circle fs-8 me-1"></i> {{ $isEngineOnline ? 'High Speed Multi-Device Engine Online' : 'Check Node Service' }}
                    </small>
                </div>
            </div>

            <div class="col-xl-4 col-sm-6">
                <div class="card custom--card p-4 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Active Gateways</span>
                        <div class="avatar avatar--sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="lab la-whatsapp fs-3"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ $activeCount }} <span class="text-muted fs-6 fw-normal">/ {{ $plan->account_limit ?? 1 }}</span></h4>
                    <small class="text-primary"><i class="las la-wifi me-1"></i>Live WhatsApp Sockets</small>
                </div>
            </div>

            <div class="col-xl-4 col-sm-6">
                <div class="card custom--card p-4 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Anti-Ban Protection</span>
                        <div class="avatar avatar--sm bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="las la-user-shield fs-4"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Human Behavior Active</h4>
                    <small class="text-warning"><i class="las la-clock me-1"></i>Random Delays & Typing Simulation</small>
                </div>
            </div>
        </div>

        <div class="row gy-4">
            
            <!-- Connected Gateway Instances -->
            <div class="col-lg-7">
                <div class="card custom--card border shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold"><i class="lab la-whatsapp text-success me-1"></i> Gateway Instances</h5>
                        <a href="{{ route('user.whatsapp.index') }}" class="btn btn-outline-primary btn-sm">Manage Accounts</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Device / Instance</th>
                                        <th>Phone Number</th>
                                        <th>Socket Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($accounts as $acc)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $acc->account_name }}</div>
                                                <small class="text-muted font-monospace">{{ $acc->session_id }}</small>
                                            </td>
                                            <td>
                                                @if($acc->phone_number)
                                                    <span class="fw-bold text-dark">+{{ $acc->phone_number }}</span>
                                                @else
                                                    <span class="text-muted">Not Paired</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($acc->status == 1)
                                                    <span class="badge bg-success"><i class="las la-check-circle me-1"></i> Online & Ready</span>
                                                @else
                                                    <span class="badge bg-warning"><i class="las la-clock me-1"></i> Offline / QR Needed</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('user.whatsapp.index') }}" class="btn btn-sm btn-outline-secondary" title="View Connection">
                                                    <i class="las la-external-link-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <p class="text-muted mb-2">No gateway instance linked yet.</p>
                                                <a href="{{ route('user.whatsapp.create') }}" class="btn btn--base btn-sm">Connect WhatsApp</a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gateway Test Dispatcher -->
            <div class="col-lg-5">
                <div class="card custom--card border shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold"><i class="las la-paper-plane text--base me-1"></i> Test Gateway Dispatch</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('user.gateway.test') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="fw-bold small mb-1">Sender WhatsApp Instance <span class="text-danger">*</span></label>
                                <select name="session_id" class="form-select" required>
                                    <option value="">Select Connected Account</option>
                                    @foreach($accounts as $acc)
                                        @if($acc->status == 1)
                                            <option value="{{ $acc->session_id }}">{{ $acc->account_name }} (+{{ $acc->phone_number }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold small mb-1">Recipient Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="las la-phone"></i></span>
                                    <input type="text" name="phone_number" class="form-control" placeholder="e.g. 923001234567" required>
                                </div>
                                <small class="text-muted fs-8">Include country code without + or dashes.</small>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold small mb-1">Message Body <span class="text-danger">*</span></label>
                                <textarea name="message" rows="3" class="form-control" placeholder="Hello from WhatsApp Gateway!" required>Gateway Test Ping from {{ gs('site_name') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn--base w-100">
                                <i class="las la-paper-plane me-1"></i> Send Gateway Test Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection