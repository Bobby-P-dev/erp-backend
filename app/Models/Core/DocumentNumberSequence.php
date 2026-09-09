<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentNumberSequence extends Model
{
    protected $table = 'document_number_sequences';

    protected $fillable = [
        'company_id',
        'category',
        'prefix',
        'period',
        'current_number',
        'format',
        'number_length',
        'is_active',
    ];

    protected $casts = [
        'current_number' => 'integer',
        'number_length' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
