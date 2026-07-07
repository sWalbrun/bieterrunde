# SolaWi | Beitragsrunde

[![run-tests](https://github.com/sWalbrun/bieterrunde/actions/workflows/run-tests.yml/badge.svg)](https://github.com/sWalbrun/bieterrunde/actions/workflows/run-tests.yml)
[![codecov](https://codecov.io/gh/sWalbrun/bieterrunde/branch/main/graph/badge.svg)](https://codecov.io/gh/sWalbrun/bieterrunde)
![PHP](https://img.shields.io/badge/PHP-%E2%89%A5%208.2-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/Lizenz-MIT-green)

**Die Beitragsrunde deiner Solawi — digital, einfach, ohne Passwort.**

Solidarische Landwirtschaften (Solawis) finanzieren sich über jährliche Beitragsrunden: Jedes Mitglied bietet,
welchen monatlichen Beitrag es leisten kann, bis die Zielsumme des Betriebs erreicht ist. Diese App bildet genau
diesen Prozess ab — vom Start der Runde über die Gebotsabgabe bis zum Export der festgelegten Beiträge.

---

## Zwei Bereiche, klare Rollen

| Bereich | Für wen | Was passiert dort |
| --- | --- | --- |
| **Mitgliederbereich** (`/`) | Mitglieder | Mobilfreundliche Oberfläche: Stand der aktuellen Runde sehen, Gebote abgeben, eigene Ergebnisse einsehen. Mehr nicht — bewusst simpel. |
| **Adminbereich** (`/admin`) | Solawi-Orga | [Filament](https://filamentphp.com)-Panel: Beitragsrunden, Produkte, Mitglieder und Importe verwalten, Ergebnisse ermitteln, Beiträge exportieren. |

Jeder Benutzer hat genau eine Rolle:

| Rolle | Rechte |
| --- | --- |
| `member` | Mitgliederbereich: Gebote abgeben, eigene Ergebnisse sehen |
| `admin` | Zusätzlich das Admin-Panel der eigenen Solawi |
| `super_admin` | Zusätzlich Solawi-(Mandanten-)Verwaltung, Account-Anfragen, Wechsel zwischen Solawis |

**Anmeldung ohne Passwort:** Alle melden sich per Magic Link an — E-Mail-Adresse eingeben, Link aus der Mail
klicken, fertig. Es gibt keine Passwörter, die Mitglieder vergessen könnten.

## ✅ Das kann die App

**Für Mitglieder**
- Gebote pro Produkt und Runde abgeben — mit Live-Umrechnung auf den Betrag pro Anteil bei mehreren Anteilen
- Bestätigungs-Mail mit den abgegebenen Geboten als Beleg
- Dashboard mit laufender Runde, Abgabefrist, Gebotsfortschritt und festgelegten Beiträgen vergangener Runden

**Für die Solawi-Orga**
- Beitragsrunden mit mehreren Produkten („Themen“), Runden-Anzahl und Zielsumme anlegen
- Mitglieder verwalten oder per Excel/CSV importieren (Anteile, Beitragsgruppen, Zahlungsintervalle)
- Rundenstart per Mail ankündigen — optional mit persönlicher Nachricht
- Säumige Mitglieder per Knopfdruck erinnern
- Ergebnis ermitteln: Die App findet automatisch die erste Runde, deren Gebotssumme die Zielsumme deckt,
  und informiert alle Mitglieder über ihren festgelegten Beitrag
- Festgelegte Beiträge als Excel exportieren (für Buchhaltung/Lastschrift-Vorbereitung)

**Für Betreiber (Multi-Mandanten)**
- Mehrere Solawis auf einer Instanz — jede sieht ausschließlich ihre eigenen Daten
- Solawis im Web-UI anlegen (inklusive erstem Admin mit Willkommens-Mail) und löschen
- Interessierte Solawis können über die Anmeldeseite einen **Testzugang anfragen**;
  Super-Admins genehmigen mit einem Klick, der Mandant wird automatisch eingerichtet

## ❌ Das kann die App (bewusst) nicht

- **Kein Zahlungsverkehr.** Es werden keine Lastschriften eingezogen und keine Zahlungen verarbeitet —
  die App ermittelt die Beiträge, der Einzug läuft über eure Buchhaltung (dafür gibt es den Excel-Export).
- **Keine zeitgesteuerte Automatik.** Ankündigung, Erinnerung und Ergebnisermittlung werden von Admins per
  Knopfdruck ausgelöst. Dadurch läuft die App auch auf Hosting ohne Cron/Scheduler.
- **Keine Selbstregistrierung von Mitgliedern.** Mitglieder werden von der Solawi angelegt oder importiert.
  Nur *neue Solawis* können sich über die Testzugang-Anfrage selbst melden.
- **Keine getrennten Datenbanken.** Alle Mandanten teilen sich eine Datenbank (logische Trennung per
  `tenant_id`). Eine E-Mail-Adresse gehört genau einer Solawi.
- **Nur Deutsch.** Die Oberfläche ist auf deutschsprachige Solawis ausgelegt.
- **Kein Passwort-Login.** Ohne funktionierenden Mailversand kann sich niemand anmelden — ein
  konfigurierter SMTP-Server ist Pflicht.

## So läuft eine Beitragsrunde ab

1. **Anlegen** — Admin erstellt die Beitragsrunde mit Abgabezeitraum und Produkten samt Zielsumme.
2. **Ankündigen** — Admin informiert alle Teilnehmenden per Mail über den Start (optional mit persönlicher Nachricht).
3. **Bieten** — Mitglieder geben im Mitgliederbereich für jede Runde ihr monatliches Gebot ab und erhalten eine Bestätigung.
4. **Erinnern** — Admin erinnert säumige Mitglieder per Knopfdruck.
5. **Ermitteln** — Admin lässt das Ergebnis berechnen: Die günstigste Runde, deren Summe die Zielsumme deckt, gewinnt.
   Alle Mitglieder erhalten ihren festgelegten Beitrag per Mail.
6. **Exportieren** — Admin lädt die festgelegten Beiträge als Excel für die Buchhaltung herunter.

## Schnellstart (lokal)

Voraussetzungen: Docker inklusive Docker Compose.

```shell
cp .env.example .env   # Mailtrap-Zugangsdaten (MAIL_USERNAME/MAIL_PASSWORD) eintragen
./serve.sh --fresh
```

Der erste Start dauert einige Minuten (Abhängigkeiten, Assets, Datenbank mit Testdaten). Danach:

- **App:** https://localhost (http://localhost:8000 leitet dorthin weiter)
- **Login:** `adminfoo@solawi.de` oder `adminbar@solawi.de` (Super-Admins der Test-Solawis `foo` und `bar`) —
  der Magic Link landet in deinem [Mailtrap](https://mailtrap.io)-Postfach
- Zusätzlich sind je Solawi 120 Testmitglieder mit Anteilen und Geboten angelegt

> [!IMPORTANT]
> `APP_URL` muss exakt der URL entsprechen, unter der die App aufgerufen wird (lokal: `https://localhost`).
> Die Anmelde-Links sind signiert — stimmt die URL nicht, laufen alle Links ins Leere (HTTP 403).

## Betrieb (Produktion)

```shell
# Einmalig bzw. je Deployment
composer install --no-dev
npm ci && npm run build
php artisan migrate
php artisan tenants:run migrate

# Erste Solawi samt Admin anlegen (Admin erhält eine Willkommens-Mail mit Anmelde-Link)
php artisan tenants:create meine-solawi --admin-name="Maria" --admin-email=maria@example.org
```

Checkliste:

- [ ] `APP_URL` auf die öffentliche URL gesetzt (siehe Hinweis oben)
- [ ] SMTP-Zugangsdaten (`MAIL_*`) konfiguriert — ohne Mail kein Login
- [ ] Impressum & Datenschutzerklärung ausgefüllt
      ([imprint.blade.php](resources/views/legal/imprint.blade.php), [privacy.blade.php](resources/views/legal/privacy.blade.php))
- [ ] Kein Cron nötig — die App kommt ohne Scheduler und Queue-Worker aus (`QUEUE_CONNECTION=sync`)

### CLI-Befehle

| Befehl | Zweck |
| --- | --- |
| `php artisan tenants:create <id> [--admin-name= --admin-email=]` | Solawi anlegen, optional mit erstem Admin |
| `php artisan tenants:delete <id>` | Solawi **samt aller Daten** löschen |
| `php artisan topic:targetAmountReached [topicId]` | Ergebnisermittlung per CLI (alternativ zum Panel-Button) |

## Tech-Stack

[Laravel 11](https://laravel.com) · [Livewire 3](https://livewire.laravel.com) (Mitgliederbereich) ·
[Filament 3](https://filamentphp.com) (Adminbereich) · [stancl/tenancy](https://tenancyforlaravel.com)
(Multi-Mandanten, Single-DB) · [Tailwind CSS 4](https://tailwindcss.com) + Vite · MySQL 8 ·
[FrankenPHP](https://frankenphp.dev) im Docker-Setup

## Entwicklung

```shell
vendor/bin/pest          # Testsuite (Pest, In-Memory-SQLite)
vendor/bin/pint          # Code-Style (Laravel Pint)
npm run dev              # Vite Dev-Server mit Hot Reload
```

Pull Requests sind willkommen — bitte mit Tests und grünem `vendor/bin/pint --test`.

## Lizenz

[MIT](https://opensource.org/licenses/MIT)
