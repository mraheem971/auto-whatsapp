@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="deposit-container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card custom--card border shadow-sm">
                    <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
                            <i class="las la-wallet text-success fs-4"></i>
                            {{ __($pageTitle) }}
                        </h5>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                            {{ $gateway->name }}
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <form class="disableSubmission" action="{{ route('user.deposit.manual.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="alert alert-primary d-flex align-items-center gap-2 mb-3">
                                        <i class="las la-info-circle fs-4"></i>
                                        <div>
                                            @lang('You are requesting to deposit') <strong class="text-primary">{{ showAmount($data['amount']) }} {{ __(gs('cur_text')) }}</strong>.
                                            @lang('Please send exactly') <strong class="text-success">{{ showAmount($data['final_amount'], currencyFormat: false) . ' ' . $data['method_currency'] }}</strong> @lang('using the details below.')
                                        </div>
                                    </div>

                                    @if($data->gateway->description)
                                        <div class="gateway-instruction-box p-3 rounded-3 bg-light border mb-4">
                                            <h6 class="fw-bold mb-2 small text-uppercase text-muted"><i class="las la-file-alt me-1"></i> @lang('Payment Instructions'):</h6>
                                            <div class="instruction-content">
                                                @php echo $data->gateway->description @endphp
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <x-viser-form identifier="id" identifierValue="{{ $gateway->form_id }}" />
                                </div>

                                <div class="col-12 mt-4">
                                    <button class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm" type="submit">
                                        <i class="las la-check-circle me-1"></i> @lang('Submit Deposit Proof')
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
