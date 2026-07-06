# SolaWi|Beitragsrunde

## Übersicht

SolaWi|Beitragsrunde ist ein eigenständiger Webserver, der speziell für die effiziente Verwaltung einer Beitragsrunde in
der Solidarischen Landwirtschaft (Solawi) entwickelt wurde.

Die Anwendung ist in zwei Bereiche geteilt:

- **Mitgliederbereich** (`/`): Eine schlanke, mobilfreundliche Livewire-Oberfläche, in der Mitglieder den Stand der
  aktuellen Beitragsrunde sehen und ihre Gebote abgeben.
- **Adminbereich** (`/admin`): Ein Filament-Panel für die Verwaltung von Beitragsrunden, Produkten und Benutzern.
  Zugriff haben nur Benutzer mit der Rolle `admin` oder `super_admin`.

Die Anmeldung erfolgt passwortlos: Benutzer erhalten einen Anmelde-Link per E-Mail (Magic Link).

## Erste Schritte

### Installation

Um zu beginnen, führe den folgenden Befehl aus. Du wirst nach dem Passwort für den Sudo-Benutzer gefragt, um die
Dateiberechtigungen zu ändern.

```shell
./serve.sh --fresh
```

Dieser Vorgang kann bei der ersten Ausführung mehrere Minuten dauern. Er beinhaltet das Herunterladen aller
Abhängigkeiten sowie das Erstellen und die initiale Befüllung der Datenbank mit Testdaten. Nach Abschluss des Vorgangs
ist der Webserver unter https://localhost bzw. http://localhost verfügbar. Standardmäßig sind die Benutzerkonten
`adminfoo@solawi.de` und `adminbar@solawi.de` als Super-Admins eingerichtet.

### Rollen

Jeder Benutzer hat genau eine Rolle (Spalte `role` auf dem Benutzer):

| Rolle         | Rechte                                                                 |
| ------------- | ---------------------------------------------------------------------- |
| `member`      | Mitgliederbereich: Gebote abgeben und eigene Ergebnisse einsehen        |
| `admin`       | Zusätzlich Zugriff auf das Admin-Panel der eigenen Solawi               |
| `super_admin` | Zusätzlich Mandantenverwaltung, Account-Anfragen und Mandanten-Wechsel  |

Rollen werden im Admin-Panel unter *Benutzer* vergeben.

## Hauptfunktionen

### Verwaltung einer Beitragsrunde

- **Organisation einer Beitragsrunde**: Der Admin kann eine neue Beitragsrunde für ein Gartenjahr anlegen und verwalten.
- **Gebotsabgabe durch Mitglieder**: Mitglieder geben ihre Gebote über den Mitgliederbereich ab und sehen nur ihre
  eigenen Gebote und Ergebnisse.
- **Ermittlung der passenden Runde und E-Mail-Versand**: Nachdem alle Gebote abgegeben wurden, wird die passende Runde
  ermittelt (`php artisan topic:targetAmountReached`) und E-Mails werden an alle Mitglieder versandt.
- **Erinnerungsmails**: Versand von Erinnerungsmails an Mitglieder, die noch kein Gebot abgegeben haben.

### Benutzermanagement

- **CRUD-Funktionalitäten**: Vollständige Verwaltung von Benutzerkonten inklusive Rollenvergabe.
- **Benutzerimport**: Möglichkeit zum Importieren von Benutzern via Excel oder CSV.

### Multimandantenfähigkeit

- **Mehrere Mandanten auf einer Instanz**: Jede Solawi kann ihre eigene Instanz hosten oder mehrere Solawis können
  dieselbe Instanz nutzen, wobei jede nur ihre eigenen Informationen sieht (gemeinsame Datenbank, Trennung über
  `tenant_id`).
- **Mandantenverwaltung im Web-UI**: Super-Admins legen Mandanten im Admin-Panel an (inklusive erstem Admin-Benutzer,
  der eine Willkommens-Mail mit Anmelde-Link erhält), löschen sie samt aller Daten und können per Aktion in einen
  anderen Mandanten wechseln.
- **Testzugang anfragen**: Interessierte Solawis können über die Anmeldeseite einen Testzugang anfragen. Super-Admins
  prüfen die Anfrage im Admin-Panel und genehmigen sie mit einem Klick — der Mandant wird automatisch angelegt.

#### CLI-Befehle für Mandantenverwaltung

- **Anlegen eines Mandanten** (optional mit erstem Admin):

  ```shell
  php artisan tenants:create <tenantId> --admin-name="Maria" --admin-email=maria@example.org
  ```

- **Löschen eines Mandanten** (löscht alle Daten des Mandanten):

  ```shell
  php artisan tenants:delete <tenantId>
  ```
