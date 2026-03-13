# AI-Tutor-DEMO — Agent Guide

Use this document as your primary reference when helping build, debug, or extend this project.

## Project Overview

AI-Tutor-DEMO is a PHP web application that provides an AI tutoring interface powered by the Groq API. Users register, log in, and interact with an AI tutor (llama-3.1-8b-instant) through a chat-like prompt page. The app is a procedural PHP monolith using Bootstrap 5 for the frontend and MySQL (via PDO) for persistence.

## Tech Stack

| Layer       | Technology                              |
|-------------|----------------------------------------|
| Language    | PHP 8.2                                |
| Frontend    | Bootstrap 5.3.0, Bootstrap Icons 1.10.0, vanilla JavaScript |
| Database    | MySQL (PDO)                            |
| AI API      | Groq API (OpenAI-compatible endpoint)  |
| AI Model    | llama-3.1-8b-instant                   |
| Server      | PHP built-in dev server                |
| Deployment  | Replit / Cloud Run                     |

## Directory Structure

```
/
├── index.php            # Home page + login handler
├── login.php            # Login page
├── register.php         # Registration page
├── logout.php           # Destroys session, redirects to login
├── profile.php          # User profile display (auth required)
├── prompt.php           # AI tutor chat interface (auth required)
├── api_handler.php      # Server-side proxy to Groq API (POST, auth required)
├── layout.php           # Base HTML template (nav, footer, Bootstrap CDN)
├── db.php               # PDO database connection
├── auth.php             # Authentication helpers (checkLoggedIn, etc.)
├── functions.php        # Shared utility functions
├── view_post.php        # Post viewing (stub — not yet implemented)
├── admin/
│   └── dashboard.php    # Admin dashboard (stub — not yet implemented)
├── attached_assets/     # Screenshots and media
├── .replit              # Replit run/deploy config
├── replit.nix           # Nix packages (php82)
├── replit.md            # Project readme
└── instruction.md       # Links to external project spec
```

## How to Run

```bash
php -S 0.0.0.0:5000 -t .
```

The app has no build step. Just start the PHP dev server and open the browser.

## Environment Variables (Required)

Set these as secrets/environment variables before running:

| Variable      | Purpose                     |
|---------------|-----------------------------|
| `GROQ_API_KEY`| Bearer token for Groq API   |
| `DB_HOST`     | MySQL host                  |
| `DB_NAME`     | MySQL database name         |
| `DB_USER`     | MySQL username              |
| `DB_PASS`     | MySQL password              |

## Database

### Connection

`db.php` creates a PDO connection using the env vars above. Every page that needs the database includes this file.

### Schema

There is no migration file. The `users` table must be created manually:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);
```

When adding new tables or columns, create them in the database directly and document the schema change here.

## Architecture & Patterns

### Page Rendering (Template Pattern)

Every page follows this pattern:

```php
<?php
// 1. Includes
require_once 'db.php';
require_once 'auth.php';

// 2. Auth check (if protected)
checkLoggedIn();

// 3. Handle POST logic (forms, etc.)

// 4. Start output buffer
ob_start();
?>
<!-- HTML content here -->
<?php
// 5. Capture buffer and render layout
$content = ob_get_clean();
include 'layout.php';
?>
```

`layout.php` provides the `<html>`, `<head>`, navbar, footer, and Bootstrap assets. It echoes `$content` in the main `<div class="container">`.

### Authentication

- **Session-based**: PHP `$_SESSION` with `user_id` and `username`.
- **Password hashing**: bcrypt via `password_hash()` / `password_verify()`.
- **Route protection**: `checkLoggedIn()` redirects unauthenticated users to `login.php`.
- Public pages: `index.php`, `login.php`, `register.php`.
- Protected pages: `prompt.php`, `profile.php`.

### AI Integration (Groq API)

The frontend (`prompt.php`) sends a JSON POST to `api_handler.php`, which proxies the request to `https://api.groq.com/openai/v1/chat/completions`.

Key parameters sent to Groq:
- `model`: `"llama-3.1-8b-instant"`
- `temperature`: `0.7`
- `max_tokens`: `1024`
- `messages`: array of `{role, content}` objects

The response is returned as JSON to the frontend, which renders it dynamically via vanilla JS `fetch()`.

## Coding Conventions

- **No framework** — plain procedural PHP. Keep it simple.
- **Includes over autoloading** — use `require_once` for shared files.
- **Inline SQL** — queries live in the page files, not in separate model classes. Use PDO prepared statements to prevent SQL injection.
- **Bootstrap for UI** — use Bootstrap 5 classes for layout, forms, buttons, cards, alerts. No custom CSS framework.
- **Vanilla JS** — no jQuery, no React. Use `fetch()` for AJAX calls.
- **htmlspecialchars()** — always escape user-provided data before echoing into HTML.

## Key Files to Understand

| File             | What it does                                              |
|------------------|-----------------------------------------------------------|
| `layout.php`     | Base template — change this to update nav, footer, or global assets |
| `db.php`         | Database connection — change this if switching DB engines  |
| `auth.php`       | Auth helpers — add new auth functions here                |
| `api_handler.php`| Groq API proxy — modify to change AI behavior, model, or parameters |
| `prompt.php`     | Chat UI — the core user-facing feature                    |

## Common Tasks

### Add a new page

1. Create `newpage.php` in the root directory.
2. Include `db.php` and `auth.php` if needed.
3. Call `checkLoggedIn()` if the page requires authentication.
4. Use `ob_start()` / `ob_get_clean()` + `include 'layout.php'` for consistent layout.
5. Add a link in the navbar inside `layout.php`.

### Add a new database table

1. Write and run the `CREATE TABLE` SQL directly on the database.
2. Document the schema in this file.
3. Use PDO prepared statements for all queries.

### Change the AI model or behavior

Edit `api_handler.php`:
- Change the `model` field to use a different Groq model.
- Adjust `temperature`, `max_tokens`, or add a system prompt in the `messages` array.

### Add form validation

- Server-side: validate in the PHP POST handler before database operations.
- Client-side: use HTML5 `required`, `type`, `pattern` attributes and Bootstrap validation classes.

## Stubs / Incomplete Features

These files exist but are empty or non-functional — they are ready to be built out:

- **`view_post.php`** — intended for viewing posts/content. Needs implementation.
- **`admin/dashboard.php`** — intended for admin functionality. Needs implementation.

## Known Issues & Improvement Opportunities

- **No CSRF protection** — forms should include CSRF tokens.
- **No database migrations** — schema changes are manual. Consider adding a `schema.sql` file.
- **No test suite** — no automated tests exist yet.
- **Session stores password** — `$_SESSION['login_password']` stores the plain-text password; this should be removed.
- **No input rate limiting** — the Groq API proxy has no rate limiting or abuse prevention.
- **No conversation history** — the AI chat does not persist or display previous messages across sessions.

## Testing

There is no test suite yet. To add tests, consider:
- PHPUnit for backend logic.
- Manual browser testing for UI flows.
- Test the Groq API integration by sending a POST to `api_handler.php` with a JSON body: `{"message": "Hello"}`.
