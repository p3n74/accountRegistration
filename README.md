# NAME TO BE DETERMINED – _The all-in-one campus companion_

Hey there 👋  Welcome to **BLAH BLAH** – an open-source platform that marries event management, student-organisation tooling and day-to-day student life features (think merch shops, messaging, mini-social-network – the works ✨).

This repo contains **everything**: back-end (PHP), database schema, file-storage layer, Tailwind-flavoured views, and plenty of JavaScript to keep the UI snappy.  
We're still evolving, but the core is already rock-solid and battle-tested with live events.

> **Heads-up** : the legacy `suppdata/` folder has been retired – you won't see it here or anywhere else in this doc.

---

## 🍔 What's on the menu?
* **Organisers** – run events, sell merchandise, manage members, automate paperwork.  
* **Students** – discover events, buy stuff, chat, keep track of everything uni-related in one tab.  
* **Messaging** – Slack-lite rooms & DMs (MVP in progress).  
* **Social Feed** – a chilled timeline for announcements & photos (alpha).  
* **Pluggable tools** – polls, attendance QR, equipment inventory … you name it.

Everything rides on the same account system, so you sign in once and bounce between features seamlessly.

---

## 🚀 Quick-Start (dev)
```bash
# 1. Clone & enter
$ git clone https://github.com/your-org/unilife-hub.git 
$ cd unilife-hub

# 2. Environment
$ cp .env.example .env && nano .env   # set DB creds & mail settings

# 3. Dependencies
$ composer install          # PHP libs
$ npm install && npm run dev  # builds Tailwind + Alpine assets

# 4. DB schema + demo data
$ mysql -u root -p < database/schema.sql
$ php scripts/seed_demo.php

# 5. Fire it up
$ php -S 0.0.0.0:8000 -t public
```
Visit `http://localhost:8000` – demo creds: **admin@example.com / admin123**.

---

## 🗂️ Repo Layout
```
app/
 ├─ controllers/      MVC controllers (Auth, Event, Org, Chat …)
 ├─ core/            Minimal framework (Router, Controller, View, FileStorage)
 ├─ models/          ORM-ish models (User, Event, Org, Message, Student …)
 └─ views/           Blade-style PHP + Tailwind + vanilla JS
public/              Webroot + index.php (+ uploads / built assets)
storage/             JSON blobs written by FileStorage (users, events, participants …)
scripts/             CLI helpers (migrations, seeders, backups)
README.md            ← this file (architecture + docs)
```
_No `suppdata/` – it's history 🔥._

---

## 🏗️ Architecture – the nerdy bit
### Data storage (Hybrid)
| Layer | Why we use it | Main tables / dirs |
|-------|---------------|--------------------|
| **MySQL** | Fast relational queries, strong integrity | `user_credentials`, `events`, `event_participants`, `orgs`, `messages` |
| **FileStorage (JSON)** | Infinite scalability for blob-ish data (large participant lists, chat attachments, flexible settings) | `storage/users/`, `storage/events/`, `storage/participants/`, `storage/orgs/` |

A `FileStorage` helper keeps DB ↔ file structures in sync – updates are atomic and logged.

### Key DB tables (current)
| Table | Purpose | Notable columns |
|-------|---------|-----------------|
| `user_credentials` | Auth + profile | `uid PK`, `email UNIQUE`, `password`, `is_student TINYINT` |
| `events` | Event meta | `eventid PK`, `eventcreator FK`, triggers maintain `participantcount` |
| `event_participants` | Normalised participant list | `participant_id PK UUID`, `event_id FK`, `email`, `attendance_status` ENUM |
| `orgs` (alpha) | Student org accounts | `org_id PK`, `name`, `slug`, `owner_uid` |
| `messages` (alpha) | Chat & DM | `msg_id PK`, `channel_id`, `sender_uid`, `body`, `created_at` |

See `database/schema.sql` for full DDL.

### Attendance Status Enum
```
0 Invited
1 Pending payment
2 Paid
3 Attended
4 Absent
5 Awaiting verification (manual)
```
Special rule: **Paid (2) → can only move to Attended (3)** in the UI – enforced in JS and controllers.

### Application stack
* **PHP 8.1** – minimal custom MVC (no Laravel bloat).  
* **MySQL / MariaDB** – relational anchor.  
* **Alpine.js** – tiny sprinkles where we need reactivity (chat, modals).  
* **TailwindCSS** – utility-first styling, built via Vite.  
* **Vanilla JS / Fetch API** – AJAX everywhere, zero jQuery.  

---

## 🔄 Core Data Flows
1. **Student Registration**  
   `AuthController::register()` → `ExistingStudent` check → DB insert → `FileStorage::saveUserData()`.

2. **Create Event**  
   `EventController::create()` → DB row + file JSON → organiser redirected to /manage.

3. **Add Participant**  
   JS fetch → `EventController::addParticipant()` → `Event::addParticipant()` returns `participant_id` → live inject row, update counter.

4. **Update Status**  
   Modal → `/events/updateParticipantStatus` → DB update → row badge tweaked live (reload fallback).

5. **Chat Message (alpha)**  
   WebSocket (Mercure) push → `MessageController::store()` → file archive in `storage/orgs/{org}/channels/{id}.json`.

---

## 🔍 Function / Class Index
### app/core
| Path | Member | TL;DR |
|------|--------|------|
| core/Router.php | `dispatch()` | Parses URI, instantiates controller, calls action |
| core/FileStorage.php | `saveUserData($uid,$data)` | write `storage/users/{uid}.json` |
| | `addParticipantToEvent($eventId,$pid,$data)` | Append participant blob |

### app/models
| Model | Key methods |
|-------|-------------|
| **User** | `createUser`, `getUserByEmail`, `syncUserToFile` |
| **Event** | `addParticipant`, `removeParticipant`, `updateAttendanceStatus`, `getEventParticipants` |
| **Org** (alpha) | `createOrg`, `inviteMember`, `getMerchCatalogue` |
| **ExistingStudent** | `getStudentByEmail`, `studentExists` |

_(Grepping for `function` in each file shows full list – code is doc-blocked)._  

---

## 🎨 Front-End Cheatsheet
* Every view lives in `app/views/…`.  
* Layout is `views/shared/layout.php` – includes Tailwind build.  
* Event manage page: `views/events/manage.php` – 1.1k LOC but heavily commented.
* Inline JS uses IIFEs & strict mode; no global bleed.
* Real-time UI rules:  
  * Add participant → append row, no reload.  
  * Status update → badge swap; if fail, fallback reload.  
  * "Paid" status locks every option except "Attended".

---

## 🤝 Development Guidelines
### Commit style
`<type>: <subject>` – e.g. `feat: add merch storefront skeleton`. Types: `feat`, `fix`, `docs`, `refactor`, `chore`.

### DB migrations
Run `php scripts/migrate.php up|down` – see `scripts/migrations/*.sql`.

### Coding conventions
* PHP PSR-12 (use `composer cs:fix`).  
* JS – eslint-standard, 2-space indent.  
* Tailwind – keep custom classes in `/src/css/components.css`.

### Student-integration edge-cases
* A student's **name fields are immutable** – both UI & API must respect.
* `existing_student_info` is the single source of truth – nightly sync job runs in cron.

---

## 🛠️ Roadmap (abridged)
- [x] Registration and verification
- [ ] Student Organization Module
- [ ] OSFA Module
- [ ] Notifications module
- [ ] CES Points module
- [x] Event core (create, manage, invite, stats)  
- [ ] Finance Module
- [ ] Merch module scaffold (stripe checkout)  
- [ ] Messaging & channels (Mercure, websocket)  
- [ ] Social feed w/ image uploads  
- [ ] Equipment inventory for orgs  
- [ ] Mobile-first PWA shell  

Community contributions welcome – check `TODO.md`.

---

## ❓ FAQ & Troubleshooting
**Q: File permissions are killing me.**  
A: `chmod -R 755 storage && chown -R www-data:www-data storage`.

**Q: Status badge doesn't change sometimes.**  
A: Likely JS couldn't locate the row – press F12 for console; fallback reload triggers automatically after 1s.

**Q: Student can't edit name.**  
A: Works as intended – names come from university roster and are locked.

For more issues peep the dedicated section in `ARCHITECTURE.md` (now merged below 😉).

---

## 📝 License
MIT © 2023-present Your-Org – hack away! 