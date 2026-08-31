# Pageturner Books — PHP + Tailwind Bookshop

## Setup

1. **Requirements**: PHP 8+, MySQL/MariaDB, Apache/Nginx (or `php -S localhost:8000`).
2. **Database**: Import `database/schema.sql` into MySQL:
   ```
   mysql -u root -p < database/schema.sql
   ```
3. **Config**: Edit `config/db.php` with your DB username/password.
4. **Create admin account**: Visit `/admin/setup.php` in your browser, fill in
   a username and password, then **delete `admin/setup.php`** — leaving it
   live would let anyone create an admin login.
5. **Uploads folder**: Make sure `uploads/` is writable by the web server
   (`chmod 755 uploads`).
6. **Run it**: Point your web server's document root at this folder, or for
   quick local testing:
   ```
   php -S localhost:8000
   ```
   then visit `http://localhost:8000`.

## Folder structure

```
admin/          Admin dashboard (protected, login required)
  includes/     Shared admin layout (sidebar/header/footer)
assets/         (place custom CSS/JS here if you outgrow the Tailwind CDN)
config/         db.php — the single DB connection every page reuses
includes/       Shared public site header/footer + functions.php helpers
uploads/        Book cover images land here (locked against PHP execution)
database/       schema.sql — run this once to create tables
```

## How the flow works

- **Public site**: `index.php` lists books → `book.php` shows one book and
  an "Add to Cart" form → `cart.php` holds items in the PHP session →
  `checkout.php` collects customer info → `save-order.php` writes the order
  to the database inside a transaction and decrements stock.
- **Admin dashboard**: `admin/login.php` authenticates against the `admins`
  table (bcrypt-hashed passwords via `password_hash`/`password_verify`).
  Once logged in, `admin/add_book.php` is where new books + descriptions +
  cover images are added — it inserts directly into the `books` table and
  saves the uploaded image into `/uploads`. `admin/books_list.php` lets you
  edit or delete existing books, and `admin/orders.php` shows what's sold.

## Security notes already built in

- All DB queries use PDO **prepared statements** — no SQL injection.
- All output is passed through `h()` (htmlspecialchars) — no XSS.
- Admin passwords are hashed with bcrypt, never stored in plain text.
- `uploads/.htaccess` blocks PHP files from ever being executed there.
- Order totals are recalculated server-side at checkout — a shopper can't
  tamper with prices via browser dev tools.
