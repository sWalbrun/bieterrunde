<p align="center"><a href="https://solawir.de/" target="_blank"><img src="https://solawir.de/wp-content/uploads/2020/01/Lgo-Lena-Transparent-alternativ.png" width="400"></a></p>

<p align="center">
<a href="https://github.com/sWalbrun/solawi/actions/workflows/ci.yml"><img src="https://github.com/sWalbrun/solawi/actions/workflows/ci.yml/badge.svg?branch=develop" alt="CI"></a>
<a href="https://github.styleci.io/repos/442431085"><img src="https://github.styleci.io/repos/442431085/shield?style=plastic" alt="Style CI"></a>
</p>

# SolaWi

Dieser Webserver bietet einige Funktionen, um die Verwaltung einer Solawi (https://www.solidarische-landwirtschaft.org) zu erleichtern.

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
    Ab diesem Zeitpunkt steht unter http://localhost der Webserver zur Verfügung mit dem Standardbenutzer
    <i>admin@solawi.de</i> mit Passwort <i>LetMeIn</i>
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

### Bieterrunde

Für ein Gartenjahr kann eine Bieterrunde organisiert werden. Dabei wird vom Admin eine neue Bieterrunde angelegt:
![BieterrundeAnlegen](https://user-images.githubusercontent.com/38902857/150683392-f7a978e2-3713-411f-89dd-b056e1988679.png)

Zusätzlich gibt es für die Verwaltung noch einen Übersichtsdialog über alle abgegeben Gebote (hier können außerdem manuel Gebote eingetragen werden):
![BieterrundenÜbersicht](https://user-images.githubusercontent.com/38902857/150683483-7cd9f114-51d5-409e-a0b7-aa8ba5191e21.png)

Mitglieder können über einen weiteren Dialog ihre Gebote abgeben. Dabei können sie ausschließlich ihre eigenen Gebote einsehen:
![GeboteAbgeben](https://user-images.githubusercontent.com/38902857/150683581-dd9aa8a0-cc2e-484b-9968-9c3d0bffb972.png)

Sobald alle Gebote abgeben werden konnten, wird die passende Runde ermittelt. Im gleichen Zug werden E-Mails an alle Mitglieder versandt:
![BerechnungDerZielrunde](https://user-images.githubusercontent.com/38902857/150683700-cd46a8f4-0203-4f5a-a97f-9975228de9f0.gif)
