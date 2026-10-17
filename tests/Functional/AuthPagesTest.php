<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Common\FunctionalTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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

    #[DataProvider('provideAppPagesRedirectAnonymousUsersToLoginCases')]
    public function testAppPagesRedirectAnonymousUsersToLogin(string $path): void
    {
        $this->request('GET', $path);

        $this->assertRedirectsTo('/login');
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function provideAppPagesRedirectAnonymousUsersToLoginCases(): iterable
    {
        yield 'expenses' => ['/expenses'];
        yield 'sync' => ['/sync'];
        yield 'sync bank' => ['/sync/bank'];
        yield 'settings' => ['/settings'];
        yield 'settings classes' => ['/settings/classes'];
        yield 'settings users' => ['/settings/users'];
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
