<?php

declare(strict_types=1);

namespace App\Tests\Unit\Country;

use App\Country\Countries;
use App\Tests\Common\UnitTestCase;

/**
 * @internal
 */
final class CountriesTest extends UnitTestCase
{
    public function testChoicesMapNamesToCodesSortedByName(): void
    {
        $choices = Countries::choices();

        self::assertSame('HR', $choices['Croatia']);
        $names = array_keys($choices);
        $sorted = $names;
        sort($sorted);
        self::assertSame($sorted, $names);
    }

    public function testValidityAndName(): void
    {
        self::assertTrue(Countries::isValid('HR'));
        self::assertFalse(Countries::isValid('XX'));
        self::assertSame('Croatia', Countries::name('HR'));
        self::assertNull(Countries::name('XX'));
    }
}
