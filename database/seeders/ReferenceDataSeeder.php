<?php

namespace Database\Seeders;

use App\Models\EngineVersion;
use App\Models\PriceTable;
use App\Scoring\RulesLoader;
use App\Scoring\ScoringEngine;
use Illuminate\Database\Seeder;

/**
 * Seeds the reference data every environment needs to score an assessment at
 * all - the current price table and engine version rows. Safe to run alone
 * in production (php artisan db:seed --class=ReferenceDataSeeder), unlike
 * DatabaseSeeder which also creates dev-only fake demo data.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $priceTableData = RulesLoader::load(ScoringEngine::PRICE_TABLE_VERSION, 'price-table');

        PriceTable::firstOrCreate(
            ['version' => ScoringEngine::PRICE_TABLE_VERSION],
            [
                'as_of_date' => $priceTableData['_meta']['as_of_date'],
                'data' => $priceTableData,
            ]
        );

        EngineVersion::firstOrCreate(
            ['version' => ScoringEngine::VERSION],
            ['description' => 'MVP scoring engine v1: readiness score, platform recommendation, 6R map, compliance register, TCO bands']
        );
    }
}
