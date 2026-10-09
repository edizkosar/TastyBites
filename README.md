# TastyBites 🍽️

A restaurant web application for browsing menus, making table reservations, and managing orders — built with PHP and MySQL.

---

## Features

- **Menu** — Browse food & drink items by category
- **Reservations** — Book a table with date, time and guest count
- **User Dashboard** — View and cancel your reservations
- **Admin Panel** — Manage menu items, tables, reservations, and contact messages
- **Authentication** — Register, login, logout with session management
- **Contact** — Send messages to the restaurant

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP |
| Database | MySQL |
| Frontend | HTML, CSS, JavaScript |
| Auth | PHP Sessions |

---

## Project Structure

```
tastybites/
├── index.php              # Home page
├── menu.php               # Menu listing
├── about.php              # About page
├── reservation.php        # Make a reservation
├── contact.php            # Contact form
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── user/
│   ├── dashboard.php      # User reservations
│   └── cancel_reservation.php
├── admin/
│   ├── dashboard.php      # Admin overview
│   ├── menu_manage.php    # CRUD for menu items
│   ├── table_manage.php   # Manage tables
│   ├── reservation_manage.php
│   └── messages.php       # Contact messages
├── includes/
│   ├── config.php         # DB credentials
│   ├── db.php             # Database connection
│   ├── helpers.php
│   ├── auth_helpers.php
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/style.css
│   └── js/main.js
└── database/
    └── schema.sql         # Database schema
```

---

## Setup

### Requirements
- PHP 7.4+
- MySQL 5.7+
- Apache / XAMPP / WAMP

### Installation

1. Clone the repo into your web server directory (e.g. `htdocs/`)
2. Import the database schema:
   ```sql
   mysql -u root -p < database/schema.sql
   ```
3. Configure database credentials in `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'tastybites');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
4. Visit `http://localhost/tastybites`

---

## Developed By

Ediz — Computer Engineering, Yeditepe University
