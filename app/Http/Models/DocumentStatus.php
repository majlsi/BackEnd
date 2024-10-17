<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class DocumentStatus extends Model {

    protected $fillable = ['document_status_name_ar','document_status_name_en'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'document_statuses';



}
