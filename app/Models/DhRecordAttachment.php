<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DhRecordAttachment extends Model
{
    protected $fillable = [
        'record_id','file_path','original_name','mime','size'
    ];

    public function record(){
        return $this->belongsTo(DhRecord::class, 'record_id');
    }
}
