<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DhFolder extends Model
{
    protected $table = 'dh_folders';

    protected $fillable = [
        'folder_name',
        'month_label',
        'remarks',
        'parent_id',
        'is_trashed',
        'trashed_at',
        'trashed_by',
    ];

    protected $casts = [
        'is_trashed' => 'boolean',
        'trashed_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function records()
    {
        return $this->hasMany(DhRecord::class, 'folder_id');
    }
}
