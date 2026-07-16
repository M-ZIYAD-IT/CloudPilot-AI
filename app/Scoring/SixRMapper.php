<?php

namespace App\Scoring;

/**
 * Rules-based 6R mapper. Covers Rehost, Replatform, Repurchase, Refactor and
 * Retain; "Retire" is intentionally never returned in v1 because the MVP
 * survey (Phase 1, sections A/B/D/G) collects no signal on whether a declared
 * app is still needed - fabricating a Retire trigger with zero evidence would
 * be worse than omitting it.
 */
final class SixRMapper
{
    public function map(array $answers, array $apps): array
    {
        $residencyRequired = ($answers['compliance_data_residency'] ?? null) === 'Yes, required';

        return array_map(fn (array $app) => $this->mapApp($app, $residencyRequired), $apps);
    }

    private function mapApp(array $app, bool $residencyRequired): array
    {
        $name = $app['name'] ?? 'Unnamed application';
        $category = $app['category'] ?? 'Other';
        $isCots = $app['is_cots'] ?? null;
        $vendorSupported = $app['vendor_supported'] ?? null;
        $licenseTiedToHardware = $app['licensing_tied_to_hardware'] ?? false;

        $saasFriendlyCategories = ['Email', 'File shares', 'CRM', 'ERP', 'BI / analytics'];

        [$strategy, $justification] = match (true) {
            $residencyRequired && $category === 'Database' => [
                'Retain',
                'Data residency is required and this is a database workload; until an in-Kingdom cloud region is confirmed and validated, retaining this workload on-prem is the lower-risk choice.',
            ],
            $isCots === true && $vendorSupported === false => [
                'Repurchase',
                'Vendor support has ended for this commercial product; replacing it with a supported SaaS/COTS equivalent is safer than migrating unsupported software as-is.',
            ],
            $isCots === true && $licenseTiedToHardware === true => [
                'Repurchase',
                'Hardware-tied licensing is a common migration trap; repurchasing as a cloud-native/SaaS equivalent avoids carrying the licensing constraint forward.',
            ],
            $isCots === true && $vendorSupported === true && in_array($category, $saasFriendlyCategories, true) => [
                'Repurchase',
                "Mature SaaS equivalents exist for this category ({$category}); repurchasing is typically faster and cheaper than lifting the current install.",
            ],
            $isCots === true => [
                'Replatform',
                'Supported commercial software with a likely cloud-managed equivalent (e.g. a managed database or app service); replatforming captures cloud operational benefits without a full rewrite.',
            ],
            $isCots === false && $licenseTiedToHardware === true => [
                'Refactor',
                'Custom-built and tied to on-prem hardware licensing; refactoring is needed to remove that dependency before it can run in the cloud.',
            ],
            $isCots === false => [
                'Rehost',
                'Custom-built software with no hardware-licensing constraint can typically be lifted-and-shifted to cloud VMs as a first step, with refactoring considered later.',
            ],
            default => [
                'Rehost',
                'Insufficient signal on whether this is COTS or custom-built; defaulting to the lowest-risk lift-and-shift strategy pending more discovery.',
            ],
        };

        return [
            'name' => $name,
            'category' => $category,
            'strategy' => $strategy,
            'justification' => $justification,
        ];
    }
}
