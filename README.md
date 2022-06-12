<p align="center"><a href="https://solawir.de/" target="_blank"><img src="https://kattendorfer-hof.de/kattendorferhof/wp-content/uploads/2015/01/solawi-logo-660x204.png" width="400"></a></p>

<p align="center">
<a href="https://github.com/sWalbrun/solawi/actions/workflows/ci.yml"><img src="https://github.com/sWalbrun/solawi/actions/workflows/ci.yml/badge.svg?branch=develop" alt="CI"></a>
</p>

# SolaWi|Bieterrunde

Dieser Standalone-Webserver bietet umfangreiche Funktionen für die Abwicklung einer Bieterrunde.

# Requirements|Installation

Es gibt zwei Möglichkeiten für das Hosting:
<ol>
    <li>Docker Compose</li>
    <ul>
        <li><code>cp .env.example .env</code></li>
        <li><code>docker-compose up</code></li>
        <li><code>docker exec solawir_php_app php artisan key:generate</code></li>
        <li><code>docker exec solawir_php_app php artisan migrate --seed</code></li>
    </ul>
    Ab diesem Zeitpunkt steht unter http://localhost der Webserver zur Verfügung mit den Standardbenutzern
    <i>adminfoo@solawi.de</i> und <i>adminbar@solawi.de</i> mit Passwort <i>password</i>. Die Admin-Benutzer sind dabei über ihr Mandat getrennt.
    <li>Nativ mit Php, Mysql und Nginx (nicht empfohlen)</li>
        <ul>
            <li><b>PHP >7.4, ein MySql Server sowie Nginx müssen installiert sein</b></li>
            <li><code>cp .env.example .env</code></li>
            <li>.env anpassen auf die Datenbankanbindung</li>
            <li><code>php artisan key:generate</code></li>
            <li><code>php artisan migrate --seed</code></li>
            <li><code>php artisan serve</code></li>
            <li>Ab diesem Zeitpunkt steht unter http://localhost:8000 der Webserver zur Verfügung</li>
        </ul>
</ol>

## Features

## Verwaltung eine Bieterrunde

Für ein Gartenjahr kann eine Bieterrunde organisiert werden. Dabei wird vom Admin eine neue Bieterrunde angelegt:
![BieterrundeAnlegen](https://user-images.githubusercontent.com/38902857/150683392-f7a978e2-3713-411f-89dd-b056e1988679.png)

Zusätzlich gibt es für die Verwaltung noch einen Übersichtsdialog über alle abgegeben Gebote (hier können außerdem
manuel Gebote eingetragen werden):
![grafik](https://user-images.githubusercontent.com/38902857/173243791-2453387b-403c-409c-b842-8592067cc774.png)

Mitglieder können über einen weiteren Dialog ihre Gebote abgeben. Dabei können sie ausschließlich ihre eigenen Gebote
einsehen. Dabei wird ein Gebotsvorschlag berechnet, der sich aus Mitgliederzahl und Zielbetrag über eine Mittlung berechnet:
![grafik](https://user-images.githubusercontent.com/38902857/173243823-2481837f-bd83-4ed8-b580-3ef5468927f7.png)

Sobald alle Gebote abgeben werden konnten, wird die passende Runde ermittelt. Im gleichen Zug werden E-Mails an alle
Mitglieder versandt:
![BerechnungDerZielrunde](https://user-images.githubusercontent.com/38902857/150683700-cd46a8f4-0203-4f5a-a97f-9975228de9f0.gif)

Es sind außerdem noch weitere, kleine Features implementiert, wie der Versand eine Erinnerungsmail an alle Mitglieder, die während des Gebotzeitraums noch kein Gebot abgegeben haben.
![grafik](https://user-images.githubusercontent.com/38902857/173244163-44b577a2-6aa1-4ee8-8713-0a910162f2b5.png)

## Benutzermanagement

Die Software verfügt über einen Benutzermanagementbereich mit den üblichen CRUD-Funktionalitäten. Ein Rollenmanagement
ist ebenfalls implementiert.

### Benutzerlistung

![grafik](https://user-images.githubusercontent.com/38902857/173243657-cd81ab68-a52c-4760-ab20-22026153bc94.png)

### Benutzeransicht

![grafik](https://user-images.githubusercontent.com/38902857/173243691-1c4d8132-771d-45ba-b5d1-03a62ac79604.png)

### Bearbeitung eines Benutzers

![grafik](https://user-images.githubusercontent.com/38902857/173243711-536dd2cc-4698-4284-9489-ae6a598bc97f.png)

## Multimandantenfähigkeit

Das System verfügt über eine Multimandantenfähigkeit. Das bedeutet, dass eine SolaWi eine Instanz für sich selbst hostet
aber dann auch beliebig viele weitere SolaWis die gleiche Instanz nutzen können. Dabei sieht jede SolaWi ausschließlich
ihre eigenen Informationen.<br>
Das Anlegen eines Mandats geschieht über die CLI.

## Kommende Features

<ul>
<li>Anlegen eines Mandats über einen benutzerfreundliche CLI Command</li>
<li>Excel Ex- und Import von Benutzern</li>
</ul>
