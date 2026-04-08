<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestMail extends Command
{
    protected $signature = 'mail:test {email : Recipient inbox for the test message}';

    protected $description = 'Send a test email using the current Laravel mail configuration';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please provide a valid email address.');
            return self::FAILURE;
        }

        $this->line('Mailer: ' . config('mail.default'));
        $this->line('From: ' . config('mail.from.address') . ' (' . config('mail.from.name') . ')');

        try {
            Mail::to($email)->send(
                new TestMail(
                    config('app.name', 'DepEd DOCTRAX'),
                    (string) config('app.url', 'http://localhost')
                )
            );
        } catch (\Throwable $e) {
            $this->error('Mail send failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Test email sent successfully to ' . $email . '.');

        return self::SUCCESS;
    }
}
