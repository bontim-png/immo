# IMMO SaaS — V1 foundation

Modern PHP/MySQL real-estate SaaS foundation.

## Stack
- PHP 8.2+
- MySQL 8 / MariaDB 10.6+
- PDO
- Vanilla JS + CSS
- I18n: FR / EN / NL
- No framework dependency in V1

## Domain model
Office -> Agents -> Properties -> Property Photos

An agent can only be assigned to a property belonging to their office.

## Install
1. Create a MySQL database.
2. Import `database/schema.sql`.
3. Copy `config/config.example.php` to `config/config.php` and set database credentials.
4. Point the web server document root to `/public`.
5. Make `public/uploads/properties` writable by PHP.
6. Open `/`.

For Apache, `public/.htaccess` is included.

## First login
The initial database contains no authentication user. Authentication will be added in the next increment rather than shipping a hard-coded password.

## Git
Do not commit `config/config.php`, uploaded photos, or logs. See `.gitignore`.
