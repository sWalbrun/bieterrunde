<x-mail::message>
# Überprüfen und anmelden

Überprüfe die Anmeldeanfrage an dein {{ config('app.name') }} Konto für **{{ $email }}**.

Du kannst diesen Link nur einmal verwenden, und er läuft nach {{ $expiry }} Minuten ab.

<x-mail::button :url="$url">
    Überprüfen und anmelden
</x-mail::button>

Falls du diese Bestätigungs-E-Mail nicht angefordert haben, kannst du sie ignorieren.

Wenn die Schaltfläche nicht erscheint [Stattdessen diesen Link verwenden]({{ $url }}).

Danke,<br>
{{ config('app.name') }}
</x-mail::message>
