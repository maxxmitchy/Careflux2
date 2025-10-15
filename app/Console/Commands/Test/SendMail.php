<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMail extends Command
{
    protected $signature = 'test:send-mail {recipient}';

    protected $description = 'Sends a test email to verify SMTP configuration.';

    public function handle(): int
    {
        $recipient = $this->argument('recipient');
        $this->info("Attempting to send a test email to: {$recipient}");

        try {
            Mail::raw('This is a test email to confirm that your Careflux SMTP settings are working correctly.', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Careflux SMTP Configuration Test');
            });

            $this->info('✅ Test email dispatched successfully! Please check your inbox (and spam folder).');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Email sending failed. Error:');
            $this->error($e->getMessage());
            $this->comment('Please double-check your MAIL_ settings in the .env file and ensure your config cache is cleared.');

            return self::FAILURE;
        }
    }
}
