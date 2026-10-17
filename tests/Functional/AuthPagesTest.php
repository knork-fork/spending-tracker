<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Common\FunctionalTestCase;

/**
 * @internal
 */
final class AuthPagesTest extends FunctionalTestCase
{
    public function testHomeRedirectsAnonymousUsersToLogin(): void
    {
        $this->request('GET', '/');

        $this->assertRedirectsTo('/login');
    }

    public function testLoginPageShowsSignupLink(): void
    {
        $this->request('GET', '/login');

        $this->assertStatusCode(200);
        $this->assertBodyContains('href="/signup"');
    }

    public function testStatusEndpoint(): void
    {
        $this->request('GET', '/status');

        $this->assertJsonResponse(['status' => 'ok']);
    }
}
