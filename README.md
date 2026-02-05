# PetPal - Pet Care & E-Commerce Web Application

Complete web application for pet lovers featuring pet care tips, e-commerce shop, hospital listings, review system, photo uploads, and health tracking.

## Features

- 🔐 User Authentication (Register, Login, Logout)
- 🏠 Home Page with hero section and featured products
- 💡 Pet Care Tips
- 🏥 Animal Hospitals with ratings
- ⭐ Review System
- 🛒 Shop with cart functionality
- 📷 Pet Photo Gallery
- 📋 Pet Health Tracker

## Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL

## Test Account

```
Username: rYuk
Password: Pass123
```

---

## Local Setup (XAMPP)

1. Copy `PetPal/` to `C:\xampp\htdocs\`
2. Start Apache + MySQL in XAMPP
3. Create database `petpal` in phpMyAdmin
4. Import `database/database.sql`
5. Visit `http://localhost/PetPal`

---

## Railway Deployment

### Step 1: Push to GitHub
```bash
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR_USERNAME/petpal.git
git push -u origin main
```

### Step 2: Deploy on Railway

1. Go to [railway.app](https://railway.app) and login with GitHub
2. Click **"New Project"** → **"Deploy from GitHub repo"**
3. Select your PetPal repository
4. Add **MySQL** service:
   - Click **"New"** → **"Database"** → **"MySQL"**
5. Connect MySQL to your app:
   - Click on your app service
   - Go to **Variables** tab
   - Add reference variable: `DATABASE_URL` = `${{MySQL.DATABASE_URL}}`
6. Deploy!

### Step 3: Initialize Database

After deployment, run the SQL from `database/database.sql` in Railway's MySQL:
- Click MySQL service → **Data** tab → Run queries

---

## Project Structure

```
PetPal/
├── api/                 # API endpoints
├── assets/css/          # Stylesheets
├── assets/js/           # JavaScript
├── config/              # Configuration
├── database/            # SQL schema
├── includes/            # Components
├── pages/               # All pages
├── uploads/             # User uploads
└── index.php            # Home page
```

---

Made with ❤️ for pet lovers
