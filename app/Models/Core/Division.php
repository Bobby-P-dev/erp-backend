<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use SoftDeletes;

    protected $table = 'divisions';

    protected $hidden = [
        'pivot'
    ];

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'is_active',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function positions()
    {
        return $this->belongsToMany(
            Position::class,
            'division_positions'
        );
    }
}
