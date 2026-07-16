<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assessment_id', 'name', 'category', 'is_cots', 'vendor_supported', 'licensing_tied_to_hardware'])]
class AppEntry extends Model
{
    protected $table = 'apps';

    protected function casts(): array
    {
        return [
            'is_cots' => 'boolean',
            'vendor_supported' => 'boolean',
            'licensing_tied_to_hardware' => 'boolean',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
