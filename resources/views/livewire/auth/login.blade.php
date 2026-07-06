<div>
    <h1 class="text-xl font-semibold">{{ trans('Login') }}</h1>

    @if (! $submitted)
        <p class="mt-2 text-sm text-gray-600">
            {{ trans('Enter your e-mail address and we will send you a login link. No password needed.') }}
        </p>

        <form wire:submit="sendLink" class="mt-6 space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">{{ trans('E-Mail') }}</label>
                <input
                    id="email"
                    type="email"
                    wire:model="email"
                    required
                    autofocus
                    autocomplete="email"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-primary-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-primary-500"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>{{ trans('Send login link') }}</span>
                <span wire:loading>{{ trans('Sending…') }}</span>
            </button>
        </form>
    @else
        <div class="mt-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">
            {{ trans('If an account exists for this e-mail address, a login link has been sent. Please check your inbox.') }}
        </div>
    @endif

    <p class="mt-6 border-t border-gray-100 pt-4 text-center text-sm text-gray-500">
        {{ trans('You are part of a Solawi and want to try the tool?') }}
        <a href="{{ route('request-account') }}" class="font-medium text-primary-700 hover:underline">
            {{ trans('Request a test account') }}
        </a>
    </p>
</div>
