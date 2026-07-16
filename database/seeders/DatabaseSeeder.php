<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Assessment;
use App\Models\EngineVersion;
use App\Models\Organization;
use App\Models\PriceTable;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $organization = Organization::create([
            'name' => 'Acme Test Co',
            'slug' => 'acme-test-co',
        ]);

        $companyUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'company_user',
        ]);

        $consultant = User::factory()->create([
            'name' => 'Test Consultant',
            'email' => 'consultant@example.com',
            'role' => 'consultant',
        ]);

        $admin = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $organization->users()->attach([
            $companyUser->id => ['is_owner' => true],
            $consultant->id => ['is_owner' => false],
            $admin->id => ['is_owner' => false],
        ]);

        $assessment = Assessment::create([
            'organization_id' => $organization->id,
            'created_by' => $companyUser->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $answers = [
            ['question_key' => 'business.driver', 'value' => ['cost_reduction']],
            ['question_key' => 'infrastructure.location', 'value' => ['own_server_room']],
            ['question_key' => 'compliance.regulations', 'value' => ['pdpl', 'nca_ecc']],
        ];

        foreach ($answers as $answer) {
            Answer::create([
                'assessment_id' => $assessment->id,
                ...$answer,
            ]);
        }

        $priceTable = PriceTable::create([
            'version' => 'v1',
            'as_of_date' => now()->toDateString(),
            'data' => ['note' => 'placeholder MVP price table, populated in Phase 2'],
        ]);

        $engineVersion = EngineVersion::create([
            'version' => 'v1',
            'description' => 'MVP scoring engine, placeholder until Phase 2',
        ]);

        Report::create([
            'assessment_id' => $assessment->id,
            'price_table_id' => $priceTable->id,
            'engine_version_id' => $engineVersion->id,
            'answers_snapshot' => $assessment->answers()->get(['question_key', 'value'])->toArray(),
            'generated_at' => now(),
        ]);
    }
}
