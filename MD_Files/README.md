# 🧩 Component-Level Documentation

This folder contains **one MD file per source file**. Each MD is a complete, self-contained spec for the AI coding assistant to implement exactly that one file — nothing more, nothing less.

## How To Use

1. Pick the next MD in order (see table below).
2. Paste that MD's full content into Cursor / Antigravity along with the prompt:
   > *"Implement the file described in this MD. Follow it exactly. Do not modify any other file. After you finish, run the Health Check at the bottom."*
3. The AI implements just that file. You verify the health check.
4. Move to the next MD.

## ⚠️ Mandatory First Reads

Before touching any code, the AI must read **all 4 files in `00-rules/`**. These set the global standards every component obeys.

## Build Order

| # | File | Output |
|---|------|--------|
| 1 | `00-rules/00.1-GOLDEN-RULES.md` | — |
| 2 | `00-rules/00.2-FOLDER-STRUCTURE.md` | — |
| 3 | `00-rules/00.3-TECH-STACK.md` | — |
| 4 | `00-rules/00.4-SCOPE-BOUNDARIES.md` | — |
| 5 | `01-database/01.1-SCHEMA-SQL.md` | `database/schema.sql` |
| 6 | `02-core/02.1-CONFIG-PHP.md` | `includes/config.php` |
| 7 | `02-core/02.2-DB-PHP.md` | `includes/db.php` |
| 8 | `02-core/02.3-HELPERS-PHP.md` | `includes/helpers.php` |
| 9 | `02-core/02.4-AUTH-HELPERS-PHP.md` | `includes/auth_helpers.php` |
| 10 | `03-layout/03.1-HEADER-PHP.md` | `includes/header.php` |
| 11 | `03-layout/03.2-FOOTER-PHP.md` | `includes/footer.php` |
| 12 | `04-auth/04.1-REGISTER-PHP.md` | `auth/register.php` |
| 13 | `04-auth/04.2-LOGIN-PHP.md` | `auth/login.php` |
| 14 | `04-auth/04.3-LOGOUT-PHP.md` | `auth/logout.php` |
| 15 | `05-public/05.1-INDEX-PHP.md` | `index.php` |
| 16 | `05-public/05.2-MENU-PHP.md` | `menu.php` |
| 17 | `05-public/05.3-ABOUT-PHP.md` | `about.php` |
| 18 | `06-reservation/06.1-RESERVATION-PHP.md` | `reservation.php` |
| 19 | `07-user-panel/07.1-DASHBOARD-PHP.md` | `user/dashboard.php` |
| 20 | `07-user-panel/07.2-CANCEL-RESERVATION-PHP.md` | `user/cancel_reservation.php` |
| 21 | `08-admin/08.1-ADMIN-DASHBOARD-PHP.md` | `admin/dashboard.php` |
| 22 | `08-admin/08.2-MENU-MANAGE-PHP.md` | `admin/menu_manage.php` |
| 23 | `08-admin/08.3-TABLE-MANAGE-PHP.md` | `admin/table_manage.php` |
| 24 | `08-admin/08.4-RESERVATION-MANAGE-PHP.md` | `admin/reservation_manage.php` |
| 25 | `09-contact/09.1-CONTACT-PHP.md` | `contact.php` |
| 26 | `09-contact/09.2-ADMIN-MESSAGES-PHP.md` | `admin/messages.php` |
| 27 | `10-styling/10.1-STYLE-CSS.md` | `assets/css/style.css` |
| 28 | `10-styling/10.2-MAIN-JS.md` | `assets/js/main.js` |

## What's NOT Here

The original module-level docs (`docs/00-PROJECT-OVERVIEW.md` … `docs/10-FINAL-CHECKLIST.md`) stay as quick reference. The component-level files in this folder ARE the source of truth for implementation.

## Each Component MD Has The Same 9 Sections

1. **📋 File Info** — path, type, dependencies, who uses it
2. **🎯 Purpose** — one sentence
3. **📦 Required Behavior** — what it does
4. **🔧 Interface** — what it exposes
5. **🚫 Must NOT Do** — anti-requirements
6. **📐 Code Skeleton** — names + signatures, not full code
7. **🧹 Clean Code Reminders** — file-specific style rules
8. **🧪 Health Check** — exact test steps + checklist
9. **✅ Done When** — single completion line

This consistency means the AI knows exactly where to look for any piece of info.
