# FabricFlow

FabricFlow is a secure, responsive operations dashboard for small textile and fabric businesses. It brings orders, inventory, customers, logistics, employees, attendance, machine assignments, and printable invoices into one straightforward PHP application.

## What this project demonstrates

- Transaction-safe order processing with row locking and stock validation
- Historical item pricing, so invoices remain accurate after catalog prices change
- Inventory restoration when orders are deleted
- Secure authentication using modern password hashing and hardened sessions
- CSRF protection on every state-changing action
- Prepared SQL statements, output escaping, and controlled error handling
- Relational data integrity through foreign keys, unique constraints, checks, and migrations
- Reusable PHP layouts, validation helpers, and CRUD components
- Responsive administration UI with dashboard metrics and low-stock alerts

## Features

| Area | Capabilities |
|---|---|
| Orders | Create orders, reserve inventory, update status, view history, delete safely |
| Inventory | Manage fabrics, prices, quantities, and low-stock alerts |
| Customers | Search, create, edit, delete, and inspect order history |
| Carriers | Maintain logistics partners and view assigned orders |
| Employees | Maintain employee records and daily attendance |
| Machines | Manage equipment and employee assignments |
| Invoices | Preserve sale-time prices and generate print-friendly invoices |
| Administrators | Protected account creation and safe account management |

## Technical overview

```text
Browser
   │
   ├── PHP pages and reusable UI components
   │      ├── authentication and CSRF validation
   │      ├── business rules and transactions
   │      └── prepared database operations
   │
   └── MySQL
          ├── orders and historical line-item prices
          ├── inventory and operational records
          └── foreign keys, unique indexes, and checks
```

### Stack

- PHP 8.1+
- MySQL 8+
- HTML5 and responsive CSS
- Native `mysqli` with prepared statements
- No framework or build tooling required

## Quick start

Requirements:

- PHP 8.1 or newer with the `mysqli` extension
- MySQL 8 or newer

From the project directory:

```bash
brew services start mysql
mysql -u root -e "CREATE DATABASE IF NOT EXISTS fabricflowwsystem CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -u root fabricflowwsystem < fabricflowsystem.sql
php -S localhost:8000
```

Open <http://localhost:8000/signup.php> and create the first administrator. Public registration closes automatically after the first account; later accounts must be created by a signed-in administrator.

The SQL file contains fictional demo records using reserved `example.com` addresses.

## Configuration

FabricFlow reads database configuration from environment variables:

| Variable | Default |
|---|---|
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_NAME` | `fabricflowwsystem` |
| `DB_USER` | `root` |
| `DB_PASS` | empty |

For example:

```bash
DB_USER=fabricflow DB_PASS=your-password php -S localhost:8000
```

Never commit real passwords or a local `.env` file.

## Updating an older database

Back up the database and run the migrations in order:

```bash
mysqldump -u root fabricflowwsystem > fabricflowwsystem-backup.sql
mysql -u root fabricflowwsystem < migrations/001_modernize.sql
mysql -u root fabricflowwsystem < migrations/002_preserve_history.sql
mysql -u root fabricflowwsystem < migrations/003_audit_fields.sql
```

These migrations preserve existing records, deduplicate attendance and machine assignments, backfill historical prices, protect order history, normalize statuses, and add audit timestamps.

## Security design

- Passwords use PHP's current recommended hashing algorithm.
- Session identifiers rotate after successful authentication.
- Cookies are HTTP-only and use `SameSite=Lax`.
- Every write action requires a CSRF token.
- User input is validated and SQL values are parameterized.
- Dynamic sorting fields are explicitly whitelisted.
- Internal database errors are logged rather than shown in the browser.
- Customers and fabrics referenced by order history cannot be deleted.

For a real deployment, serve the application over HTTPS, disable PHP error display, use a restricted MySQL account, and keep secrets in the hosting environment.

## Project structure

```text
assets/                 Shared responsive styling
includes/               Database, security, layout, and CRUD helpers
migrations/             Ordered upgrades for older databases
fabricflowsystem.sql    Clean schema with fictional demo data
admin_dashboard.php     Operational dashboard
add_order.php           Transactional order creation
manage_*.php            Administration workflows
invoice.php             Historical, print-friendly invoices
```

## Verification

The project has been checked with PHP syntax validation, clean-database imports, authenticated route smoke tests, CRUD exercises, attendance upserts, order inventory reduction/restoration, invoice rendering, and database-constraint inspection.
