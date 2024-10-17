<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestType extends Model{
    use SoftDeletes;
    protected $fillable = [
        'request_type_name_ar',
        'request_type_name_en',
    ];

    protected $dates = ['created_at', 'updated_at'];
    protected $table = 'request_types';
}