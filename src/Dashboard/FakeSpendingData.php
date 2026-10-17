<?php

declare(strict_types=1);

namespace App\Dashboard;

/**
 * Placeholder numbers for the homepage until real expenses are stored.
 */
final class FakeSpendingData
{
    /**
     * @return array<string, int> month label => total spent (EUR)
     */
    public function monthlyTotals(): array
    {
        return [
            'Jan' => 820,
            'Feb' => 1130,
            'Mar' => 940,
            'Apr' => 1260,
            'May' => 1010,
            'Jun' => 1390,
            'Jul' => 1080,
            'Aug' => 1210,
            'Sep' => 970,
            'Oct' => 1340,
            'Nov' => 1120,
            'Dec' => 1450,
        ];
    }

    /**
     * @return list<array{label: string, amount: int, color: string}>
     */
    public function lastMonthBreakdown(): array
    {
        return [
            ['label' => 'Housing', 'amount' => 520, 'color' => '#3b2fb5'],
            ['label' => 'Food & Grocery', 'amount' => 310, 'color' => '#ff9f55'],
            ['label' => 'Transportation', 'amount' => 180, 'color' => '#e8703a'],
            ['label' => 'Shopping', 'amount' => 145, 'color' => '#d9424e'],
            ['label' => 'Other', 'amount' => 95, 'color' => '#9aa5c4'],
        ];
    }
}
