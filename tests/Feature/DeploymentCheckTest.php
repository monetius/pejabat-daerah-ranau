<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeploymentCheckTest extends TestCase
{
    private function productionConfig(): void
    {
        $this->app['env'] = 'production';
        config([
            'app.debug' => false,
            'app.key' => 'base64:'.base64_encode(str_repeat('x', 32)),
            'app.url' => 'https://pdranau.sabah.gov.my',
            'app.timezone' => 'Asia/Kuala_Lumpur',
            'session.secure' => true,
            'session.http_only' => true,
            'session.driver' => 'database',
            'cache.default' => 'database',
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.org',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.password' => 'secret-that-must-not-be-printed',
            'mail.from.address' => 'website@example.org',
            'calendar.api.ca_bundle' => null,
        ]);
    }

    public function test_production_configuration_passes_without_sending_mail_or_connecting_to_the_database(): void
    {
        $this->productionConfig();
        $this->artisan('deploy:check')->expectsOutputToContain('PASS Production environment')
            ->expectsOutputToContain('PASS HTTPS public domain configured')
            ->expectsOutputToContain('Manual checks:')->doesntExpectOutputToContain('secret-that-must-not-be-printed')
            ->assertSuccessful();
    }

    public function test_insecure_production_settings_fail_the_check(): void
    {
        $this->productionConfig();
        config(['app.debug' => true, 'session.secure' => false, 'app.url' => 'http://localhost:8000']);
        $this->artisan('deploy:check')->expectsOutputToContain('FAIL Debug disabled')
            ->expectsOutputToContain('FAIL HTTPS public domain configured')
            ->expectsOutputToContain('FAIL Secure session cookies')->assertFailed();
    }

    public function test_placeholder_domain_and_nonpersistent_verification_stores_are_rejected(): void
    {
        $this->productionConfig();
        config(['app.url' => 'https://replace-with-your-domain.example', 'cache.default' => 'array', 'session.driver' => 'array', 'mail.default' => 'log']);
        $this->artisan('deploy:check')->expectsOutputToContain('FAIL HTTPS public domain configured')
            ->expectsOutputToContain('FAIL Persistent cache driver for verification codes')
            ->expectsOutputToContain('FAIL Persistent session driver')
            ->expectsOutputToContain('FAIL Real mail transport configured')->assertFailed();
    }
}
