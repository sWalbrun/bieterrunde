<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-primary-500 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto',
]) }}>
    {{ $slot }}
</button>
