<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_failures_temporarily_block_login_then_expire(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'password' => 'correct-password']);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong-password'])->assertSessionHasErrors('email');
        }
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'correct-password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->travel(61)->seconds();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'correct-password'])->assertSessionHasNoErrors()->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_successful_login_clears_the_account_failure_counter(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'password' => 'correct-password']);
        for ($attempt = 0; $attempt < 4; $attempt++) {
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong-password']);
        }
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'correct-password'])->assertSessionHasNoErrors();
        $this->post('/admin/logout');
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong-password']);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'correct-password'])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_rotating_email_addresses_does_not_bypass_the_ip_limit(): void
    {
        $this->freezeTime();
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->post('/admin/login', ['email' => 'unknown'.$attempt.'@example.com', 'password' => 'wrong-password']);
        }
        $this->post('/admin/login', ['email' => 'another@example.com', 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => 'Terlalu banyak percubaan log masuk. Cuba lagi dalam 60 saat.']);
    }
}
