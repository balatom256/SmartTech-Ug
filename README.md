# SmartTech-UG – XAMPP Setup Guide
## Complete PHP + MySQL E-commerce Website

---

## 📁 Project Structure

```
smarttech-ug/
├── index.php            ← Homepage (dynamic, from DB)
├── products.php         ← Products listing + search + filter
├── cart.php             ← Shopping cart (add/update/remove)
├── checkout.php         ← Checkout with payment options
├── order_success.php    ← Order confirmation
├── orders.php           ← My orders (customer)
├── wishlist.php         ← Wishlist
├── login.php            ← Sign in
├── register.php         ← Create account
├── logout.php           ← Logout
│
├── includes/
│   ├── config.php       ← DB connection + helper functions
│   ├── header.php       ← Shared nav + HTML head
│   └── footer.php       ← Shared footer
│
├── admin/
│   ├── index.php        ← Admin dashboard + order management
│   └── products.php     ← Add/Edit/Delete products
│
├── assets/              ← (images go here)
│
└── smarttech_ug.sql     ← Database (import this first!)
```

---

## 🚀 Setup Steps (XAMPP)

### Step 1 – Start XAMPP
1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache** and **MySQL**
3. Both should show green

### Step 2 – Copy the project
1. Copy the entire `smarttech-ug` folder
2. Paste it into: `C:\xampp\htdocs\`
3. Final path should be: `C:\xampp\htdocs\smarttech-ug\`

### Step 3 – Import the database
1. Open your browser → go to: `http://localhost/phpmyadmin`
2. Click **New** (left sidebar) to create a new database
3. Name it: `smarttech_ug` → click **Create**
4. Click the **Import** tab (top menu)
5. Click **Choose File** → select `smarttech_ug.sql`
6. Scroll down → click **Import**
7. You should see: "Import has been successfully finished"

### Step 4 – Open the website
Open your browser and go to:
```
http://localhost/smarttech-ug/
```

---

## 🔑 Login Credentials

### Admin Account
- **Email:** admin@smarttech-ug.com
- **Password:** password
- **Admin panel:** http://localhost/smarttech-ug/admin/

### Demo Customer
- **Email:** aisha@example.com
- **Password:** password

---

## ⚙️ Configuration

If you need to change anything, edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');   // XAMPP default
define('DB_USER', 'root');        // XAMPP default
define('DB_PASS', '');            // XAMPP default (blank password)
define('DB_NAME', 'smarttech_ug');
define('SITE_URL', 'http://localhost/smarttech-ug');
```

---

## 🌟 Features Built

### Customer Side
- ✅ Homepage with dynamic products from database
- ✅ Product listing with search + category filter + sort
- ✅ Shopping cart (add, update quantity, remove, clear)
- ✅ Wishlist (save products for later)
- ✅ Checkout with 4 payment options (MTN MoMo, Airtel, VISA, Cash)
- ✅ Order placement (saves to DB, reduces stock)
- ✅ Order history
- ✅ User registration and login
- ✅ Session management

### Admin Side
- ✅ Dashboard with stats (products, orders, customers, revenue)
- ✅ Recent orders with status update
- ✅ Product management: Add, Edit, Delete
- ✅ Toggle featured products
- ✅ Stock management

### Database Tables
- `users` – customers and admin accounts
- `categories` – product categories
- `products` – product catalog with pricing
- `cart` – per-user cart items
- `wishlist` – saved products
- `orders` – placed orders
- `order_items` – items in each order

---

## 🔧 Common Fixes

**"Connection Failed"** → Make sure MySQL is running in XAMPP and you imported the SQL file.

**Page shows as text** → Make sure you're visiting `http://localhost/smarttech-ug/` and Apache is running (not opening the .php file directly from your file manager).

**Photos not showing** → This version uses emoji icons. To add real images, upload them to `assets/uploads/` and update the `image` column in the products table.

---

## 📚 What to Learn Next (CS Year 2)

1. **PDO** instead of mysqli (more secure, supports multiple databases)
2. **Prepared Statements** to prevent SQL injection properly
3. **File uploads** – let admins upload real product images
4. **MTN MoMo API** integration for real payment processing
5. **Deploy to cPanel** (free on Hostinger or 000webhost)
6. **Laravel or CodeIgniter** PHP framework for bigger projects

---

Built with ❤️ in Uganda | SmartTech-UG © 2025
