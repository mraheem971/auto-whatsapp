@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">WhatsApp Marketing Campaigns</h4>
                <p class="text-muted mb-0">Launch high-speed bulk broadcast campaigns to your contact lists with anti-ban natural delay protection.</p>
            </div>
            <div>
                <a href="{{ route('user.campaigns.create') }}" class="btn btn--base">
                    <i class="las la-plus-circle me-1"></i> Launch New Campaign
                </a>
            </div>
        </div>

        <div class="card custom--card border shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Campaign Name</th>
                                <th>Target Audience</th>
                                <th>Delivery Progress</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $camp)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $camp->name }}</div>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 220px;">
                                            {{ $camp->message }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ ucwords(str_replace('_', ' ', $camp->target_type)) }}
                                        </span>
                                        <div class="small text-muted">{{ $camp->total_targets }} Recipients</div>
                                    </td>
                                    <td>
                                        @php
                                            $total = $camp->total_targets ?: 1;
                                            $sent = $camp->sent_count ?: 0;
                                            $pct = min(100, round(($sent / $total) * 100));
                                        @endphp
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-success fw-bold">{{ $camp->sent_count }} sent</span>
                                            <span class="text-danger small">{{ $camp->failed_count }} failed</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($camp->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($camp->status === 'running')
                                            <span class="badge bg-primary">Running</span>
                                        @elseif($camp->status === 'paused')
                                            <span class="badge bg-warning">Paused</span>
                                        @else
                                            <span class="badge bg-secondary">Ready</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ showDateTime($camp->created_at) }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('user.campaigns.view', $camp->id) }}" class="btn btn-outline-primary btn-sm" title="Launch / View Live Progress">
                                                <i class="las la-play"></i> Launch
                                            </a>
                                            <form action="{{ route('user.campaigns.delete', $camp->id) }}" method="POST" onsubmit="return confirm('Delete this campaign?')">
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
                                    <td colspan="6" class="text-center py-5">
                                        <i class="las la-bullhorn text-muted fs-1 d-block mb-2"></i>
                                        <h6 class="text-muted">No campaigns created yet</h6>
                                        <p class="text-muted small">Broadcast your first marketing campaign to your WhatsApp contacts.</p>
                                        <a href="{{ route('user.campaigns.create') }}" class="btn btn--base btn-sm">Create Campaign</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($campaigns->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ paginateLinks($campaigns) }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
