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

    /**
     * Scope a query to search by division name or position name.
     */
    public function scopeSearchDivision($query, $search)
    {
        if (filled($search)) {
            $query->whereHas('division', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Scope a query to search by position name only.
     */
    public function scopeSearchPosition($query, $search)
    {
        if (filled($search)) {
            $query->whereHas('position', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
