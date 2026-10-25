<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('deploy:check')]
#[Description('Check production configuration without changing data or sending email')]
class CheckDeployment extends Command
{
    public function handle(): int
    {
        $url = parse_url((string) config('app.url'));
        $mailer = (string) config('mail.default');
        $checks = [
            'PHP 8.5 or newer' => PHP_VERSION_ID >= 80500,
            'Production environment' => app()->environment('production'),
            'Debug disabled' => config('app.debug') === false,
            'Application key configured' => filled(config('app.key')),
            'HTTPS public domain configured' => ($url['scheme'] ?? '') === 'https'
                && filled($url['host'] ?? null)
                && ! in_array($url['host'], ['localhost', '127.0.0.1', '::1'], true)
                && ! str_ends_with($url['host'], '.example')
                && ! str_ends_with($url['host'], '.test'),
            'Malaysia timezone' => config('app.timezone') === 'Asia/Kuala_Lumpur',
            'Secure session cookies' => config('session.secure') === true,
            'HTTP-only session cookies' => config('session.http_only') === true,
            'Persistent cache driver for verification codes' => ! in_array(config('cache.default'), ['array', 'null'], true),
            'Persistent session driver' => ! in_array(config('session.driver'), ['array', 'cookie'], true),
            'Mail sender configured' => filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL) !== false
                && ! str_ends_with((string) config('mail.from.address'), '@example.com'),
            'Real mail transport configured' => in_array(config('mail.mailers.'.$mailer.'.transport'), ['smtp', 'sendmail', 'ses', 'ses-v2', 'mailgun', 'postmark', 'resend'], true),
            'Storage writable' => is_writable(storage_path()),
            'Bootstrap cache writable' => is_writable(base_path('bootstrap/cache')),
            'Uploads directory writable' => is_dir(public_path('uploads')) && is_writable(public_path('uploads')),
        ];
        if ($mailer === 'smtp') {
            $checks['SMTP host configured'] = filled(config('mail.mailers.smtp.host'))
                && ! in_array(config('mail.mailers.smtp.host'), ['127.0.0.1', 'localhost'], true);
        }
        $bundle = config('calendar.api.ca_bundle');
        $checks['Holiday API certificate configuration portable'] = blank($bundle) || is_readable($bundle);
        $failed = 0;
        foreach ($checks as $label => $passed) {
            $this->line(($passed ? 'PASS' : 'FAIL').' '.$label);
            if (! $passed) {
                $failed++;
            }
        }
        $this->warn('Manual checks: public/ web root, HTTPS/proxy handling, database connection and migrations, persistent uploads/storage, backups and restore, scheduler, and actual email delivery.');

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
