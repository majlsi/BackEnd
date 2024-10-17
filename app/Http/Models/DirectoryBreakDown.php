<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class DirectoryBreakDown extends Model
{
    //
    protected $fillable = [
        'parent_id', 'directory_id', 'level',
    ];


    public function parent()
    {
        return $this->belongsTo('Models\Directory','parent_id');
    }
    public function directory()
    {
        return $this->belongsTo('Models\Directory','directory_id');
    }
}
