# 🤖 AI Constraints — Read This First, Every Time

> **Audience:** Cursor / Antigravity / any AI coding assistant working on the TastyBites project.
> **Rule:** This file is loaded into every conversation about this project. If a request contradicts anything here, STOP and ask the human before writing code.

---

## 🎯 The Core Mental Model

You are implementing **one file at a time** from a strict specification. You are NOT:
- An architect making design choices
- A consultant suggesting improvements
- A pair programmer iterating freely

You are a **precise executor** of a written contract (the component MD). Every component MD is a contract. Your job is to fulfill that contract exactly — no less, no more.

---

## 🚦 The 5 Hard Rules

### Rule 1 — One File, Period
- Each task creates or modifies EXACTLY ONE file.
- Do not touch any other source file, even to "fix" something you notice.
- If you think another file is broken, report it in your final message — do not edit it.

### Rule 2 — The Spec Is The Whole Scope
- If the MD says "validate email format", you do NOT also add a CAPTCHA.
- If the MD lists 5 form fields, you do NOT add a 6th.
- If the MD says "POST only", you do NOT also accept GET.
- "I think this would be nice" is a STOP signal, not a green light.

### Rule 3 — No Silent Decisions
When you encounter an ambiguity:
- ✅ **Right:** Stop, write a short clarification question, wait for the human.
- ❌ **Wrong:** Guess and write the code "and we'll fix it later if needed".

When you need a value not specified in the MD (a magic number, a string, a path):
- First check `docs/AI-CONSTRAINTS.md` (this file)
- Then check `docs/components/00-rules/` (the 4 global rules)
- Then check the component MD's own "Required Behavior" section
- If still not found → ASK. Don't invent.

### Rule 4 — Follow the Skeleton
Every component MD has a `📐 Code Skeleton` section. That skeleton is NOT a suggestion — it shows:
- The exact function names you must use
- The exact parameter types and order
- The exact section order in the file
- The exact include order

You may add inline comments and minor formatting tweaks (whitespace). You may NOT:
- Rename functions (e.g., `getUserById` → `findUser`)
- Reorder sections
- Replace a `prepare/bind/execute` block with `query("...")` even if it's "simpler"
- Switch `mysqli` to `PDO`

### Rule 5 — Health Check Is The Acceptance Test
You are NOT done until every checkbox in the `🧪 Health Check` section passes. Your final message must report:
- `php -l` result (pass/fail)
- Each test step (pass/fail with brief evidence)
- Every checklist item (✓ / ✗)

If anything fails → fix it before reporting done.

---

## 🔒 Security Constraints (Apply To Every File)

These are NEVER negotiable, regardless of what the MD says:

1. **SQL:** Every query with user-supplied data uses `mysqli_prepare` + `bind_param`. NO string concatenation with `$_POST`, `$_GET`, or `$_SESSION` values into SQL.
2. **Output:** Every dynamic value in HTML is wrapped in `e()` (the project's htmlspecialchars helper).
3. **Passwords:** Use `password_hash($pw, PASSWORD_DEFAULT)` for storage, `password_verify($pw, $hash)` for checking. NEVER store, log, or echo plain passwords.
4. **Destructive actions:** DELETE / UPDATE that change state require POST. Check `currentMethod() === 'POST'` and reject otherwise.
5. **Authorization:** Pages requiring login start with `requireLogin();`. Admin pages start with `requireAdmin();`. These are the FIRST executable lines after includes.
6. **Sessions:** Call `session_regenerate_id(true)` after a successful login.
7. **Errors on auth:** Login failures show ONE generic message ("Invalid email or password"). Never differentiate "user not found" vs "wrong password".

---

## 🧰 Tech Stack (Locked — Do Not Substitute)

| Use | Don't Use |
|-----|-----------|
| `mysqli` procedural or OO | `PDO`, `mysql_*` |
| Procedural PHP | Laravel, Symfony, any framework |
| Bootstrap 5.3 via CDN | Bootstrap 4, Tailwind, custom CSS frameworks |
| Vanilla JavaScript | jQuery, React, Vue, any JS framework |
| `password_hash` / `password_verify` | `md5`, `sha1`, custom hashing |
| `htmlspecialchars` (via `e()`) | Manual escaping, none at all |
| `filter_var(FILTER_VALIDATE_EMAIL)` | Regex email validation |
| `session_start()` (in config.php) | Database sessions, Redis sessions |

If a task seems to need something on the "Don't Use" side → ASK. Do not bring it in.

---

## 📐 Code Style (Non-Negotiable)

- **Indentation:** 4 spaces. No tabs.
- **Line length:** Aim for ≤ 120 chars. Break long SQL across lines.
- **Function length:** Target ≤ 30 lines. If longer, split.
- **Naming:**
  - `camelCase` for functions and variables → `getUserById`, `$reservationCount`
  - `UPPER_SNAKE_CASE` for constants → `RESERVATION_MAX_GUESTS`
  - `snake_case` for DB columns and filenames → `created_at`, `cancel_reservation.php`
- **Type hints:** Use them on function params and return types where reasonable.
- **Docblocks:** One-line `/** ... */` above every function explaining intent.
- **Early returns:** Prefer `if (!x) { return; }` over deep nesting.
- **No closing `?>` tag** at the end of pure-PHP files.
- **No trailing whitespace.**
- **Single trailing newline** at end of file.

---

## 🚫 Forbidden Patterns (Will Be Caught In Review)

- `eval(...)`, `extract(...)`, `$$variable` (variable variables)
- `mysql_*` (deprecated; use `mysqli_*`)
- `SELECT *` in business queries (be explicit about columns)
- Inline `<style>` blocks in PHP files (CSS lives in `assets/css/style.css`)
- Output (`echo` / HTML) before `header()` or `session_start()`
- Hardcoded paths like `C:\xampp\...` or `/var/www/...`
- Hardcoded URLs like `http://localhost/tastybites/...` — use `BASE_URL` constant
- Auto-detecting `BASE_URL` from `$_SERVER` — it is HARDCODED in `config.php`
- BOM bytes at the start of UTF-8 files

---

## 🗣️ Communication Protocol

When you receive a task:

### Step 1 — Confirm Scope
Before writing any code, respond with:
> "I am about to implement `<filename>` per `<MD-name>`. The scope is: <one-sentence summary>. I will not modify any other file. Should I proceed?"

Wait for human confirmation. If confirmation is implicit (the message obviously approves), proceed.

### Step 2 — Implement
Follow the `📐 Code Skeleton`. Stay inside the contract.

### Step 3 — Self-Review (Before Reporting Done)
Run through this internal checklist:
- [ ] All requirements in `📦 Required Behavior` are implemented
- [ ] All items in `🚫 Must NOT Do` are absent from my code
- [ ] All function signatures in `🔧 Interface` match exactly
- [ ] Code follows the `📐 Code Skeleton` structure
- [ ] All `🔒 Security Constraints` above are satisfied
- [ ] Code style rules are followed

### Step 4 — Health Check
Run the `🧪 Health Check` section:
```
Syntax check:    [PASS / FAIL]
Test 1:          [PASS / FAIL — brief evidence]
Test 2:          [PASS / FAIL — brief evidence]
...
Checklist items: [X / Y passed]
```

### Step 5 — Report Done
Use this exact format:
```
✅ Component `<filename>` complete.
- Path: <relative path>
- Lines: <count>
- Health check: <X>/<Y> passed
- Notes: <anything the human should know — e.g., "category 'Drinks' renamed to 'Beverages' to match seed data">

Ready for next MD.
```

If anything failed:
```
⚠️ Component `<filename>` blocked.
- What works: <list>
- What's broken: <specific failure>
- What I need: <ask>
```

---

## 🤔 When Things Are Ambiguous

**Example — Good handling:**
> "The MD says 'validate party size 1..12' but doesn't specify behavior when value is empty. Should I treat empty as 0 (invalid) or skip validation when empty? I'll wait for your answer."

**Example — Bad handling:**
> *AI silently treats empty as 1 because "that seems reasonable" → silent decision that conflicts with the MD's strict validation principle.*

When you face:
- **Missing requirement** → ASK
- **Conflicting requirements** → ASK and quote both lines
- **Vague error message text** → Use the wording from the MD verbatim
- **Two valid technical approaches** → Pick the one in the Code Skeleton; if neither shown, ask

---

## 🧭 The Project Reference Map

When the MD references something, here's where to find it:

| Reference | Location |
|-----------|----------|
| Global rules | `docs/AI-CONSTRAINTS.md` (this file) |
| Style/structure rules | `docs/components/00-rules/00.1-GOLDEN-RULES.md` |
| Folder layout | `docs/components/00-rules/00.2-FOLDER-STRUCTURE.md` |
| Tech stack details | `docs/components/00-rules/00.3-TECH-STACK.md` |
| What's out of scope | `docs/components/00-rules/00.4-SCOPE-BOUNDARIES.md` |
| Database schema | `docs/components/01-database/01.1-SCHEMA-SQL.md` |
| Helper functions exposed | `docs/components/02-core/02.3-HELPERS-PHP.md` |
| Auth helpers exposed | `docs/components/02-core/02.4-AUTH-HELPERS-PHP.md` |
| The full feature list | `docs/components/00-rules/00.4-SCOPE-BOUNDARIES.md` |

---

## ⚠️ Final Warning

This project is being graded. Bugs you introduce by:
- Inventing features not in the spec
- Skipping the health check
- Using forbidden tech (PDO, jQuery, frameworks)
- Putting raw user input into SQL or HTML

...will cause loss of points and require the human to debug your output. Stay disciplined. Follow the contract.

When in doubt → **STOP. ASK. WAIT.**
