<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;


class Language extends Model 
{

    protected $fillable = ['language_name_ar','language_name_en'];
    protected $dates = ['created_at', 'updated_at'];
    protected $table = 'languages';


}
