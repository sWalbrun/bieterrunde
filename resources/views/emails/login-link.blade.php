<x-mail::message>
# {{ trans('Login to :app', ['app' => config('app.name')]) }}

{{ trans('Servus :name,', ['name' => $user->name]) }}

{{ trans('click the button below to log in. The link is valid for :expiry minutes and can only be used once.', ['expiry' => $expiry]) }}

<x-mail::button :url="$url">
{{ trans('Log in now') }}
</x-mail::button>

{{ trans('If you did not request this e-mail, you can safely ignore it.') }}

{{ trans('If the button does not work, use this link instead:') }} {{ $url }}

{{ trans('Thanks,') }}<br>
{{ config('app.name') }}
</x-mail::message>
