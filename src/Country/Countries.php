<?php

declare(strict_types=1);

namespace App\Country;

/**
 * Selectable countries (ISO 3166-1 alpha-2). symfony/intl is not installed, so the list is kept here.
 */
final class Countries
{
    /**
     * @return array<string, string> country name => ISO code
     */
    public static function choices(): array
    {
        $choices = array_flip(self::NAMES);
        ksort($choices);

        return $choices;
    }

    public static function isValid(string $code): bool
    {
        return isset(self::NAMES[$code]);
    }

    public static function name(string $code): ?string
    {
        return self::NAMES[$code] ?? null;
    }

    private const NAMES = [
        'AL' => 'Albania', 'AT' => 'Austria', 'BA' => 'Bosnia and Herzegovina', 'BE' => 'Belgium',
        'BG' => 'Bulgaria', 'CA' => 'Canada', 'CH' => 'Switzerland', 'CY' => 'Cyprus',
        'CZ' => 'Czechia', 'DE' => 'Germany', 'DK' => 'Denmark', 'EE' => 'Estonia',
        'ES' => 'Spain', 'FI' => 'Finland', 'FR' => 'France', 'GB' => 'United Kingdom',
        'GR' => 'Greece', 'HR' => 'Croatia', 'HU' => 'Hungary', 'IE' => 'Ireland',
        'IT' => 'Italy', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'LV' => 'Latvia',
        'ME' => 'Montenegro', 'MK' => 'North Macedonia', 'MT' => 'Malta', 'NL' => 'Netherlands',
        'NO' => 'Norway', 'PL' => 'Poland', 'PT' => 'Portugal', 'RO' => 'Romania',
        'RS' => 'Serbia', 'SE' => 'Sweden', 'SI' => 'Slovenia', 'SK' => 'Slovakia',
        'US' => 'United States',
    ];
}
