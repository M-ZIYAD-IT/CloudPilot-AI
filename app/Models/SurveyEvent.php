<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assessment_id', 'event', 'page_name'])]
class SurveyEvent extends Model
{
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
