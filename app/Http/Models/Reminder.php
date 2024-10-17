<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Reminder extends Model implements Auditable {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

     use \OwenIt\Auditing\Auditable;
    protected $fillable = ['reminder_description_ar','reminder_description_en','reminder_duration_in_minutes',
                           ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'reminders';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'reminder_description_ar' => 'required',
                    'reminder_duration_in_minutes' => 'required',
                );
            case 'update':
                return array(
                    'reminder_description_ar' => 'required',
                    'reminder_duration_in_minutes' => 'required',
                );
        }
    }

}
