<?php

namespace App\Reporting;

/**
 * Builds QuickChart.io image URLs so the report template can embed charts as
 * plain <img> tags - no native canvas/rendering dependency on the server.
 */
final class QuickChartUrl
{
    public static function radar(array $labels, array $data): string
    {
        return self::build([
            'type' => 'radar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Readiness',
                    'data' => $data,
                ]],
            ],
            'options' => [
                'scale' => ['ticks' => ['min' => 0, 'max' => 100]],
            ],
        ]);
    }

    public static function bar(array $labels, array $data, string $label): string
    {
        return self::build([
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                ]],
            ],
        ]);
    }

    private static function build(array $config, int $width = 500, int $height = 300): string
    {
        $encoded = urlencode(json_encode($config, JSON_THROW_ON_ERROR));

        return "https://quickchart.io/chart?c={$encoded}&w={$width}&h={$height}&backgroundColor=white";
    }
}
