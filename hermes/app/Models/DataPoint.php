<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'node_id',
        'name',
        'value',
        'time',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    protected function casts(): array
    {
        return [
            'time' => 'timestamp',
        ];
    }
}
