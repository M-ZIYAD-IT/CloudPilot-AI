<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['version', 'as_of_date', 'data'])]
class PriceTable extends Model
{
    protected function casts(): array
    {
        return [
            'as_of_date' => 'date',
            'data' => 'array',
        ];
    }
}
