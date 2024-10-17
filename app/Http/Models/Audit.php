<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;


class Audit extends Model 
{

    protected $fillable = [
        'user_type',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'audits';

}