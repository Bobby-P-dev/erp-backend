<?php

namespace App\Models\Core;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'label',
        'guard_name',
        'permission_category_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function permissionCategory()
    {
        return $this->belongsTo(PermissionCategory::class);
    }
}
