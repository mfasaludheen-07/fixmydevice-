# FixMyDevice 🛠️

A modern, full-featured Hardware Repair & Customer Service Complaint Management Web Application built with PHP (MVC architecture), Vanilla CSS/JS, and MySQL.

---

## 🌟 Key Features

- **Public Repair Tracker**: Customers can track real-time status and technician stage progression with a unique Ticket Code without logging in.
- **Customer Portal**:
  - Register and authenticate securely.
  - Submit hardware repair requests across categories (Smart TVs, ACs, Refrigerators, Laptops, Washing Machines, Microwaves).
  - Geolocation support to attach map coordinates.
  - Interactive status stepper and live progress timeline.
  - Real-time discussion thread with assigned technician.
  - Service ratings and satisfaction reviews (1 to 5 stars).
  - Printable / Downloadable repair estimate invoices with verification barcodes in **INR (₹)**.
- **Technician Workbench**:
  - Claim unassigned service complaints from the queue.
  - Update repair stages (`in_diagnosis`, `awaiting_parts`, `repair_in_progress`, `ready`, `resolved`).
  - Provide and update repair cost quotes in **INR (₹)** with quote notes.
  - Post public updates or confidential internal staff logs.
- **Admin Control Center**:
  - Full analytics dashboard with total, pending, and resolved complaint counters.
  - Filterable complaint registry with customer map locations.
  - Technician assignment and reassignment.
  - Manage hardware device categories with icon selectors.
  - Export full ticket registry as CSV with INR figures.
- **Modern UI & Design System**:
  - Rich responsive interface with light and dark theme toggle.
  - Mobile-responsive navigation.
  - Security protections: CSRF tokens, PDO prepared statements, and session hardening.

---

## 🚀 Setup & Installation (XAMPP / PHP)

### 1. Requirements
- PHP 8.0+
- MySQL / MariaDB (via XAMPP)
- Apache (or PHP built-in server)

### 2. Database Configuration
1. Start **MySQL** in XAMPP.
2. Edit `config/database.php` if you have custom MySQL credentials (defaults: `localhost`, `root`, password `""`).
3. The database `fixmydevice` and initial tables are automatically created on first visit via `database/setup.php`.

### 3. Run Locally
**Option A: PHP Development Server**
```bash
php -S localhost:8080 -t public
```
Visit: [http://localhost:8080](http://localhost:8080)

**Option B: XAMPP Apache**
Place the folder in `c:/xampp/htdocs/FixMyDevice/` and visit:
[http://localhost/FixMyDevice](http://localhost/FixMyDevice)

---

## 🔐 Demo Accounts

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@fixmydevice.com` | `admin123` |
| **Technician** | `tech@fixmydevice.com` | `tech123` |
| **Customer** | *Register any new account on `/index.php?url=register`* |

---

## 📁 Project Structure

```
FixMyDevice/
├── app/
│   ├── controllers/      # MVC Controllers (Admin, Auth, Home, Technician, Ticket, Track)
│   ├── core/             # Framework Core (Router, Controller, Model, Session)
│   ├── models/           # Data Models (Ticket, User, Category, Review, TicketComment)
│   └── views/            # Templated Views & Layouts
├── config/
│   └── database.php      # PDO Database Connection Singleton
├── database/
│   ├── schema.sql        # Database Table Schemas
│   └── setup.php         # Automated Setup & Seeder Script
├── public/
│   ├── assets/           # CSS, JavaScript, and Icons
│   ├── uploads/          # Customer Upload Attachments
│   └── index.php         # Application Dispatcher
├── storage/              # Local logs & cache storage
├── index.php             # Root Apache redirector to public/
└── README.md
```
