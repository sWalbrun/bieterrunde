<x-layouts.guest :title="trans('Imprint')">
    <article class="prose prose-sm max-w-none">
        <h1>{{ trans('Imprint') }}</h1>

        <h2>Angaben gemäß § 5 DDG</h2>
        {{-- TODO: Betreiberdaten eintragen --}}
        <p>
            [Name des Betreibers]<br>
            [Straße und Hausnummer]<br>
            [PLZ und Ort]
        </p>

        <h2>Kontakt</h2>
        <p>
            E-Mail: [E-Mail-Adresse]<br>
        </p>

        <h2>Verantwortlich für den Inhalt</h2>
        <p>[Name des Verantwortlichen, Anschrift wie oben]</p>

        <h2>Haftung für Inhalte</h2>
        <p>
            Als Diensteanbieter sind wir für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen
            verantwortlich. Wir sind jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu
            überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.
        </p>
    </article>

    <p class="mt-6 text-center text-sm text-gray-500">
        <a href="{{ route('login') }}" class="font-medium text-primary-700 hover:underline">{{ trans('Back to login') }}</a>
    </p>
</x-layouts.guest>
