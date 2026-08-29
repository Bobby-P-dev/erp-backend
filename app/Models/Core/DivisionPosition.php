<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class DivisionPosition extends Model
{
    protected $table = 'division_positions';
    protected $fillable = [
        'division_id',
        'position_id',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
