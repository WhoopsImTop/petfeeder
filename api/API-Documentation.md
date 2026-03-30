# Petcare API Documentation

Alle Anfragen an die API (außer Login und Registrierung) erfordern einen gültigen **Bearer Token** im `Authorization` Header und den `Accept: application/json` Header.
Das Backend liefert ausschließlich JSON-Responses.

## Basis-URL
`/api`

---

## 1. Authentication

### Registrieren
Erstellt einen neuen Benutzer und liefert einen Zugangs-Token.
* **POST** `/register`
* **Body (JSON):**
  ```json
  {
      "name": "Max Mustermann",
      "email": "max@example.com",
      "password": "secretpassword",
      "password_confirmation": "secretpassword"
  }
  ```
* **Response (201 Created):**
  ```json
  {
      "token": "1|abc123def456..."
  }
  ```

### Login
Loggt einen Benutzer ein und liefert einen Zugangs-Token.
* **POST** `/login`
* **Body (JSON):**
  ```json
  {
      "email": "max@example.com",
      "password": "secretpassword"
  }
  ```
* **Response (200 OK):**
  ```json
  {
      "token": "2|xyz987vwu654..."
  }
  ```

### Aktueller Nutzer (Me)
Gibt die Daten des aktuell eingeloggten Nutzers inklusive der verknüpften Haushalte zurück (filtert abgelaufene Sitter-Zugänge heraus).
* **GET** `/user`
* **Response (200 OK):**
  ```json
  {
      "id": 1,
      "name": "Max Mustermann",
      "email": "max@example.com",
      "households": [
          {
              "id": 1,
              "name": "Mein Zuhause",
              "pivot": {
                  "role": "admin",
                  "expires_at": null
              }
          }
      ]
  }
  ```

### Logout
Löscht den aktuellen Token.
* **POST** `/logout`
* **Response (200 OK):**
  `{ "message": "Tokens revoked" }`

---

## 2. Households (Mandanten)

Ein User kann Mitglied in mehreren Haushalten (Households) sein. Alle Daten (Pets, Logs etc.) hängen immer an einem Household.

### Haushalte auflisten
* **GET** `/households` 

### Haushalt erstellen
* **POST** `/households`
* **Body:**
  ```json
  {
      "name": "Familie Mustermann"
  }
  ```

### Haushalt anzeigen, aktualisieren, löschen
* **GET** `/households/{household}`
* **PUT** `/households/{household}` (Body: `{"name": "Neuer Name"}`)
* **DELETE** `/households/{household}`

### Mitglied einladen
Nur möglich, wenn der User `admin` im Haushalt ist.
* **POST** `/households/{household}/invite`
* **Body:**
  ```json
  {
      "email": "sitter@example.com",
      "role": "sitter", 
      "expires_at": "2026-03-31 12:00:00" 
  }
  ```
  *(Erlaubte Rollen: `admin`, `member`, `sitter`. `expires_at` ist optional).*

---

## 3. Pets (Nested Resource)

Alle Routen starten mit `/households/{household}/pets`.

### Pets auflisten
* **GET** `/households/{household}/pets`

### Pet anlegen
* **POST** `/households/{household}/pets`
* **Body:** JSON **oder** `multipart/form-data` (für Bild-Upload).
  * **JSON-Beispiel:**
  ```json
  {
      "name": "Bello",
      "species": "Dog",
      "breed": "Golden Retriever",
      "birth_date": "2020-05-10",
      "weight": 25.5
  }
  ```
  * **Avatar:** Datei-Feld `avatar` (`image`, max. ca. 4 MB) speichert unter `storage/app/public/pets`. In der Antwort gibt es zusätzlich **`avatar_url`** (öffentliche URL), `avatar` bleibt der interne Pfad.
  *(Nur `name` ist ein Pflichtfeld. `weight` als Dezimalzahl `min:0`).*

* **Antwort:** u. a. mit `feeding_plans` (inkl. `slots`), wenn die Relation geladen wird (Liste/Einzelansicht).

### Pet anzeigen, aktualisieren, löschen
* **GET** `/households/{household}/pets/{pet}`
* **PUT** `/households/{household}/pets/{pet}` — JSON-Body (ohne Datei).
* **POST** `/households/{household}/pets/{pet}` — Aktualisierung mit `multipart/form-data` (Profilbild empfohlen; PUT + Datei ist oft unzuverlässig).
* **DELETE** `/households/{household}/pets/{pet}`

---

## 4. Activity Logs (Tracking)

Beim Erstellen von Logs erhalten andere Haushaltsmitglieder optional WebPush-Benachrichtigungen.

### Logs abrufen
* **GET** `/households/{household}/activity-logs`
* **Query-Parameter (optional):**
  * `pet_id=1`
  * `activity_type_id=2`

### Log/Aktion erstellen
* **POST** `/households/{household}/activity-logs`
* **Body:**
  ```json
  {
      "pet_id": 1,
      "activity_type_id": 2,
      "feeding_plan_slot_id": 5,
      "started_at": "2026-03-19 08:00:00",
      "ended_at": "2026-03-19 08:05:00",
      "value": 200,
      "notes": null
  }
  ```
  * `feeding_plan_slot_id` ist **optional**; soll bei Abhaken einer geplanten Fütterung gesetzt werden (Kalender, Skip-Logik für Erinnerungen).
  * *(Bei Schnellaktionen reichen oft `pet_id`, `activity_type_id` und `started_at`.)*

### Mehrere Tiere gleichzeitig (Bulk)
* **POST** `/households/{household}/activity-logs/bulk`
* **Body:**
  ```json
  {
      "pet_ids": [1, 2],
      "activity_type_id": 2,
      "feeding_plan_slot_id": 5,
      "started_at": "2026-03-19 08:00:00",
      "value": null,
      "notes": null
  }
  ```
  * Erzeugt pro `pet_id` einen eigenen Log-Eintrag.

### Log löschen
* **DELETE** `/households/{household}/activity-logs/{activityLog}`

---

## 5. Feeding Plans (Futterpläne)

Futterpläne gehören zum Haushalt. Ein Plan hat **mehrere Slots** (Uhrzeit, Wochentage, Aktivitätstyp). Über die Pivot-Tabelle **`feeding_plan_pet`** ist jedes Tier **höchstens einem** Plan zugeordnet (gemeinsamer Plan für viele Tiere oder ein Plan nur für ein Tier).

**Wochentage (`weekdays`):** ISO **1 = Montag … 7 = Sonntag**.

Der Scheduler (`php artisan app:send-reminders`, minütlich) versendet WebPush, wenn ein aktiver Slot zur aktuellen Uhrzeit fällt und der Wochentag passt. Wurde die Fütterung für alle betroffenen Tiere **vor** der Slot-Zeit bereits protokolliert, entfällt die Benachrichtigung.

### Pläne auflisten
* **GET** `/households/{household}/feeding-plans`  
  *(inkl. `slots` mit `activity_type`, `pets`.)*

### Plan anlegen
* **POST** `/households/{household}/feeding-plans`
* **Body:**
  ```json
  {
      "name": "Futterplan Katzen",
      "pet_ids": [1, 2],
      "slots": [
          {
              "activity_type_id": 1,
              "time": "08:00",
              "weekdays": [1, 2, 3, 4, 5, 6, 7],
              "title": "Morgens",
              "is_active": true
          }
      ]
  }
  ```
  * `time` im Format **`H:i`**. `slots` kann leer sein; `weekdays` mindestens ein Eintrag.

### Plan anzeigen, aktualisieren, löschen
* **GET** `/households/{household}/feeding-plans/{feeding_plan}`
* **PUT** `/households/{household}/feeding-plans/{feeding_plan}`
  * Wie POST; bestehende Slots mit **`id`** mitsenden, neue ohne `id`. Slots, deren `id` fehlt, werden entfernt (Full-Sync der Slot-Liste).
* **DELETE** `/households/{household}/feeding-plans/{feeding_plan}`

---

## 6. Feeding Week (Kalender-Auszug pro Tier)

Liefert für eine Kalenderwoche (Montag als Start) pro Tag die **erwarteten** Slot-IDs und die **als erledigt gewerteten** Slot-IDs (über `feeding_plan_slot_id` in den Logs, mit Fallback für ältere Einträge ohne Slot-ID).

* **GET** `/households/{household}/pets/{pet}/feeding-week`
* **Query (optional):** `start=2026-03-24` (beliebiges Datum in der Woche; Backend normalisiert auf Montag dieser Woche).

**Response (Beispiel):**
```json
{
    "week_start": "2026-03-24",
    "days": [
        {
            "date": "2026-03-24",
            "expected_slot_ids": [1, 2],
            "completed_slot_ids": [1]
        }
    ]
}
```

---

## 7. Activity Types (Aktivitätsarten)

Pro Haushalt definierbare Typen (Name, Icon, Flags). Nested unter dem Haushalt.

* **GET** `/households/{household}/activity-types`
* **POST** `/households/{household}/activity-types`
* **Body (POST):**
  ```json
  {
      "name": "Füttern",
      "type": "value",
      "icon": "🍖",
      "is_fast_action": true
  }
  ```
  * `type`: einer von `boolean`, `value`, `timer`.
* **GET** `/households/{household}/activity-types/{activity_type}`
* **PUT** `/households/{household}/activity-types/{activity_type}` — gleiche Felder wie POST, meist `sometimes`.
* **DELETE** `/households/{household}/activity-types/{activity_type}`

---

## 8. Web Push Subscriptions

Endpunkt, um die Berechtigung aus dem Browser (PWA) im Backend zu speichern, damit der User WebPush-Benachrichtigungen erhält (**neue Aktivitäts-Logs**, **fällige Futterplan-Slots**).

### Subscription speichern
Dieser Endpunkt nimmt den Standard-Payload aus der JavaScript `PushSubscription.toJSON()` Methode entgegen.
* **POST** `/user/push-subscriptions`
* **Body:**
  ```json
  {
      "endpoint": "https://fcm.googleapis.com/fcm/send/...",
      "expirationTime": null,
      "keys": {
          "p256dh": "B...",
          "auth": "C..."
      },
      "contentEncoding": "aes128gcm"
  }
  ```
  *(Der Controller validiert `endpoint`, `keys.p256dh` und `keys.auth`).*
