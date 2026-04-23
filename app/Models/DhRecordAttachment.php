<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DhRecordAttachment extends Model
{
    protected $fillable = [
        'record_id',
        'file_path',
        'original_name',
        'description',
        'mime',
        'size',
        'is_trashed',
        'trashed_at',
        'trashed_by',
        'share_token',
    ];

    protected $casts = [
        'is_trashed' => 'boolean',
        'trashed_at' => 'datetime',
    ];

    public function record()
    {
        return $this->belongsTo(DhRecord::class, 'record_id');
    }
}
