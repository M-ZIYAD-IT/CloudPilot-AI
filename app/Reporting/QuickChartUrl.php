<?php

namespace App\Reporting;

/**
 * Builds QuickChart.io image URLs so the report template can embed charts as
 * plain <img> tags - no native canvas/rendering dependency on the server.
 */
final class QuickChartUrl
{
    private const INK = '#ffffff';

    private const INK_MUTED = '#999999';

    private const ACCENT = '#0099ff';

    private const HAIRLINE = '#262626';

    public static function radar(array $labels, array $data): string
    {
        return self::build([
            'type' => 'radar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Readiness',
                    'data' => $data,
                    'borderColor' => self::ACCENT,
                    'backgroundColor' => 'rgba(0, 153, 255, 0.15)',
                    'pointBackgroundColor' => self::ACCENT,
                ]],
            ],
            'options' => [
                'scale' => [
                    'ticks' => ['min' => 0, 'max' => 100, 'color' => self::INK_MUTED, 'backdropColor' => 'transparent'],
                    'angleLines' => ['color' => self::HAIRLINE],
                    'grid' => ['color' => self::HAIRLINE],
                    'pointLabels' => ['color' => self::INK],
                ],
                'legend' => ['display' => false],
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
                    'backgroundColor' => self::ACCENT,
                    'borderRadius' => 4,
                ]],
            ],
            'options' => [
                'legend' => ['display' => false],
                'scales' => [
                    'xAxes' => [['ticks' => ['fontColor' => self::INK_MUTED], 'gridLines' => ['color' => self::HAIRLINE]]],
                    'yAxes' => [['ticks' => ['fontColor' => self::INK_MUTED], 'gridLines' => ['color' => self::HAIRLINE]]],
                ],
            ],
        ]);
    }

    private static function build(array $config, int $width = 500, int $height = 300): string
    {
        $encoded = urlencode(json_encode($config, JSON_THROW_ON_ERROR));

        return "https://quickchart.io/chart?c={$encoded}&w={$width}&h={$height}&backgroundColor=%23141414&devicePixelRatio=2";
    }
}
