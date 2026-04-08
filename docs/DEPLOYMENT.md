# GitHub Actions Deploy Guide

This project now includes a production deploy workflow at `.github/workflows/deploy.yml`.

It is designed for this setup:

- GitHub Actions connects to your Ubuntu VM over SSH
- the Ubuntu VM already has the Laravel app checked out
- the live database credentials stay only in the server `.env`
- production migrations run on the server with `php artisan migrate --force`

## 1. GitHub Repository Secrets

Add these repository secrets in GitHub:

- `SSH_HOST`
  Your Ubuntu VM public IP or domain
- `SSH_PORT`
  Usually `22`
- `SSH_PRIVATE_KEY`
  The private key GitHub Actions will use to SSH into the VM

This repo now hardcodes the current production deploy target in the workflow:

- deploy user: `depeddoctrax1`
- app path: `/var/www/doctrax`

## 2. Ubuntu VM Checklist

Install the runtime tools your server needs:

```bash
sudo apt update
sudo apt install -y git unzip curl composer \
  php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip
```

If your Ubuntu VM uses a different PHP package version, replace `php8.3` with the version available on that server.

Make sure the app exists on the server:

```bash
cd /var/www
git clone <your-repo-url> DepedDocumentTrackingSystem
cd DepedDocumentTrackingSystem
composer install --no-dev --prefer-dist --optimize-autoloader
```

Create and configure your production `.env`:

```bash
cp .env .env.backup 2>/dev/null || true
nano .env
```

At minimum, confirm these are correct:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=...`
- `DB_CONNECTION=mysql`
- `DB_HOST=...`
- `DB_PORT=3306`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`

Generate the app key if needed:

```bash
php artisan key:generate
```

Fix writable permissions:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 3. SSH Access Needed

There are two separate access paths to set up.

GitHub Actions to your Ubuntu VM:

- create an SSH key pair for deployment
- put the public key in `~/.ssh/authorized_keys` for your deploy user
- put the private key into the GitHub secret `SSH_PRIVATE_KEY`

Ubuntu VM to your GitHub repo:

- if the repo is private, the server itself must be able to run `git pull`
- the cleanest option is a deploy key added to the repo and the matching private key stored on the VM
- make sure the server remote uses SSH, for example `git@github.com:OWNER/REPO.git`

Check the remote on the server:

```bash
git remote -v
```

If needed, switch it to SSH:

```bash
git remote set-url origin git@github.com:OWNER/REPO.git
```

## 4. First Manual Test on the Server

Before relying on GitHub Actions, run the deploy steps once directly on Ubuntu:

```bash
cd /var/www/DepedDocumentTrackingSystem
git pull --ff-only origin main
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

This confirms:

- the server can pull from GitHub
- Composer works in production
- Laravel can connect to the live database
- migrations run correctly on the VM

## 5. Live Migration for This Change

Your current pending production migration is:

- `database/migrations/2026_03_31_000000_add_representative_office_name_to_users_table.php`

Once the workflow runs successfully, it will be applied automatically by:

```bash
php artisan migrate --force
```

## 6. How Deployment Works

When CI succeeds on `main`, or when you manually trigger the workflow:

1. GitHub Actions SSHes into the Ubuntu VM
2. the VM runs `git pull --ff-only origin main`
3. Composer installs production dependencies
4. Laravel clears caches
5. Laravel runs `php artisan migrate --force`
6. Laravel rebuilds config, route, and view caches

## 7. Recommended Safety Steps

- take a database backup before your first production migration
- use a dedicated non-root deploy user on Ubuntu
- keep the server `.env` out of Git
- avoid storing database credentials in GitHub secrets when the VM already has them in `.env`
- keep the working tree clean on the server so `git pull --ff-only` does not fail

## 8. Running the Workflow

You can deploy in two ways:

- push to `main`
- open GitHub Actions and run `Deploy Production` manually with `workflow_dispatch`

## 9. Troubleshooting

If the workflow cannot SSH into the server:

- verify `SSH_HOST`, `SSH_PORT`, `SSH_USER`, and `SSH_PRIVATE_KEY`
- confirm the public key is in the server user `authorized_keys`
- confirm SSH is allowed through the VM firewall

If the workflow SSHes in but `git pull` fails:

- verify the repo remote uses SSH
- verify the server has a GitHub deploy key or another valid GitHub credential

If `php artisan migrate --force` fails:

- check the production `.env`
- confirm the MySQL server is reachable from the VM
- confirm the DB user has schema change permissions
