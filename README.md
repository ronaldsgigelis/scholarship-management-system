# Scholarship Management System

Plain PHP MVC school project for XAMPP. The project manages student groups, subjects, stipend periods, scholarship entry, saved scholarship results, and search/history for previously saved calculations.

## Technologies

- PHP 8+
- MySQL
- PDO
- Bootstrap 5
- Apache via XAMPP
- Interface language support: English and Latvian

## Architecture

The project uses a simple MVC structure without a framework.

- `app/controllers` contains controllers
- `app/models` contains database access logic
- `app/views` contains Bootstrap-based PHP views
- `config` contains configuration such as the PDO database connection
- `routes/web.php` contains route definitions
- `public/index.php` is the entry point
- `database/migrations/001_init.sql` contains the database schema

## Setup

1. Start Apache and MySQL in XAMPP.
2. Create the database and import `database/migrations/001_init.sql` in phpMyAdmin, or run:

```sql
SOURCE database/migrations/001_init.sql;
```

3. Check database credentials in `config/Database.php`.
4. Open the project in the browser:

```text
http://localhost/stipendiju_sistema/public
```

English is the default interface language. Users can switch the interface to Latvian from the visible `EN | LV` switch in the UI.

## Main Modules

- Groups
- Students
- Subjects
- Group Subjects
- Stipend Periods
- Stipend Entry
- Search / History
- Stipend Result Detail
