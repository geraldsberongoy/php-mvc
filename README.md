# php-mvc

A lightweight PHP MVC classroom management application with role-based access for **admin**, **teacher**, and **student** users.

## Features

- Role-based authentication and dashboards
- Classroom management (create, edit, archive, enroll/unenroll)
- Classroom posts and comments for teacher/student workflows
- Activity logging for admin visibility
- Tailwind CSS + PostCSS frontend pipeline

## Tech Stack

- PHP (custom MVC structure)
- MySQL/MariaDB
- Composer dependencies:
  - `nikic/fast-route`
  - `twig/twig`
  - `vlucas/phpdotenv`
- Node tooling:
  - Tailwind CSS
  - PostCSS

## Repository Structure

```text
php-mvc/
├── framework/
│   ├── app/            # Application controllers and models
│   ├── src/            # Core framework classes
│   ├── routes/         # Route definitions
│   ├── views/          # Twig/PHP views
│   ├── public/         # Web entrypoint and assets
│   ├── config/         # App and database config
│   ├── schema.txt      # Database schema reference
│   └── *.sql           # SQL scripts/migrations
└── README.md
```

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+ and npm
- MySQL/MariaDB

## Setup

From `framework/`:

1. Install PHP dependencies:
   - `composer install`
2. Install frontend dependencies:
   - `npm install`
3. Create a `.env` file in `framework/` and configure:
   - `DB_DRIVER` (default: `mysql`)
   - `DB_HOST` (default: `localhost`)
   - `DB_PORT` (defaults to `3307` to match `framework/config/database.php`; use `3306` for standard local MySQL)
   - `DB_NAME` (default: `demo`)
   - `DB_USER` (default: `root`)
   - `DB_PASS` (default: empty)
4. Prepare the database schema using your SQL tooling and the provided schema/scripts.

## Running the App

From `framework/`:

- Build CSS once: `npm run build`
- Watch CSS during development: `npm run dev`
- Start PHP server: `php -S localhost:8000 -t public`

Then open: `http://localhost:8000`

## Notes

- See `framework/CONTROLLER_REFACTORING_SUMMARY.md` for recent controller architecture changes.
