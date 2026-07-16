<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assessment_id', 'price_table_id', 'engine_version_id', 'answers_snapshot', 'generated_at'])]
class Report extends Model
{
    protected function casts(): array
    {
        return [
            'answers_snapshot' => 'array',
            'generated_at' => 'datetime',
        ];
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
