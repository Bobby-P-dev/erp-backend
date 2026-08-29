<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'positions';

    protected $hidden = [
        'pivot'
    ];

    protected $fillable = [
        'code',
        'name',
        'is_active'
    ];

    public function divisions()
    {
        return $this->belongsToMany(
            Division::class,
            'division_positions'
        );
    }
}
