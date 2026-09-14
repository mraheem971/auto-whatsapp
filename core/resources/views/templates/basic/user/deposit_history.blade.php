@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="py-4">
        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold mb-1">@lang('Deposit History')</h4>
                <p class="text-muted mb-0 small">@lang('Track and monitor all your past deposit transactions and payment statuses.')</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('user.deposit.index') }}" class="btn btn-success btn-sm d-flex align-items-center gap-1">
                    <i class="las la-plus"></i> @lang('New Deposit')
                </a>
            </div>
        </div>

        <div class="card custom--card border shadow-sm mb-4">
            <div class="card-body p-3">
                <form class="listing-search-form" method="GET">
                    <div class="row g-2 align-items-center justify-content-end">
                        <div class="col-md-4 col-sm-6">
                            <div class="input-group">
                                <input class="form-control form-control-sm" name="search" type="text" value="{{ request()->search }}" placeholder="@lang('Search by TRX number...')">
                                <button class="btn btn-secondary btn-sm" type="submit"><i class="las la-search"></i> @lang('Search')</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom--card border shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>@lang('Gateway & TRX')</th>
                                <th class="text-center">@lang('Initiated')</th>
                                <th class="text-center">@lang('Amount')</th>
                                <th class="text-center">@lang('Conversion')</th>
                                <th class="text-center">@lang('Status')</th>
                                <th class="text-end pe-3">@lang('Details')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deposits as $deposit)
                                <tr>
                                    <td>
                                        <div>
                                            <span class="fw-bold text-success">
                                                @if($deposit->method_code < 5000)
                                                    {{ __(@$deposit->gateway->name) }}
                                                @else
                                                    @lang('Google Pay')
                                                @endif
                                            </span>
                                            <br>
                                            <small class="text-muted font-monospace">{{ $deposit->trx }}</small>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="small">
                                            <span>{{ showDateTime($deposit->created_at) }}</span>
                                            <br>
                                            <span class="text-muted">{{ diffForHumans($deposit->created_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div>
                                            <span class="fw-bold">{{ showAmount($deposit->amount) }} {{ __(gs('cur_text')) }}</span>
                                            <br>
                                            <small class="text-danger" data-bs-toggle="tooltip" title="@lang('Processing Charge')">
                                                +{{ showAmount($deposit->charge) }} {{ __(gs('cur_text')) }} @lang('fee')
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="small">
                                            <span>1 {{ __(gs('cur_text')) }} = {{ showAmount($deposit->rate, currencyFormat: false) }} {{ __($deposit->method_currency) }}</span>
                                            <br>
                                            <strong class="text-primary">{{ showAmount($deposit->final_amount, currencyFormat: false) }} {{ __($deposit->method_currency) }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php echo $deposit->statusBadge @endphp
                                    </td>
                                    @php
                                        $details = [];
                                        if($deposit->method_code >= 1000 && $deposit->method_code <= 5000){
                                            foreach (@$deposit->detail ?? [] as $key => $info) {
                                                $details[] = $info;
                                                if ($info->type == 'file') {
                                                    $details[$key]->value = route('user.download.attachment', encrypt(getFilePath('verify').'/'.$info->value));
                                                }
                                            }
                                        }
                                    @endphp

                                    <td class="text-end pe-3">
                                        @if($deposit->method_code >= 1000 && $deposit->method_code <= 5000)
                                            <button type="button" class="btn btn-outline-primary btn-sm detailBtn" data-info="{{ json_encode($details) }}"
                                                @if ($deposit->status == Status::PAYMENT_REJECT)
                                                data-admin_feedback="{{ $deposit->admin_feedback }}"
                                                @endif
                                                >
                                                <i class="las la-desktop"></i>
                                            </button>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" data-bs-toggle="tooltip" title="@lang('Processed automatically')">
                                                <i class="las la-bolt"></i> @lang('Auto')
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center py-4 text-muted">
                                        <i class="las la-inbox fs-2 mb-2 d-block text-muted"></i>
                                        {{ __($emptyMessage) }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($deposits->hasPages())
                <div class="card-footer bg-transparent py-3">
                    {{ $deposits->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- DETAILS MODAL --}}
    <div class="modal fade custom--modal" id="detailModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">@lang('Deposit Details')</h5>
                    <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group userData mb-3">
                    </ul>
                    <div class="feedback"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal" type="button">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');
                var userData = $(this).data('info');
                var html = '';
                if (userData) {
                    userData.forEach(element => {
                        if (element.type != 'file') {
                            html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="fw-semibold">${element.name}</span>
                                <span>${element.value}</span>
                            </li>`;
                        } else {
                            html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="fw-semibold">${element.name}</span>
                                <a href="${element.value}" class="btn btn-sm btn-outline-primary" target="_blank"><i class="las la-download"></i> @lang('Attachment')</a>
                            </li>`;
                        }
                    });
                }

                modal.find('.userData').html(html);

                if ($(this).data('admin_feedback') != undefined && $(this).data('admin_feedback') != '') {
                    var adminFeedback = `
                        <div class="alert alert-warning my-2">
                            <strong class="d-block mb-1">@lang('Admin Feedback'):</strong>
                            <p class="mb-0 small">${$(this).data('admin_feedback')}</p>
                        </div>
                    `;
                } else {
                    var adminFeedback = '';
                }

                modal.find('.feedback').html(adminFeedback);
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
