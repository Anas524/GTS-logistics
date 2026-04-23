<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DhRecord extends Model
{
    protected $table = 'dh_records';

    protected $fillable = [
        'folder_id',
        'doc_date',
        'description',
        'file_path',
        'original_name',
    ];

    protected $casts = [
        'doc_date' => 'date',
    ];

    public function folder()
    {
        return $this->belongsTo(DhFolder::class, 'folder_id');
    }

    public function attachments()
    {
        return $this->hasMany(DhRecordAttachment::class, 'record_id');
    }

    public function activeAttachments()
    {
        return $this->hasMany(DhRecordAttachment::class, 'record_id')
            ->where('is_trashed', false);
    }
}
