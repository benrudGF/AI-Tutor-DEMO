# AI-Tutor-DEMO

## Overview
PHP-based AI Tutor application using the Groq API.

## Stack
- **Language:** PHP 8.2
- **Server:** Built-in PHP development server on port 5000
- **AI API:** Groq (uses `GROQ_API_KEY` secret)

## Project Structure
- `index.php` - Main entry point
- `test.php` - Groq API key test page
- `auth.php` - Authentication logic
- `login.php` / `register.php` / `logout.php` - User auth pages
- `profile.php` - User profile
- `db.php` - Database connection
- `functions.php` - Shared utility functions
- `api_handler.php` - API request handling
- `prompt.php` - AI prompt handling
- `view_post.php` - Post viewing
- `admin/` - Admin panel
- `instruction.md` - Project instructions

## Secrets
- `GROQ_API_KEY` - Groq API key for LLM access

## Running
```
php -S 0.0.0.0:5000 -t .
```
