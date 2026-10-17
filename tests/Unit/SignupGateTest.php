<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Security\SignupGate;
use App\Security\SignupState;
use App\Tests\Common\UnitTestCase;

/**
 * @internal
 */
final class SignupGateTest extends UnitTestCase
{
    public function testEmptySecretMeansOpen(): void
    {
        $gate = new SignupGate('');

        self::assertSame(SignupState::Open, $gate->state());
        self::assertTrue($gate->allows(null));
        self::assertTrue($gate->allows('anything'));
    }

    public function testRandomSecretRequiresMatchingInvite(): void
    {
        $gate = new SignupGate('s3cret');

        self::assertSame(SignupState::InviteOnly, $gate->state());
        self::assertFalse($gate->allows(null));
        self::assertFalse($gate->allows('wrong'));
        self::assertTrue($gate->allows('s3cret'));
    }

    public function testDisabledRejectsEverything(): void
    {
        $gate = new SignupGate('disabled');

        self::assertSame(SignupState::Disabled, $gate->state());
        self::assertFalse($gate->allows(null));
        self::assertFalse($gate->allows('disabled'));
    }
}
