# Mail Setup

This project already sends two important emails:

- account activation / set-password emails
- forgot-password reset emails

To make them land in a real inbox, configure SMTP on the server `.env`.

## Required `.env` values

Add or update these on the Ubuntu VM:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@your-domain.com
MAIL_PASSWORD=your-email-password-or-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@your-domain.com
MAIL_FROM_NAME="DepEd DOCTRAX"
```

Use `MAIL_ENCRYPTION=tls` for STARTTLS on port `587`. Only use `MAIL_SCHEME=smtps` when your provider requires implicit SSL on port `465`.

## Common SMTP examples

### cPanel / hosting email

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your-mailbox-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="DepEd DOCTRAX"
```

### Gmail

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=yourgmail@gmail.com
MAIL_PASSWORD=your-google-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=yourgmail@gmail.com
MAIL_FROM_NAME="DepEd DOCTRAX"
```

For Gmail, use an App Password, not your normal Gmail password.

## Apply the config on the VM

After editing `.env`, run:

```bash
cd /var/www/doctrax
php artisan optimize:clear
php artisan config:cache
```

## Send a test email

This repo now includes a test command:

```bash
php artisan mail:test your-inbox@example.com
```

If that succeeds, your forgot-password and account activation emails should also work.

## If mail still does not arrive

Check:

- the SMTP host, port, username, and password
- whether your provider requires SSL/TLS or STARTTLS
- whether the mailbox itself can send from the configured address
- your spam/junk folder
- Laravel logs in `storage/logs/laravel.log`
