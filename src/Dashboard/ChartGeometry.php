<?php

declare(strict_types=1);

namespace App\Dashboard;

use InvalidArgumentException;

/**
 * Builds SVG geometry server-side so the homepage needs no JavaScript chart library.
 */
final class ChartGeometry
{
    public const WIDTH = 640;
    public const HEIGHT = 240;
    public const PAD_LEFT = 44;
    public const PAD_RIGHT = 12;
    public const PAD_TOP = 12;
    public const PAD_BOTTOM = 28;

    public const DONUT_RADIUS = 70;

    /**
     * Smooth line chart for the given series.
     *
     * @param array<string, int> $series label => value
     *
     * @return array{
     *     line: string,
     *     area: string,
     *     axis: list<array{y: float, label: int}>,
     *     labels: list<array{x: float, text: string}>,
     *     points: list<array{x: float, y: float, label: string, value: int}>,
     *     max: int
     * }
     */
    public function lineChart(array $series, int $step = 500): array
    {
        if (\count($series) < 2) {
            throw new InvalidArgumentException('A line chart needs at least two points.');
        }

        $max = (int) (ceil(max($series) / $step) * $step);
        $innerWidth = self::WIDTH - self::PAD_LEFT - self::PAD_RIGHT;
        $innerHeight = self::HEIGHT - self::PAD_TOP - self::PAD_BOTTOM;
        $baseline = (float) (self::PAD_TOP + $innerHeight);

        $points = [];
        $labels = [];
        $chartPoints = [];
        $count = \count($series);
        $i = 0;
        foreach ($series as $label => $value) {
            $x = self::PAD_LEFT + $innerWidth * $i / ($count - 1);
            $y = self::PAD_TOP + $innerHeight * (1 - $value / $max);
            $points[] = [$x, $y];
            $chartPoints[] = ['x' => $x, 'y' => $y, 'label' => (string) $label, 'value' => $value];
            $labels[] = ['x' => $x, 'text' => (string) $label];
            ++$i;
        }

        $line = $this->smoothPath($points);
        $last = $points[$count - 1];
        $area = \sprintf(
            '%s L%.1f,%.1f L%.1f,%.1f Z',
            $line,
            $last[0],
            $baseline,
            $points[0][0],
            $baseline,
        );

        $axis = [];
        for ($value = 0; $value <= $max; $value += $step) {
            $axis[] = ['y' => self::PAD_TOP + $innerHeight * (1 - $value / $max), 'label' => $value];
        }

        return ['line' => $line, 'area' => $area, 'axis' => $axis, 'labels' => $labels, 'points' => $chartPoints, 'max' => $max];
    }

    /**
     * Donut segments drawn as dashed circle strokes (start at 12 o'clock).
     *
     * @param list<array{label: string, amount: int, color: string}> $items
     *
     * @return list<array{label: string, amount: int, color: string, dash: float, gap: float, offset: float}>
     */
    public function donut(array $items): array
    {
        $total = array_sum(array_column($items, 'amount'));
        if ($total <= 0) {
            return [];
        }

        $circumference = 2 * \M_PI * self::DONUT_RADIUS;
        $segmentGap = 3.0;
        $segments = [];
        $consumed = 0.0;
        foreach ($items as $item) {
            $length = $circumference * $item['amount'] / $total;
            $segments[] = $item + [
                'dash' => max($length - $segmentGap, 0.0),
                'gap' => $circumference - max($length - $segmentGap, 0.0),
                'offset' => -$consumed,
            ];
            $consumed += $length;
        }

        return $segments;
    }

    /**
     * Catmull-Rom spline converted to cubic beziers.
     *
     * @param list<array{float, float}> $points
     */
    private function smoothPath(array $points): string
    {
        $path = \sprintf('M%.1f,%.1f', $points[0][0], $points[0][1]);
        $last = \count($points) - 1;
        for ($i = 0; $i < $last; ++$i) {
            $p0 = $points[max($i - 1, 0)];
            $p1 = $points[$i];
            $p2 = $points[$i + 1];
            $p3 = $points[min($i + 2, $last)];
            $path .= \sprintf(
                ' C%.1f,%.1f %.1f,%.1f %.1f,%.1f',
                $p1[0] + ($p2[0] - $p0[0]) / 6,
                $p1[1] + ($p2[1] - $p0[1]) / 6,
                $p2[0] - ($p3[0] - $p1[0]) / 6,
                $p2[1] - ($p3[1] - $p1[1]) / 6,
                $p2[0],
                $p2[1],
            );
        }

        return $path;
    }
}
