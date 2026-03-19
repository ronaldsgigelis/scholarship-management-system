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

## Database Notes

- The database schema was created based on the original school task requirements.
- The `students` table uses `first_name`, `last_name`, and `personal_code`.
- The `stipend_periods` table uses `year`, `period`, and `period_group`.
- If the database was imported from an older version of the project, recreate the database or re-import `database/migrations/001_init.sql`.

## Scholarship Calculation Rules

- Professional subject (`P`) is failed if grade is below `5`.
- General subject (`V`) is failed if grade is below `4`.
- Average grade is calculated from all entered grades for the selected stipend period.
- If no grades are entered for a student, scholarship calculation is not performed.
- If absences are from `2` to `8` inclusive, base stipend is `15.00`.
- If absences are `9` or more, base stipend is `0.00`.
- If failed subjects count is `2` or more, base stipend is `0.00`.
- Otherwise:
  - average grade `>= 8.0` => `81.00`
  - average grade `>= 6.0` and `< 8.0` => `41.00`
  - average grade `>= 4.0` and `< 6.0` => `16.00`
  - average grade `< 4.0` => `0.00`
- Total stipend = base stipend + activity bonus.

## Activity Bonus Rule

- Activity bonus is entered manually.
- Activity bonus is stored for a `6`-month period.
- The selected stipend period is treated as the start month of that `6`-month range.
- `period_start` is the first day of the selected month.
- `period_end` is the last day of the sixth month in that range.

## Search and Filters

The Search / History module supports these filters:

- `year`
- `period`
- `period_group`
- `group`
- `student name text search`
- `student exact select`

Results include saved scholarship values and a detail page for each saved record.

## XAMPP Usage

This project is intended to run locally in XAMPP at:

```text
http://localhost/stipendiju_sistema/public
```

## Implementation Note

The stipend period structure follows the fields `year`, `period`, and `period_group`. For the activity bonus `6`-month date range logic, the project assumes that `period` contains a month-style value such as `January`, `February`, or `March`, because calendar dates are derived from it.