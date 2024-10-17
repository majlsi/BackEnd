<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;


class OrganizationType extends Model 
{

    protected $fillable = ['organization_type_name_ar','organization_type_name_en'];
    protected $dates = ['created_at', 'updated_at'];
    protected $table = 'organization_types';


}
