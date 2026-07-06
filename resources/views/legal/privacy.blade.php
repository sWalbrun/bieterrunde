<x-layouts.guest :title="trans('Privacy policy')">
    <article class="prose prose-sm max-w-none">
        <h1>{{ trans('Privacy policy') }}</h1>

        <h2>Verantwortlicher</h2>
        {{-- TODO: Betreiberdaten eintragen (identisch zum Impressum) --}}
        <p>
            [Name des Betreibers]<br>
            [Anschrift]<br>
            E-Mail: [E-Mail-Adresse]
        </p>

        <h2>Welche Daten wir verarbeiten</h2>
        <p>
            Diese Anwendung verwaltet Beitragsrunden einer Solidarischen Landwirtschaft. Dazu speichern wir die von
            deiner Solawi hinterlegten Daten: Name, E-Mail-Adresse, Beitragsgruppe, Anteile, Zahlungsintervall,
            Beitritts-/Austrittsdatum sowie die von dir abgegebenen Gebote.
        </p>

        <h2>Zweck und Rechtsgrundlage</h2>
        <p>
            Die Verarbeitung erfolgt zur Durchführung der Beitragsrunde deiner Solawi (Art. 6 Abs. 1 lit. b DSGVO)
            beziehungsweise auf Grundlage der Mitgliedschaftsvereinbarung mit deiner Solawi.
        </p>

        <h2>Cookies</h2>
        <p>
            Wir verwenden ausschließlich technisch notwendige Cookies (Sitzung, CSRF-Schutz und die Zuordnung zu deiner
            Solawi). Es findet kein Tracking statt.
        </p>

        <h2>E-Mails</h2>
        <p>
            Du erhältst systembezogene E-Mails: Anmelde-Links, Erinnerungen an laufende Beitragsrunden und
            Ergebnisbenachrichtigungen. Werbe-E-Mails versenden wir nicht.
        </p>

        <h2>Speicherdauer und Löschung</h2>
        <p>
            Deine Daten werden gelöscht, wenn dein Benutzerkonto durch deine Solawi entfernt wird. Wende dich für
            Auskunft, Berichtigung oder Löschung an deine Solawi oder an den oben genannten Verantwortlichen.
        </p>

        <h2>Deine Rechte</h2>
        <p>
            Du hast das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung sowie
            Datenübertragbarkeit (Art. 15–20 DSGVO) und das Recht auf Beschwerde bei einer Aufsichtsbehörde
            (Art. 77 DSGVO).
        </p>
    </article>

    <p class="mt-6 text-center text-sm text-gray-500">
        <a href="{{ route('login') }}" class="font-medium text-primary-700 hover:underline">{{ trans('Back to login') }}</a>
    </p>
</x-layouts.guest>
