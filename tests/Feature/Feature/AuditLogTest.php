<?php

namespace Tests\Feature\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/audit');

        $response->assertRedirect('/login');
    }
}
