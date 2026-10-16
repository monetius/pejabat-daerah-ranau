<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\CmsLoginCode;
use App\Support\TwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminEmailTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $this->freezeTime();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'password' => 'original-password']);
    }

    private function begin(User $user, bool $remember = false): string
    {
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'original-password', 'remember' => $remember])
            ->assertSessionHasNoErrors()->assertRedirect('/admin/two-factor-challenge')->assertCookieMissing(Auth::getRecallerName());
        $this->assertGuest();

        return Mail::sent(CmsLoginCode::class)->last()->code;
    }

    public function test_password_only_never_authenticates_and_code_is_sent_only_to_the_account_email(): void
    {
        $user = $this->admin();
        $code = $this->begin($user);
        Mail::assertSent(CmsLoginCode::class, fn (CmsLoginCode $mail): bool => $mail->hasTo($user->email));
        $this->assertMatchesRegularExpression('/\A[0-9]{6}\z/', $code);
        $pending = session('two_factor_pending');
        $challenge = Cache::get('cms-email-code:'.$user->id);
        $this->assertSame(hash_hmac('sha256', $pending['challenge'].'|'.$code, config('app.key')), $challenge['hash']);
        $this->assertArrayNotHasKey('code', $pending);
        $this->assertArrayNotHasKey('code', $challenge);
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/two-factor-challenge')->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('***@')->assertDontSee($user->email)->assertDontSee('Google Authenticator')
            ->assertSee('autocomplete="one-time-code"', false);
    }

    public function test_valid_code_authenticates_restores_intended_page_and_is_consumed(): void
    {
        $user = $this->admin();
        $this->get('/admin/account')->assertRedirect('/login');
        $code = $this->begin($user);
        $oldSession = session()->getId();
        $this->post('/admin/two-factor-challenge', ['code' => $code])->assertSessionHasNoErrors()->assertRedirect('/admin/account');
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldSession, session()->getId());
        $this->assertNull(Cache::get('cms-email-code:'.$user->id));
        $this->assertNull(session('two_factor_pending'));
        $this->get('/admin/account')->assertOk();
    }

    public function test_code_cannot_be_replayed_from_a_second_session(): void
    {
        $user = $this->admin();
        $code = $this->begin($user);
        $pending = session('two_factor_pending');
        $this->post('/admin/two-factor-challenge', ['code' => $code])->assertRedirect('/admin');
        $this->post('/admin/logout');
        $this->withSession(['two_factor_pending' => $pending])->post('/admin/two-factor-challenge', ['code' => $code])->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_wrong_codes_are_not_flashed_and_are_limited_across_new_login_sessions(): void
    {
        $user = $this->admin();
        $code = $this->begin($user);
        $wrong = $code === '000000' ? '000001' : '000000';
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/admin/two-factor-challenge')->post('/admin/two-factor-challenge', ['code' => $wrong])
                ->assertSessionHasErrors('code')->assertSessionMissing('_old_input.code');
        }
        $this->post('/admin/two-factor-challenge', ['code' => $code])->assertSessionHasErrors('code');
        $this->travel(61)->seconds();
        $newCode = $this->begin($user);
        $this->post('/admin/two-factor-challenge', ['code' => $newCode])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->travel(601)->seconds();
        $freshCode = $this->begin($user);
        $this->post('/admin/two-factor-challenge', ['code' => $freshCode])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_expired_and_missing_challenges_require_password_again(): void
    {
        $this->get('/admin/two-factor-challenge')->assertRedirect('/admin/login');
        $user = $this->admin();
        $code = $this->begin($user);
        $this->travel(600)->seconds();
        $this->post('/admin/two-factor-challenge', ['code' => $code])->assertRedirect('/admin/login');
        $this->post('/admin/two-factor-resend')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_resend_is_limited_rotates_code_and_does_not_extend_password_verification(): void
    {
        $user = $this->admin();
        $oldCode = $this->begin($user);
        $oldId = session('two_factor_pending.challenge');
        $started = session('two_factor_pending.started');
        $this->post('/admin/two-factor-resend')->assertSessionHasErrors('email');
        Mail::assertSentCount(1);
        $this->travel(61)->seconds();
        $this->post('/admin/two-factor-resend', ['email' => 'attacker@example.com'])->assertRedirect('/admin/two-factor-challenge');
        Mail::assertSentCount(2);
        Mail::assertSent(CmsLoginCode::class, fn (CmsLoginCode $mail): bool => $mail->hasTo($user->email));
        $this->assertNotSame($oldId, session('two_factor_pending.challenge'));
        $this->assertSame($started, session('two_factor_pending.started'));
        $this->assertSame($started + 600, Cache::get('cms-email-code:'.$user->id)['expires']);
        $newCode = Mail::sent(CmsLoginCode::class)->last()->code;
        if ($oldCode !== $newCode) {
            $this->post('/admin/two-factor-challenge', ['code' => $oldCode])->assertSessionHasErrors('code');
        }
        $this->post('/admin/two-factor-challenge', ['code' => $newCode])->assertSessionHasNoErrors()->assertRedirect('/admin');
    }

    public function test_resending_cannot_create_unlimited_emails(): void
    {
        $user = $this->admin();
        $this->begin($user);
        for ($index = 0; $index < 4; $index++) {
            $this->travel(61)->seconds();
            $this->post('/admin/two-factor-resend')->assertSessionHasNoErrors();
        }
        $this->travel(61)->seconds();
        $this->post('/admin/two-factor-resend')->assertSessionHasErrors('email');
        Mail::assertSentCount(5);
    }

    public function test_new_password_login_invalidates_an_older_pending_session(): void
    {
        $user = $this->admin();
        $oldCode = $this->begin($user);
        $oldPending = session('two_factor_pending');
        $this->travel(61)->seconds();
        $this->begin($user);
        $this->withSession(['two_factor_pending' => $oldPending])->post('/admin/two-factor-challenge', ['code' => $oldCode])->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_account_changes_and_role_removal_invalidate_password_verification(): void
    {
        foreach (['password', 'email', 'is_admin'] as $field) {
            $user = $this->admin();
            $code = $this->begin($user);
            $user->forceFill([$field => match ($field) {
                'password' => 'changed-password', 'email' => 'changed@example.com', 'is_admin' => false,
            }])->save();
            $this->post('/admin/two-factor-challenge', ['code' => $code])->assertRedirect('/admin/login');
            $this->assertGuest();
        }
    }

    public function test_cancelling_deletes_only_the_matching_challenge(): void
    {
        $user = $this->admin();
        $this->begin($user);
        $this->post('/admin/two-factor-cancel')->assertRedirect('/admin/login');
        $this->assertNull(Cache::get('cms-email-code:'.$user->id));
        $this->assertNull(session('two_factor_pending'));
        $this->assertGuest();
    }

    public function test_remember_me_is_created_only_after_email_verification_and_cannot_bypass_it(): void
    {
        $user = $this->admin();
        $originalToken = $user->remember_token;
        $code = $this->begin($user, true);
        $this->assertSame($originalToken, $user->fresh()->remember_token);
        $this->post('/admin/two-factor-challenge', ['code' => $code])->assertRedirect('/admin');
        $this->assertNotNull($user->fresh()->remember_token);
        $this->withSession(['two_factor_verified' => null]);
        $this->postJson('/admin/keepalive')->assertUnauthorized();
        $this->assertGuest();
    }

    public function test_existing_sessions_without_email_proof_cannot_read_or_write_cms_content(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->get('/admin/account')->assertRedirect('/admin/login');
        $this->assertGuest();
        $this->actingAs($user)->postJson('/admin/announcements', ['title' => 'Bypass'])->assertUnauthorized();
        $this->assertDatabaseMissing('announcements', ['title' => 'Bypass']);
    }

    public function test_invalid_passwords_and_non_admins_never_receive_email_codes(): void
    {
        $user = $this->admin();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $ordinary = User::factory()->create(['password' => 'original-password']);
        $this->post('/admin/login', ['email' => $ordinary->email, 'password' => 'original-password'])->assertSessionHasErrors('email');
        Mail::assertNothingSent();
        $this->assertGuest();
    }

    public function test_log_array_and_log_failover_mailers_fail_closed_without_exposing_a_code(): void
    {
        $user = $this->admin();
        foreach (['log', 'array', 'failover'] as $mailer) {
            config(['mail.default' => $mailer]);
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'original-password'])
                ->assertSessionHasErrors('email')->assertSessionMissing('two_factor_pending');
            $this->assertGuest();
        }
        Mail::assertNothingSent();
    }

    public function test_delivery_failure_does_not_create_a_challenge_or_authenticate(): void
    {
        $user = $this->admin();
        Mail::shouldReceive('to')->once()->with($user->email)->andThrow(new \RuntimeException('Mail unavailable'));
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'original-password'])
            ->assertSessionHasErrors('email')->assertSessionMissing('two_factor_pending');
        $this->assertNull(Cache::get('cms-email-code:'.$user->id));
        $this->assertGuest();
    }

    public function test_email_and_challenge_use_native_english_and_escaped_content(): void
    {
        $this->withCookie('cms_locale', 'en');
        $user = $this->admin();
        $code = $this->begin($user);
        $this->get('/admin/two-factor-challenge')->assertOk()->assertSee('Check your inbox')->assertSee('Resend code');
        $email = new CmsLoginCode($code);
        $this->assertStringContainsString('Your sign-in code', $email->render());
        $this->assertStringContainsString($code, $email->render());
    }

    public function test_database_cache_supports_atomic_single_use_codes(): void
    {
        config(['cache.default' => 'database']);
        $user = $this->admin();
        $code = $this->begin($user);
        $pending = session('two_factor_pending');
        $this->post('/admin/two-factor-challenge', ['code' => $code])->assertSessionHasNoErrors();
        $this->post('/admin/logout');
        $this->withSession(['two_factor_pending' => $pending])->post('/admin/two-factor-challenge', ['code' => $code])->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_password_change_requires_current_password_confirmation_and_minimum_length(): void
    {
        $user = $this->admin();
        $this->actingAsVerified($user);
        $this->put('/admin/account', ['current_password' => 'wrong', 'password' => 'new-long-password', 'password_confirmation' => 'new-long-password'])->assertSessionHasErrors('current_password');
        $this->put('/admin/account', ['current_password' => 'original-password', 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
        $this->put('/admin/account', ['current_password' => 'original-password', 'password' => 'new-long-password', 'password_confirmation' => 'different-password'])->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('original-password', $user->fresh()->password));
    }

    public function test_password_change_preserves_current_verified_session_and_invalidates_other_proofs(): void
    {
        $user = $this->admin();
        $oldProof = app(TwoFactor::class)->fingerprint($user);
        $user->forceFill(['remember_token' => 'old-remember-token'])->save();
        $this->actingAsVerified($user)->put('/admin/account', [
            'current_password' => 'original-password', 'password' => 'new-long-password', 'password_confirmation' => 'new-long-password',
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/account')->assertSessionMissing('_old_input.password');
        $this->assertTrue(Hash::check('new-long-password', $user->fresh()->password));
        $this->assertNull($user->fresh()->remember_token);
        $this->get('/admin/account')->assertOk();
        $this->withSession(['two_factor_verified' => $oldProof])->get('/admin/account')->assertRedirect('/admin/login');
        $this->assertGuest();
    }
}
