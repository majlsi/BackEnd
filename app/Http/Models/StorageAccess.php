<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class StorageAccess extends Model
{
    //
    protected $fillable = [
        'directory_id','user_id','file_id', 'can_read', 'can_edit','can_delete'
    ];

    public function directory()
    {
        return $this->belongsTo('Models\Directory');
    }

    public function file()
    {
        return $this->belongsTo('Models\File');
    }

    public function user(){
        return $this->belongsTo('Models\User');
    }
}
