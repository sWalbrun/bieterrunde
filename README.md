# SolaWi|Beitragsrunde - Überarbeitete README

## Übersicht

SolaWi|Beitragsrunde ist ein eigenständiger Webserver, der speziell für die effiziente Verwaltung einer Beitragsrunde in der Solidarischen Landwirtschaft (Solawi) entwickelt wurde. Er bietet eine Vielzahl von Funktionen für diesen Zweck.

## Erste Schritte

### Installation

Um zu beginnen, führen Sie den folgenden Befehl aus:

```shell
./serve.sh --fresh
```

Dieser Vorgang kann bei der ersten Ausführung mehrere Minuten dauern. Er beinhaltet das Herunterladen aller Abhängigkeiten und das Erstellen sowie die initiale Befüllung der Datenbank mit Testdaten. Nach Abschluss des Vorgangs ist der Webserver unter https://localhost bzw. http://localhost verfügbar. Standardmäßig sind die Benutzerkonten `adminfoo@solawi.de` und `adminbar@solawi.de` eingerichtet, die jedoch zu diesem Zeitpunkt noch keine Admin-Rechte besitzen.

### Admin-Rechte Verleihen

Verwenden Sie folgenden Befehl, um beliebige Accounts zu Admins zu machen:

```shell
vendor/bin/sail artisan shield:super-admin
```

## Hauptfunktionen

### Verwaltung einer Beitragsrunde

- **Organisation einer Beitragsrunde**: Der Admin kann eine neue Beitragsrunde für ein Gartenjahr anlegen und verwalten.
- **Übersichtsdialog für Gebote**: Hier können manuell Gebote eingetragen und alle abgegebenen Gebote überblickt werden.
- **Gebotsabgabe durch Mitglieder**: Mitglieder können ihre Gebote über einen Dialog abgeben und nur ihre eigenen Gebote einsehen.
- **Ermittlung der passenden Runde und E-Mail-Versand**: Nachdem alle Gebote abgegeben wurden, wird die passende Runde ermittelt und E-Mails werden an alle Mitglieder versandt.
- **Erinnerungsmails**: Versand von Erinnerungsmails an Mitglieder, die noch kein Gebot abgegeben haben.

### Benutzermanagement

- **CRUD-Funktionalitäten**: Vollständige Verwaltung von Benutzerkonten, einschließlich eines Rollenmanagements.
- **Benutzerimport**: Möglichkeit zum Importieren von Benutzern via Excel oder CSV.

### Multimandantenfähigkeit

- **Mehrere Mandanten auf einer Instanz**: Jede Solawi kann ihre eigene Instanz hosten oder mehrere Solawis können dieselbe Instanz nutzen, wobei jede nur ihre eigenen Informationen sieht.
- **CLI-Befehle für Mandantenverwaltung**: Mandanten können über die Kommandozeile hinzugefügt oder gelöscht werden.

#### Befehle für Mandantenverwaltung

- **Anlegen eines Mandanten**:

  ```shell
  php artisan tenancy:create <tenantId>
  ```

- **Löschen eines Mandanten**:

  ```shell
  php artisan tenancy:delete <tenantId>
  ```
