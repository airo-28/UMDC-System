<h1 align="center">
  UM Dining Center — Operations Management System
</h1>

<p align="center">
  A full-stack web application for managing the end-to-end operations of the University of Mindanao Dining Center — from kitchen production and inventory to point of sale and financial reporting.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12"/>
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2"/>
  <img src="https://img.shields.io/badge/PostgreSQL-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL"/>
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite"/>
  <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker"/>
</p>

---

## Overview

The UM Dining Center Operations Management System centralizes and digitizes the daily operations of a university cafeteria. It connects four user roles — **Admin**, **Cashier**, **Kitchen Manager**, and **Inventory Manager** — each with a tailored interface, into a single coherent platform that tracks everything from raw ingredient stock to end-of-day financial summaries.

---

## Features

### 🔐 Authentication & Security
- Email + password login with **rate limiting** (5 attempts per 2 minutes)
- **Two-Factor Authentication (2FA)** — a 6-digit OTP is emailed via the Brevo API after login; expires in 10 minutes
- Role-based access control via custom middleware; each role is scoped strictly to permitted routes
- Session regeneration on login and 2FA verification

### 🍳 Kitchen Production System
- Kanban-style board with **Queued → Cooking → Done / Wasted** workflow
- Start a production batch: validates ingredient stock sufficiency, then atomically deducts stock and creates a production log
- Mark as **Done**: increments finished product stock by the number of servings produced
- Mark as **Wasted**: records waste reason; deducted ingredients are not returned
- **Cancel** (queued only): refunds all deducted ingredients back to stock with full audit logging
- **Start Shift**: bulk stock-in of ingredients at the beginning of a shift
- **End Shift**: marks all in-progress batches as wasted with reason "End of shift"
- Full production history with search, date, and status filtering

### 📦 Inventory Management
- Full CRUD for ingredients: name, category, unit of measure, cost per unit, current stock, low-stock threshold
- Manual **Stock In** (with optional supplier name) and **Stock Out** (with required reason)
- Every stock change is written to a comprehensive `ingredient_audit_logs` table, recording old/new stock levels, old/new field values (JSON), who made the change, and when
- **Stock History** — filterable log of all stock in/out events by date, user, or ingredient name
- **Ingredient History** — log of all creates, edits, and deletes with before/after JSON snapshots
- **Stock Reconciliation** — compares actual stock against audit-log-derived expected stock to surface discrepancies

### 🛒 Point of Sale (POS)
- Product grid with category tabs and live search, paginated 12 per page
- Cart-based checkout supporting **Cash** and **GCash** payments
- Auto-generates sequential order IDs (`P-YYYYMMDD-NNNN`)
- Atomically validates and deducts product stock using database-level row locking
- Calculates and returns change amount
- Full **Transaction History** with search by order ID and date filtering

### 🍽️ Menu & Pricing
- CRUD for products: name, price, category (cooked/ready-made), image upload, stock level
- **Recipe Manager**: link ingredients to products with per-batch quantities; drives kitchen production stock deductions
- Log **product waste** with quantity and reason directly from the menu page
- **Pricing History** — full audit log of all price changes with previous and new values
- **Waste Logs** — log of all waste events by product

### 📊 Analysis & Reporting (Admin)

| Report | Description |
|---|---|
| **Financial Dashboard** | Revenue (total & today), cost of goods sold, gross profit, profit margin, top 5 selling products, 7-day sales trend chart |
| **Cost & Variance** | Per-ingredient comparison of theoretical usage (recipe × batches) vs actual usage (stock deductions), with variance %, and monetary loss/gain |
| **Yield & Forecasting** | Production success and waste rates, average daily sales, 7-day revenue projection, top produced products, waste reason breakdown |
| **End of Day** | Date-selectable daily summary covering POS sales, kitchen production, inventory movements, and net profit |

### 📤 CSV Export
Every major log and report can be exported to a UTF-8 CSV (BOM-encoded for Excel compatibility) with title, date range, and generation timestamp. Exports respect any active filters.

Exportable: POS History · Pricing History · Stock History · Ingredient History · Kitchen Logs · Waste Logs · Cost & Variance · Yield & Forecasting

### 👥 User Management
- Admin can create, edit, and activate/deactivate user accounts
- Roles: `admin`, `cashier`, `kitchen_manager`, `inventory_manager`

### 💾 Database Backup & Restore
- One-click manual backup using `pg_dump` via Artisan
- Download or delete backup files from the admin panel
- **Restore**: upload a `.sql` file; statements are parsed and executed inside a database transaction for safety

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Database | PostgreSQL |
| Frontend | Blade Templates, Vanilla JavaScript, Vite |
| Email / 2FA | Brevo (Sendinblue) HTTP API, Laravel Mail fallback |
| Packages | `spatie/laravel-backup`, `spatie/laravel-activitylog` |
| Deployment | Docker, Render.com |

---

## User Roles

| Role | Default Landing Page | Permissions |
|---|---|---|
| **Admin** | Financial Dashboard | Full access to all modules |
| **Cashier** | Point of Sale | POS + Transaction History |
| **Kitchen Manager** | Kitchen Production | Kitchen production + production logs |
| **Inventory Manager** | Inventory Management | Ingredient stock management |

---

## Local Development Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm
- PostgreSQL

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/airo-coder/UMDC-System.git
cd UMDC-System
```

**2. Install dependencies**
```bash
composer install
npm install
```

**3. Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=um_dining_center
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

For 2FA email, configure either:
```env
# Option A: Brevo API (recommended for production)
BREVO_API_TOKEN=your_brevo_api_key
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="UM Dining Center"

# Option B: SMTP (for local development)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
```

**4. Run migrations and seed the database**
```bash
php artisan migrate --seed
```

**5. Build frontend assets**
```bash
npm run build
```

**6. Start the development server**
```bash
composer dev
```
This starts the Laravel server, queue worker, and Vite dev server concurrently.

Open [http://localhost:8000](http://localhost:8000) in your browser.

---

## Docker

A `Dockerfile` is included for containerized deployment.

```bash
docker build -t umdc-system .
docker run -p 8000:8000 --env-file .env umdc-system
```

For deployment on **Render**, the `render-start.sh` script handles migrations and asset compilation automatically on startup.

---

## Default Credentials

After seeding, default accounts are created per role. Check the migration file `2026_03_17_080000_seed_default_users.php` for the seeded credentials, and **change all passwords immediately** before any production use.

---

## Database Schema (Key Tables)

```
users                     — accounts with role assignment
ingredients               — raw materials with stock and cost tracking
products                  — menu items with price and stock
recipes                   — ingredient → product mappings with quantities
kitchen_production_logs   — batch production records (Kanban)
kitchen_stock_deductions  — ingredient deductions per production batch
ingredient_audit_logs     — full audit trail for all ingredient changes
product_audit_logs        — full audit trail for all product/price changes
transactions              — POS sales records
transaction_items         — line items per transaction
```

---

## License

This project was developed as an academic project for the University of Mindanao. All rights reserved.
