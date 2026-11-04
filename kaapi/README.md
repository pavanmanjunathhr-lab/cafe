# Kaapi Katte - PHP + MySQL Indian coffee house site

Requires PHP 8.0+ (PDO MySQL) and MySQL/MariaDB.

## Setup
1. Import the database:  mysql -u root -p < schema.sql   (or paste schema.sql into phpMyAdmin)
2. Edit config.php: DB user/password, and ADMIN_PASSWORD.
3. Copy this folder into your web root (htdocs / www) and open index.php,
   or run locally:  php -S localhost:8000
4. Admin (bookings): /admin/

## Structure
check.php            guests check booking status (reference + phone)
index.php            home page: hero animation, menu from DB, booking form
admin/index.php      sign in and manage reservations
includes/            header, footer, hero SVG, DB + helper functions
assets/style.css     all styling and animations
assets/app.js        menu tabs
schema.sql           tables + sample menu (prices in rupees)

Edit the menu in phpMyAdmin (tables: categories, menu_items).
Set is_available = 0 to hide an item.

## Upgrading from the first version
If you already imported the earlier schema.sql, run upgrade_add_ref.sql once instead of re-importing.
