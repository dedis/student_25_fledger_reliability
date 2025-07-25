<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimelessDataPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'node_id',
        'value',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
