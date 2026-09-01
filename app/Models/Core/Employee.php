<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'name',
        'nik',
        'company_id',
        'division_id',
        'position_id',
        'job_level_id',
        'email',
        'is_active',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function jobLevel()
    {
        return $this->belongsTo(JobLevel::class);
    }
}
