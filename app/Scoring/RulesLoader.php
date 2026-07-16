<?php

namespace App\Scoring;

final class RulesLoader
{
    public static function load(string $version, string $file): array
    {
        $path = resource_path("scoring/{$version}/{$file}.json");

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }
}
