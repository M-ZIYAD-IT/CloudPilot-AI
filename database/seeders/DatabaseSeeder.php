<?php

namespace Database\Seeders;

use App\Actions\GenerateReport;
use App\Models\Answer;
use App\Models\Assessment;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with reference data plus dev-only demo
     * data. Production seeds ReferenceDataSeeder alone - see its docblock.
     */
    public function run(): void
    {
        $this->call(ReferenceDataSeeder::class);

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
            'business_driver' => ['Cost reduction'],
            'business_timeline' => '3-12 months',
            'business_company_size' => '51-200',
            'business_industry' => 'Retail / e-commerce',
            'business_countries' => ['Saudi Arabia'],
            'infra_location' => 'Own server room',
            'infra_hardware_age' => 'Refresh due within 6 months',
            'compliance_data_categories' => ['PII', 'Payment / PCI'],
            'compliance_regulations' => ['PDPL (Saudi)', 'NCA ECC (Saudi)'],
            'compliance_data_residency' => 'Yes, required',
            'budget_annual_spend' => '$50k - $250k',
            'budget_capex_opex' => 'OpEx preferred',
        ];

        foreach ($answers as $questionKey => $value) {
            Answer::create([
                'assessment_id' => $assessment->id,
                'question_key' => $questionKey,
                'value' => $value,
            ]);
        }

        $assessment->apps()->create([
            'name' => 'In-house Order Management System',
            'category' => 'Custom application',
            'is_cots' => false,
            'vendor_supported' => null,
            'licensing_tied_to_hardware' => true,
        ]);

        app(GenerateReport::class)->execute($assessment);
    }
}
