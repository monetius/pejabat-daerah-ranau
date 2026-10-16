<?php

namespace App\Support;

use Illuminate\Mail\Mailable;

class CmsLoginCode extends Mailable
{
    public function __construct(public string $code) {}

    public function build(): static
    {
        return $this->subject(__('Kod log masuk CMS Ranau'))->view('admin.login-code-email');
    }
}
