<p align="center"><a href="https://solawir.de/" target="_blank"><img src="https://kattendorfer-hof.de/kattendorferhof/wp-content/uploads/2015/01/solawi-logo-660x204.png" width="400"></a></p>

<p align="center">
<a href="https://github.com/sWalbrun/solawi/actions/workflows/run-tests.yml"><img src="https://github.com/sWalbrun/solawi/actions/workflows/run-tests.yml/badge.svg?branch=main" alt="Tests"></a>
<a href="https://github.com/sWalbrun/solawi/actions/workflows/fix-php-code-style-issues.yml"><img src="https://github.com/sWalbrun/solawi/actions/workflows/fix-php-code-style-issues.yml/badge.svg?branch=main" alt="Tests"></a>
<a href="https://codecov.io/gh/sWalbrun/bieterrunde" > 
 <img src="https://codecov.io/gh/sWalbrun/bieterrunde/branch/main/graph/badge.svg?token=9HG0Q05UW2"/> 
 </a>
</p>

# SolaWi|Beitragsrunde

Dieser Standalone-Webserver bietet umfangreiche Funktionen für die Abwicklung einer Beitragsrunde.

# Requirements|Installation

Mit dem Befehl
<code>./serve.sh --fresh</code>
(kann bei der ersten Ausführung mehrere Minuten benötigen) werden alle Abhängigkeiten bezogen und die Datenbank wird erstellt sowie initial befüllt mit Testdaten.
Sobal der Command fertig ist, steht unter http://localhost der Webserver zur Verfügung mit den <b>Standardbenutzern
<i>adminfoo@solawi.de</i> und <i>adminbar@solawi.de</i>. Die Admin-Benutzer sind dabei über ihr Mandat getrennt.

Die Admin-Benutzer sind zu diesem Zeitpunkt allerdings noch keine Admin-Benutzer. Mit dem Befehl

<code>vendor/bin/sail artisan shield:super-admin</code>

können beliebige Accounts noch zu Admins gemacht werden.

## Features

## Verwaltung eine Beitragsrunde

Für ein Gartenjahr kann eine Beitragsrunde organisiert werden. Dabei wird vom Admin eine neue Beitragsrunde angelegt:
![2023-01-08 16_38_57-Bidder Round bearbeiten - Bieterrunde – Mozilla Firefox](https://user-images.githubusercontent.com/38902857/211205521-e2668fb5-bcb9-4f36-ac53-9540d2fbfb7b.png)

Zusätzlich gibt es für die Verwaltung noch einen Übersichtsdialog über alle abgegeben Gebote (hier können außerdem
manuel Gebote eingetragen werden):
![2023-01-08 16_39_50-Bidder Round bearbeiten - Bieterrunde – Mozilla Firefox](https://user-images.githubusercontent.com/38902857/211205552-29ef40d6-7ddf-476e-a1ef-779d16d06ee2.png)
![2023-01-08 16_40_22-Bidder Round bearbeiten - Bieterrunde – Mozilla Firefox](https://user-images.githubusercontent.com/38902857/211205584-9fdac683-8297-4475-b70c-4f334a1d785a.png)


Mitglieder können über einen weiteren Dialog ihre Gebote abgeben. Dabei können sie ausschließlich ihre eigenen Gebote
einsehen. Dabei wird ein Gebotsvorschlag berechnet, der sich aus Mitgliederzahl und Richtwert über eine Mittlung berechnet:
![2023-01-08 16_42_32-Offer Page - Bieterrunde – Mozilla Firefox](https://user-images.githubusercontent.com/38902857/211205688-bdace1a5-7987-458d-9cc8-30075e778f8a.png)

Sobald alle Gebote abgeben werden konnten, wird die passende Runde ermittelt. Im gleichen Zug werden E-Mails an alle
Mitglieder versandt:
![2023-01-08 16_43_57-Bidder Round bearbeiten - Bieterrunde – Mozilla Firefox](https://user-images.githubusercontent.com/38902857/211205768-b439496d-6485-40bb-b502-70fafa4af0ac.png)

Es sind außerdem noch weitere, kleine Features implementiert, wie der Versand eine Erinnerungsmail an alle Mitglieder, die während des Gebotzeitraums noch kein Gebot abgegeben haben.
![grafik](https://user-images.githubusercontent.com/38902857/173244163-44b577a2-6aa1-4ee8-8713-0a910162f2b5.png)

## Benutzermanagement

Die Software verfügt über einen Benutzermanagementbereich mit den üblichen CRUD-Funktionalitäten. Ein Rollenmanagement
ist ebenfalls implementiert.

## Import von Benutzern

Es ist möglich Benutzer via Excel oder CSV zu importieren.
Dafür muss in einem ersten Schritt die Vorlage vom System heruntergeladen werden und dann mit den Mitgliedern befüllt werden.
Über den Import werden dann alle Benutzer in einem Zug angelegt.

## Multimandantenfähigkeit

Das System verfügt über eine Multimandantenfähigkeit. Das bedeutet, dass eine SolaWi eine Instanz für sich selbst hostet
aber dann auch beliebig viele weitere SolaWis die gleiche Instanz nutzen können. Dabei sieht jede SolaWi ausschließlich
ihre eigenen Informationen.<br>
Das Anlegen eines Mandats geschieht über die CLI.

### Anlegen und Löschen eines Mandanten
Für diese Features werden CLI Befehle bereitgestellt.
![TenantCreationAndDeletion](https://user-images.githubusercontent.com/38902857/178099026-ac2d3560-33e7-43d1-9a29-bbc944de06f3.gif)
#### Anlegen
<code>php artisan tenancy:create &lt;tenantId&gt; </code><br>
Wenn keine Id gegeben wurde, wird danach gefragt.
### Löschen
<code>php artisan tenancy:delete &lt;tenantId&gt; </code><br>
Wenn keine Id gegeben wurde, wird danach gefragt.
