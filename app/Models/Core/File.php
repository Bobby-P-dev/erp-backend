<?php

namespace App\Models\Core;

use App\Models\Purchasing\SupplierDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'original_name',
        'stored_name',
        'file_path',
        'file_type',
        'file_size',
        'file_extension',
        'file_mime_type',
        'disk',
        'uploaded_by',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function supplierDocuments(): HasMany
    {
        return $this->hasMany(SupplierDocument::class, 'file_id');
    }
}
