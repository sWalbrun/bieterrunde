<div>
    <h1 class="text-xl font-semibold">{{ trans('Request a test account') }}</h1>

    @if (! $submitted)
        <p class="mt-2 text-sm text-gray-600">
            {{ trans('You are part of a Solawi and want to try the tool? Leave your details and we will get in touch.') }}
        </p>

        <form wire:submit="submit" class="mt-6 space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ trans('Name') }}</label>
                <input id="name" type="text" wire:model="name" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">{{ trans('E-Mail') }}</label>
                <input id="email" type="email" wire:model="email" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="solawiName" class="block text-sm font-medium text-gray-700">{{ trans('Name of your Solawi') }}</label>
                <input id="solawiName" type="text" wire:model="solawiName" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                @error('solawiName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="websiteUrl" class="block text-sm font-medium text-gray-700">
                    {{ trans('Website of your Solawi (optional)') }}
                </label>
                <input id="websiteUrl" type="url" wire:model="websiteUrl" placeholder="https://"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                @error('websiteUrl') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input id="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-primary-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-primary-500"
                wire:loading.attr="disabled">
                {{ trans('Send request') }}
            </button>
        </form>
    @else
        <div class="mt-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">
            {{ trans('Thanks for your interest! We will review your request and get back to you by mail.') }}
        </div>
    @endif

    <p class="mt-6 text-center text-sm text-gray-500">
        <a href="{{ route('login') }}" class="font-medium text-primary-700 hover:underline">{{ trans('Back to login') }}</a>
    </p>
</div>
