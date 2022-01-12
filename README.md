<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://solawir.de/wp-content/uploads/2020/01/Lgo-Lena-Transparent-alternativ.png" width="400"></a></p>

<p align="center">
<a href="https://github.com/sWalbrun/solawi/actions/workflows/ci.yml"><img src="https://github.com/sWalbrun/solawi/actions/workflows/ci.yml/badge.svg?branch=develop" alt="CI"></a>
<a href="https://github.styleci.io/repos/442431085"><img src="https://github.styleci.io/repos/442431085/shield?style=plastic" alt="Style CI"></a>
</p>

# solawi
This little webserver is offering you some features for making it more easy managing a solawi (https://www.solidarische-landwirtschaft.org)

# Features

## Bieterrunde

Es kann eine Bieterrunde für ein Gartenjahr organisiert werden, dabei werden alle nötigen Daten erfasst um im Anschluss an eine erfolgreiche Bieterrunde eine regelmäßige Abrechnung durchführen zu können.

# Installation

git clone ... #projekt laden
cd ... #in das projekt springen
cp .env.example .env #env vorlage kopieren
nano .env #env anpassen
php artisan key:generate #schlüssel erzeugen
php artisan migrate --seed #datenbank initialisieren
