<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;
use Exception;

class ChatGroup extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    protected $fillable = ['chat_group_name_ar','chat_group_name_en','chat_room_id','creator_id','organization_id','chat_group_logo_id','meeting_id','committee_id','chat_group_type_id',
                           'last_message_text','last_message_date'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'chat_groups';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'chat_group_name_ar' => 'sometimes',
                    'chat_group_name_en' => 'sometimes',
                    'creator_id' => 'required',
                    'organization_id' => 'required',  
                    //'chat_group_users_ids' => 'required',          
                );
            case 'update':
                return array(
                    'chat_group_name_ar' => 'sometimes',
                    'chat_group_name_en' => 'sometimes',
                    'chat_group_users_ids' => 'sometimes',          
                );
            case 'add-users':
                return array(
                    'chat_group_users_ids' => 'required',          
                ); 
            case 'users-number':
                return array(
                    'chat_group_users_ids' => 'min:3',          
                );
        }
    }

    public static function messages($action)
    {    
        switch ($action) {
            case 'save':
                return array(
                    'chat_group_name_ar.sometimes' => ['message'=>Lang::get('validation.custom.chat_group_name_ar.sometimes',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_name_ar.sometimes',[],'ar')],

                    'chat_group_name_ar.unique' => ['message'=>Lang::get('validation.custom.chat_group_name_ar.unique',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_name_ar.unique',[],'ar')],

                    'chat_group_name_en.sometimes' => ['message'=>Lang::get('validation.custom.chat_group_name_en.sometimes',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_name_en.sometimes',[],'ar')], 

                    'chat_group_name_en.unique' => ['message'=>Lang::get('validation.custom.chat_group_name_en.unique',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_name_en.unique',[],'ar')], 
               
                    'chat_group_users_ids.required' => ['message'=>Lang::get('validation.custom.chat_group_users_ids.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_users_ids.required',[],'ar')],
                );
            case 'update':
                return array(
                    'chat_group_name_ar.sometimes' => ['message'=>Lang::get('validation.custom.chat_group_name_ar.sometimes',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_name_ar.sometimes',[],'ar')],
    
                    'chat_group_name_ar.unique' => ['message'=>Lang::get('validation.custom.chat_group_name_ar.unique',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_name_ar.unique',[],'ar')],
    
                    'chat_group_name_en.sometimes' => ['message'=>Lang::get('validation.custom.chat_group_name_en.sometimes',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_name_en.sometimes',[],'ar')], 
    
                    'chat_group_name_en.unique' => ['message'=>Lang::get('validation.custom.chat_group_name_en.unique',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_name_en.unique',[],'ar')], 
                   
                    'chat_group_users_ids.sometimes' => ['message'=>Lang::get('validation.custom.chat_group_users_ids.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_users_ids.required',[],'ar')],
                );
            case 'save-individual-chat':
                return array(       
                    'chat_group_users_ids.required' => ['message'=>Lang::get('validation.custom.member_user_id.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.member_user_id.required',[],'ar')],
                );
            case 'add-users':
                return array(
                    'chat_group_users_ids.required' => ['message'=>Lang::get('validation.custom.chat_group_users_ids.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.chat_group_users_ids.required',[],'ar')],         
                ); 
        }
    }

    public function chatGroupUsers(){
        return $this->hasMany('Models\ChatGroupUser','chat_group_id');
    }

    public function memberUsers(){
        return $this->belongsToMany('Models\User','chat_group_users','chat_group_id', 'user_id');
    }

    public function chatGroupLogo(){
        return $this->belongsTo('Models\Image','chat_group_logo_id');
    }

    public function organization()
    {
        return $this->belongsTo('Models\Organization', 'organization_id');
    }

    public function meeting()
    {
        return $this->belongsTo('Models\Meeting', 'meeting_id');
    }

    public function committee()
    {
        return $this->belongsTo('Models\Committee', 'committee_id');
    }

    public function creator()
    {
        return $this->belongsTo('Models\User', 'creator_id');
    }

    public function guests()
    {
        return $this->belongsToMany('Models\MeetingGuest','chat_group_users','chat_group_id', 'meeting_guest_id');
    }
}