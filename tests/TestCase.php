<?php

namespace Tests;

use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Simulate a completed password-and-email login for content management tests. */
    protected function actingAsVerified(User $user): static
    {
        $this->actingAs($user);
        $this->withSession(['two_factor_verified' => app(TwoFactor::class)->fingerprint($user)]);

        return $this;
    }
}
