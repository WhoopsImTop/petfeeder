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
* **Body:**
  ```json
  {
      "name": "Bello",
      "species": "Dog",
      "breed": "Golden Retriever",
      "birth_date": "2020-05-10",
      "weight": 25.5,
      "avatar": "https://example.com/avatar.jpg"
  }
  ```
  *(Nur `name` ist ein Pflichtfeld. `weight` als Dezimalzahl `min:0`).*

### Pet anzeigen, aktualisieren, löschen
* **GET** `/households/{household}/pets/{pet}`
* **PUT** `/households/{household}/pets/{pet}` (Gleiche Parameter wie POST, `sometimes` möglich).
* **DELETE** `/households/{household}/pets/{pet}`

---

## 4. Activity Logs (Tracking)

Aktuell unterstützen wir das Auflisten und Erstellen von Activity Logs. Wenn ein Log erstellt wird, bekommen andere Mitglieder via WebPush eine Info.

### Logs abrufen
* **GET** `/households/{household}/activity-logs`
* **Query-Parameter (optional):**
  * `pet_id=1`
  * `activity_type_id=2`
  * `from=2026-03-01`
  * `to=2026-03-10`

### Log/Aktion erstellen
* **POST** `/households/{household}/activity-logs`
* **Body:**
  ```json
  {
      "pet_id": 1,
      "activity_type_id": 2,
      "started_at": "2026-03-19 08:00:00",
      "ended_at": "2026-03-19 08:05:00",
      "value": 200
  }
  ```
  *(Bei "Fast Actions", z.B. nur Füttern angetippt, reicht `pet_id` und `activity_type_id` - der Rest ist nullable).*

---

## 5. Reminders (Erinnerungen)

Wiederkehrende Erinnerungen (mit Cronjob Backend geprüft) hängen an einem Pet und triggern WebPush Benachrichtigungen an den gesamten Haushalt. Alle Routen starten mit `/households/{household}/pets/{pet}/reminders`.

### Reminders für ein Pet abrufen
* **GET** `/households/{household}/pets/{pet}/reminders`

### Reminder erstellen
* **POST** `/households/{household}/pets/{pet}/reminders`
* **Body:**
  ```json
  {
      "activity_type_id": 1,
      "title": "Morgen-Fütterung",
      "time": "08:00",
      "frequency": "daily",
      "is_active": true
  }
  ```
  *(Erlaubte frequencies: `daily`, `weekly`, `monthly`, `custom`. `time` muss im Format `H:i` sein).*

### Reminder anzeigen, aktualisieren, löschen
* **GET** `/households/{household}/pets/{pet}/reminders/{reminder}`
* **PUT** `/households/{household}/pets/{pet}/reminders/{reminder}` (Gleiche Body-Felder wie POST).
* **DELETE** `/households/{household}/pets/{pet}/reminders/{reminder}`

---

## 6. Web Push Subscriptions

Endpunkt, um die Berechtigung aus dem Browser (PWA) im Backend zu speichern, damit der User WebPush-Benachrichtigungen (ActivityLog Infos, Reminder) erhält.

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
