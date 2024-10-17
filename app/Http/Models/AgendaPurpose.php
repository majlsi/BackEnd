<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class AgendaPurpose extends Model {

    protected $fillable = ['purpose_name_ar','purpose_name_en'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'agenda_purposes';



}
