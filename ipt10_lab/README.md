# IPT10 Lab: From mysqli to PDO

A student record system (CRUD) built twice in PHP: once with procedural
**mysqli** and once with object-oriented **PDO**. Individual laboratory
activity for IPT10 Database Programming.

## Contents

| Folder | Description |
|---|---|
| `ipt10_lab/` | mysqli version (`db_connect.php`) |
| `ipt10_lab_pdo/` | PDO version (`config.php`) |
| `database.sql` | Creates the `ip10_lab` database, `students` table and sample data |

## Requirements

- XAMPP (Apache, MySQL/MariaDB, PHP 8.2+)
- A modern browser
- Internet connection (Bootstrap is loaded from a CDN)

## 1. Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/download.html
2. Run the installer and select Apache, MySQL and PHP.
3. Open the XAMPP Control Panel and start **Apache** and **MySQL**.

## 2. Enable the PHP extensions

1. In the Control Panel, click **Config** next to Apache, then **PHP (php.ini)**.
2. Make sure these lines are uncommented (no leading `;`):
3. Restart Apache.
4. Create `phpinfo.php` in `htdocs` containing `<?php phpinfo();` and open
   `http://localhost/phpinfo.php` to confirm both `mysqli` and `PDO` show as enabled.

## 3. Create the database

1. Open `http://localhost/phpmyadmin`.
2. Click the **SQL** tab, paste the contents of `database.sql`, and click **Go**.
3. Confirm the `ip10_lab` database and its `students` table (3 sample rows) exist.

> Note: the collation is `utf8mb4_general_ci` because XAMPP's MariaDB does not
> support `utf8mb4_0900_ai_ci`.

## 4. Run the application

1. Copy `ipt10_lab/` and `ipt10_lab_pdo/` into `C:\xampp\htdocs\`.
2. Open in your browser:
   - mysqli version: http://localhost/ipt10_lab/index.php
   - PDO version: http://localhost/ipt10_lab_pdo/index.php

If your MySQL root user has a password, set it in `db_connect.php` (mysqli)
and `config.php` (PDO).

## Features

- List, view, create, edit and delete students (UUID primary keys)
- Prepared statements in both versions
- Server-side validation with errors shown next to each field
- Two-step delete (confirmation screen, then POST)
- Bootstrap 5 styling

## Notes on the PDO version

`PDO::ATTR_AUTOCOMMIT` is set to `false`, so every write calls `commit()`
explicitly (and `rollBack()` on failure).