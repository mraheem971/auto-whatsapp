@extends($activeTemplate .'layouts.frontend')
@section('content')
<div class="py-120">
    <div class="d-flex justify-content-center">
        <div class="verification-code-wrapper">
            <div class="verification-area">
                <h4 class="pb-3 text-center border-bottom text--base">@lang('Verify Email Address')</h4>
                <form action="{{route('user.verify.email')}}" method="POST" class="submit-form">
                    @csrf
                    <p class="verification-text">@lang('A 6 digit verification code sent to your email address'):  {{ showEmailAddress(auth()->user()->email) }}</p>
                    <div class="alert alert-info py-2 px-3 small my-2 text-start" role="alert" style="font-size: 13px;">
                        <i class="las la-info-circle me-1"></i> @lang('Please check both your <strong>Inbox</strong> and <strong>Spam / Junk</strong> folders. If not received yet, you can click "Try again" below after the timer.')
                    </div>

                    @include($activeTemplate.'partials.verification_code')

                    <div class="mb-3">
                        <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                    </div>
                    <div class="mb-3">
                        <p>
                            @lang('If you don\'t get any code'), <span class="countdown-wrapper d-inline">@lang('try again after') <span id="countdown" class="fw-bold">--</span> @lang('seconds')</span> <a href="{{route('user.send.verify.code', 'email')}}" class="try-again-link d-none"> @lang('Try again')</a>
                        </p>

                        <a href="{{ route('user.logout') }}">@lang('Logout')</a>

                        @if($errors->has('resend'))
                            <small class="text--danger d-block">{{ $errors->first('resend') }}</small>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    var distance = Number("{{ @$user->ver_code_send_at ? max(0, @$user->ver_code_send_at->addSeconds(30)->timestamp - time()) : 0 }}");
    function updateCountdown() {
        if (distance <= 0) {
            document.querySelector('.countdown-wrapper').classList.add('d-none');
            document.querySelector('.try-again-link').classList.remove('d-none');
        } else {
            document.getElementById("countdown").innerHTML = distance;
            document.querySelector('.countdown-wrapper').classList.remove('d-none');
            document.querySelector('.try-again-link').classList.add('d-none');
        }
    }
    updateCountdown();
    var x = setInterval(function() {
        distance--;
        updateCountdown();
        if (distance <= 0) {
            clearInterval(x);
        }
    }, 1000);
</script>
@endpush
