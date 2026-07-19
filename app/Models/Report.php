<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assessment_id', 'price_table_id', 'engine_version_id', 'answers_snapshot', 'narrative', 'narrative_error', 'html_content', 'generated_at', 'unlocked_at', 'stream_payment_link_id', 'stream_invoice_id'])]
class Report extends Model
{
    protected function casts(): array
    {
        return [
            'answers_snapshot' => 'array',
            'narrative' => 'array',
            'generated_at' => 'datetime',
            'unlocked_at' => 'datetime',
        ];
    }

    public function isUnlocked(): bool
    {
        return $this->unlocked_at !== null;
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function priceTable(): BelongsTo
    {
        return $this->belongsTo(PriceTable::class);
    }

    public function engineVersion(): BelongsTo
    {
        return $this->belongsTo(EngineVersion::class);
    }
}
