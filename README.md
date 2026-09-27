# Cafe POS System

A simple, modern, responsive Café Point-of-Sale system built with plain
**HTML5, Bootstrap 5, vanilla JavaScript, PHP and MySQL** — no frameworks
(no React, Node, Laravel, Django). Designed to run on **XAMPP** or **WAMP**.

## Features

- Secure staff login (bcrypt password hashing, PHP sessions), with **admin**
  and **staff** roles
- Dashboard: today's sales, today's bills, product/customer counts, a 7-day
  sales chart, and recent bills
- POS screen: product grid with category filters and search, live cart with
  quantity controls, discount, tax, subtotal/total calculation
- Customer details capture (optional — walk-in by default)
- Bill generation with a unique bill number (`CA-YYYYMMDD-NNNN`), multiple
  payment methods (Cash / UPI / Card / Other), automatic change calculation
  for cash
- Hold-bill support, and a clean thermal-printer-friendly receipt (`@media
  print`)
- Bill history with search, and a customer list with order counts/totals
- Admin product management (add / edit / activate–deactivate / delete)
- Admin sales reports (today / 7 days / 30 days) with a trend chart and top
  products table
- Fully responsive — works well on desktop and tablet

## Project Structure

```
cafe-pos/
├── index.php              # redirects to dashboard or login
├── login.php               # login form
├── logout.php
├── dashboard.php
├── setup.php                # ONE-TIME: sets default passwords, then delete it
│
├── config/database.php      # PDO/MySQL connection settings
├── includes/                # header, sidebar, footer, auth helpers
├── assets/css/style.css
├── assets/js/{app,pos,products,reports}.js
│
├── pages/
│   ├── pos.php               # main POS screen
│   ├── bills.php              # bill history
│   ├── customers.php
│   ├── products.php           # admin only
│   ├── reports.php             # admin only
│   ├── settings.php            # admin only (change password)
│   └── print_bill.php          # printable receipt
│
├── api/
│   ├── login.php
│   ├── products.php            # GET list, POST create/update
│   ├── delete_product.php
│   ├── create_bill.php
│   ├── get_bill.php
│   └── reports.php
│
└── database/cafe_pos.sql       # schema + seed data
```

## Setup (XAMPP / WAMP)

1. Copy the `cafe-pos` folder into your server's web root:
   - XAMPP: `C:\xampp\htdocs\cafe-pos`
   - WAMP: `C:\wamp64\www\cafe-pos`
2. Start **Apache** and **MySQL** from the XAMPP/WAMP control panel.
3. Open **phpMyAdmin** and import `database/cafe_pos.sql`
   (or run `mysql -u root -p < database/cafe_pos.sql` from a terminal).
   This creates the `cafe_pos` database with sample categories and
   products.
4. If your MySQL root user has a password, edit `config/database.php`
   and set `DB_PASS` accordingly (XAMPP's default is an empty password).
5. **First-time setup:** open `http://localhost/cafe-pos/setup.php` in
   your browser once. This sets working bcrypt passwords for the two
   seeded accounts:
   - `admin` / `admin123` (role: admin)
   - `staff` / `staff123` (role: staff)

   Then **delete `setup.php`** — it's only meant to run once.
6. Go to `http://localhost/cafe-pos/login.php` and log in.

## Notes

- Update the café name, address and phone number printed on receipts in
  `pages/print_bill.php` (near the top of the file).
- Product images are currently shown as a placeholder icon; if you want
  real photos, add an `image` upload field to `pages/products.php` /
  `api/products.php` and store the uploaded file under
  `assets/images/`, then swap the placeholder `<i>` icon in
  `assets/js/pos.js` for an `<img>` tag using `product.image`.
- Tax is set to 0% by default. To enable it, change `TAX_RATE` at the
  top of `assets/js/pos.js` (e.g. `0.05` for 5%).
- All money amounts are stored as `DECIMAL(10,2)` and displayed with a
  `₹` (Rupee) symbol; change the symbol in `assets/js/app.js`
  (`formatMoney`) and the PHP `formatMoney()` helpers if you use a
  different currency.
