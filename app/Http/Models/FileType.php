<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileType extends Model
{
    //
    protected $fillable = [
        'file_type_ext',
        'file_type_icon'
    ];
    public function files(){
        return $this->hasMany('Models\File');
    }
}
