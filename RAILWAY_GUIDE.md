# 🚀 Deploying PetPal to Railway

This guide will help you deploy your PetPal application to Railway.app.

## Prerequisites

- A GitHub account
- A [Railway.app](https://railway.app?referralCode=petpal) account

## Step 1: Push Code to GitHub

If you haven't already, make sure your code is pushed to a GitHub repository.

## Step 2: Create a New Project on Railway

1.  Log in to your Railway dashboard.
2.  Click **"New Project"**.
3.  Select **"Deploy from GitHub repo"**.
4.  Choose your PetPal repository.
5.  Click **"Deploy Now"**.

## Step 3: Add MySQL Database

1.  In your project view (Canvas), click **"New"** (or right-click the background).
2.  Select **"Database"** -> **"MySQL"**.
3.  Wait for the MySQL service to initialize.

## Step 4: Configure Environment Variables

Railway automatically provides `DATABASE_URL` (or `MYSQL_URL`) to your service if you link them, but we need to ensure the PHP service is picking it up.

1.  Click on your **PetPal (PHP)** service card.
2.  Go to the **"Variables"** tab.
3.  You should see `DATABASE_URL` or `MYSQL_URL` automatically injected if you linked the database. If not, you might need to manually add the variable referencing the database.
4.  **CRITICAL STEP**: Add a temporary variable to enable the setup script:
    -   Key: `ENABLE_SETUP`
    -   Value: `true`

## Step 5: Initialize the Database

1.  Wait for the deployment to finish (Green checkmark).
2.  Click the generated URL for your web app (e.g., `https://petpal-production.up.railway.app`).
3.  Append `/setup.php` to the URL:
    -   `https://your-app-url.up.railway.app/setup.php`
4.  You should see "Setup Complete!" and "Login with..." details.
    -   **Default User**: `rYuk`
    -   **Password**: `Pass123`

## Step 6: Secure Your Deployment

1.  Go back to your Railway Service **Variables**.
2.  **Delete** or set `ENABLE_SETUP` to `false`.
3.  This prevents anyone from resetting your database publically.
4.  Railway will automatically redeploy.

## Troubleshooting

-   **500 Error?** Check the "Logs" tab in Railway.
-   **Database Error?** Ensure the `DATABASE_URL` variable is correct and matches what's in the MySQL service 'Connect' tab.
