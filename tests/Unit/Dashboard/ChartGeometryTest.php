<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dashboard;

use App\Dashboard\ChartGeometry;
use App\Dashboard\FakeSpendingData;
use App\Tests\Common\UnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class ChartGeometryTest extends UnitTestCase
{
    public function testLineChartRoundsAxisUpToStep(): void
    {
        $chart = new ChartGeometry()->lineChart(['A' => 100, 'B' => 1100, 'C' => 600]);

        self::assertSame(1500, $chart['max']);
        self::assertSame([0, 500, 1000, 1500], array_column($chart['axis'], 'label'));
        self::assertSame(['A', 'B', 'C'], array_column($chart['labels'], 'text'));
        self::assertSame([100, 1100, 600], array_column($chart['points'], 'value'));
        self::assertStringStartsWith('M', $chart['line']);
        self::assertStringEndsWith('Z', $chart['area']);
    }

    public function testLineChartNeedsTwoPoints(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ChartGeometry()->lineChart(['A' => 1]);
    }

    public function testDonutSegmentsCoverTheWholeCircle(): void
    {
        $geometry = new ChartGeometry();
        $segments = $geometry->donut(new FakeSpendingData()->lastMonthBreakdown());

        $circumference = 2 * \M_PI * ChartGeometry::DONUT_RADIUS;
        foreach ($segments as $segment) {
            self::assertEqualsWithDelta($circumference, $segment['dash'] + $segment['gap'], 0.001);
        }
        self::assertSame(0.0, $segments[0]['offset']);
    }

    public function testDonutOfNothingIsEmpty(): void
    {
        self::assertSame([], new ChartGeometry()->donut([]));
    }
}
