<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Two-Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Add an extra layer of security to your account using a time-based one-time password (TOTP) from an authenticator app.') }}
        </p>
    </header>

    @if ($twoFactorEnabled)
        {{-- 2FA is confirmed & active --}}
        <div class="mt-6 space-y-6">
            <p class="text-sm font-medium text-green-700">
                {{ __('Two-factor authentication is enabled.') }}
            </p>

            @if (! empty($recoveryCodes))
                <div>
                    <p class="text-sm text-gray-600">
                        {{ __('Store these recovery codes in a safe place. Each can be used once if you lose access to your authenticator.') }}
                    </p>
                    <div class="mt-2 grid gap-1 rounded-lg bg-gray-100 p-4 font-mono text-sm">
                        @foreach ($recoveryCodes as $code)
                            <div>{{ $code }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-4">
                <form method="POST" action="{{ route('two-factor.recovery-codes.store') }}">
                    @csrf
                    <x-secondary-button>{{ __('Regenerate Recovery Codes') }}</x-secondary-button>
                </form>

                <form method="POST" action="{{ route('two-factor.disable') }}">
                    @csrf
                    @method('delete')
                    <x-danger-button>{{ __('Disable') }}</x-danger-button>
                </form>
            </div>
        </div>
    @elseif ($twoFactorPending)
        {{-- Secret generated, awaiting confirmation --}}
        <div class="mt-6 space-y-6">
            <p class="text-sm text-gray-600">
                {{ __('Scan the QR code with your authenticator app, then enter the generated code below to finish enabling two-factor authentication.') }}
            </p>

            <div class="inline-block rounded-lg bg-white p-4 ring-1 ring-gray-200">
                {!! $qrCodeSvg !!}
            </div>

            @if ($secretKey)
                <p class="text-sm text-gray-600">
                    {{ __('Setup key') }}: <span class="font-mono">{{ $secretKey }}</span>
                </p>
            @endif

            <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="confirm_2fa_code" :value="__('Code')" />
                    <x-text-input id="confirm_2fa_code" name="code" type="text" inputmode="numeric"
                        class="mt-1 block w-full max-w-xs" autocomplete="one-time-code" autofocus />
                    <x-input-error :messages="$errors->get('confirm')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Confirm') }}</x-primary-button>

                    <span class="text-sm text-gray-500">
                        <button form="cancel_2fa_form" class="underline hover:text-gray-800 cursor-pointer">
                            {{ __('Cancel') }}
                        </button>
                    </span>
                </div>
            </form>

            <form id="cancel_2fa_form" method="POST" action="{{ route('two-factor.disable') }}">
                @csrf
                @method('delete')
            </form>
        </div>
    @else
        {{-- Not enabled --}}
        <div class="mt-6">
            <form method="POST" action="{{ route('two-factor.enable') }}">
                @csrf
                <x-primary-button>{{ __('Enable') }}</x-primary-button>
            </form>
        </div>
    @endif
</section>
