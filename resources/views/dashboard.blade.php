<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="grid md:grid-cols-2 w-3/4">
        <div class="px-2">
            <x-card title="{{trans('Über uns')}}">
                <p>Wir sind der Verein SoLaWiR e.V., welcher sich zum Ziel gesetzt hat, nachhaltige und solidarische Landbewirtschaftung in
                    Regensburg umzusetzen und die Idee weiter zu verbreiten. Wir wollen die Biodiversität, eine regionale und saisonale
                    Ernährung, sowie die Schaffung von Bewusstsein für einen achtsamen und nachhaltigen Umgang mit der Natur fördern.

                    Wir sind im Frühjahr 2019 gestartet und bepflanzen 1 Hektar Ackerfläche mithilfe von mehreren Gärtnern mit Gemüse und
                    Blühwiesen. Wir pflanzen, pflegen und ernten gemeinsam und teilen die Ernte auf solidarische Weise, so wollen wir eine
                    gemeinschaftliche Selbstversorgung schaffen.

                    Dadurch entstehen auch Erfahrungsmöglichkeiten in Naturschutz, Gartenbau und Landwirtschaft. Wir veranstalten
                    Gemeinschaftsaktionen, bieten Raum für kulturellen Austausch und Bildungsangebote. </p>

            </x-card>
        </div>
        <div class="px-2">
            <x-card title="{{trans('Entstehung')}}">

                <p>Rückblickend kann festgehalten werden: Es kamen immer die richtigen Menschen zur richtigen Zeit! So konnte aus einer im
                    Dezember
                    2018 noch utopisch erscheinenden Idee innerhalb weniger Monate ein tolles Projekt werden, das wuchs und gedieh. Bis zum
                    März
                    2019 schlossen sich dann immer mehr optimistische Menschen voller Tatendrang dem Projekt an. Es fand sich eine Fläche
                    die
                    per
                    Rad nur eine viertel Stunde vom Stadtzentrum entfernt war und den Ansprüchen genügte. Nach einigen
                    Informationsveranstaltungen
                    und einem Netzwerk-Treffen veranstalteten wir im April eine „Anackern“ Aktion und eine Bieterrunde. Auch der SoLaWiR
                    e.V.
                    wurde
                    gegründet. Seit dem wird in Arbeitsgruppen geplant, verbessert und organisiert. Unsere Gärtner pflanzten und pflegten
                    fleißig
                    und mit ehrenamtlicher Unterstützung der Mitglieder, sodass im Juni die erste Ernte stattfinden konnte.</p>

            </x-card>
        </div>
    </div>

</x-app-layout>
