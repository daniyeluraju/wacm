# WACM — WhatsApp Assistant & Contact Manager

> Organize contacts, prepare messages, and manage user-controlled WhatsApp communication securely.

---

## Important Compliance Boundary

This application is a **user-controlled WhatsApp productivity assistant**, not an automated bulk-messaging or spam tool.

The system enforces the following principles:
- **No unofficial WhatsApp APIs.**
- **No scraping or DOM automation of WhatsApp Web.**
- **No Selenium, Puppeteer, Playwright, or browser injection.**
- **No automated sending.**
- **No claims of message delivery or read receipts** unless verified by an authorized source.
- All actions require explicit user confirmation.

---

## Architecture Overview (Phase 1)

WACM is built on a clean, decoupled MVC architecture with strict security standards:

- **Language & Runtime:** PHP 8.0+ / 8.2+ with PDO & Prepared Statements
- **Database:** MySQL 8+ / MariaDB (`utf8mb4_unicode_ci`)
- **Web Server:** Apache via XAMPP or PHP built-in server
- **Styling:** Custom CSS design system with Dark Theme, Glassmorphism, and responsive layout
- **Client Logic:** Modern Vanilla ES6+ JavaScript, Toast Notification manager, CSRF headers

---

## Folder Structure

```
wacm/
├── app/
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   └── HomeController.php
│   ├── Core/
│   │   ├── App.php             # Application bootstrap & lifecycle
│   │   ├── Autoloader.php      # PSR-4 standard autoloader
│   │   ├── Database.php        # PDO Singleton connection & transactions
│   │   ├── Env.php             # Environment configuration parser
│   │   ├── Model.php           # Active Record & query builder base
│   │   ├── Request.php         # Safe HTTP request handler & sanitizer
│   │   ├── Response.php        # Response serializer & security headers
│   │   ├── Router.php          # RESTful routing & middleware pipeline
│   │   ├── Security.php        # CSRF, XSS escaping, safe tokens
│   │   ├── Session.php         # HttpOnly, SameSite, timeout manager
│   │   └── View.php            # View rendering engine with layouts
│   ├── Helpers/
│   │   └── functions.php       # Global helper functions
│   ├── Middleware/
│   │   ├── CsrfMiddleware.php
│   │   ├── AuthMiddleware.php
│   │   └── RoleMiddleware.php
│   └── Models/                 # Models for all 15 relational tables
│       ├── User.php
│       ├── Contact.php
│       ├── ContactList.php
│       ├── ContactListMember.php
│       ├── ConsentRecord.php
│       ├── MessageDraft.php
│       ├── Attachment.php
│       ├── Campaign.php
│       ├── CampaignRecipient.php
│       ├── ActivityLog.php
│       ├── AuditLog.php
│       ├── CleanupHistory.php
│       ├── StorageSetting.php
│       └── ImportHistory.php
├── config/
│   ├── app.php
│   ├── database.php
│   └── storage.php
├── database/
│   ├── migrations/
│   │   └── 001_initial_schema.sql
│   ├── seeders/
│   │   └── 001_initial_seed.sql
│   └── migrate.php
├── public/
│   ├── assets/
│   │   ├── css/app.css
│   │   └── js/app.js
│   ├── .htaccess
│   └── index.php
├── resources/
│   └── views/
│       ├── auth/
│       ├── errors/
│       ├── home/
│       ├── layouts/
│       └── partials/
├── routes/
│   └── web.php
├── storage/
│   ├── exports/
│   ├── logs/
│   ├── temporary/
│   └── uploads/
├── .env.example
├── .env
├── composer.json
└── README.md
```

---

## Installation & Setup

### 1. Database Setup & Migrations
Run the built-in migration runner using PHP CLI:
```bash
php database/migrate.php
```

### 2. Starting the Application

#### Option A: PHP Built-in Server (Recommended for development)
```bash
php -S 127.0.0.1:8000 -t public
```
Then open: [http://localhost:8000](http://localhost:8000)

#### Option B: XAMPP Apache
Ensure Apache and MySQL are running in XAMPP Control Panel.
Access via: [http://localhost/wacm/public](http://localhost/wacm/public)

---

## Seeded Development Accounts

| Role | Email | Password |
|---|---|---|
| **Super Admin** | `admin@wacm.local` | `Admin@123456` |
| **Admin** | `manager@wacm.local` | `Admin@123456` |
| **Viewer** | `viewer@wacm.local` | `Viewer@123456` |

---

## 🚀 Cloud Deployment (Deploy Online)

### Option 1: Railway (Recommended — 1-Click Full-Stack)
Railway easily hosts the complete stack (PHP App + WhatsApp Gateway Daemon + MySQL database):

1. Go to [railway.app](https://railway.app) and sign in with GitHub.
2. Click **New Project** → **Deploy from GitHub repo** → select `wacm`.
3. Click **Add Service** → **Database** → **Add MySQL**.
4. In your `wacm` service settings:
   - Click **Generate Domain** (gives you a live public HTTPS URL).
   - Variables are automatically linked via `MYSQL_URL` / `DATABASE_URL`.
5. Railway builds the `Dockerfile`, auto-runs database migrations, and launches both PHP and the WhatsApp gateway!

### Option 2: Render.com
1. Go to [render.com](https://render.com) and sign in with GitHub.
2. Click **New +** → **Blueprint** → select your `wacm` repository.
3. Attach a MySQL database (e.g., from [Aiven](https://aiven.io) or [Clever Cloud](https://www.clever-cloud.com)) and set `DATABASE_URL`.
4. Deploy!

### Option 3: Docker & Docker Compose (Any Server or VPS)
Run the full stack (PHP, Node.js WhatsApp Gateway, and MySQL 8) locally or on any Linux server:

```bash
docker compose up -d --build
```
Access the application at [http://localhost:8000](http://localhost:8000).

