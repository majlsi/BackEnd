<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Configration extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['introduction_video_url','support_email','mjlsi_system_before_meeting_video_url','explain_create_meeting_video_url',
            'introduction_arabic_pdf_url','manage_board_meeting_video_url','manage_board_meeting_extra_video_url','introduction_english_pdf_url',
            'third_pdf_url'];
    						
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'configrations';


    public static function rules($action, $id = null) {
        switch ($action) {
            case 'update':
                return array(
                    'introduction_video_url' => 'required',
                    'support_email' => 'required',
                );
        }
    }

}
