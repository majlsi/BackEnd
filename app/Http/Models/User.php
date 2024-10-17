<?php

namespace Models;

use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lang;
use OwenIt\Auditing\Contracts\Auditable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Connectors\ChatConnector;

class User extends Authenticatable implements Auditable, JWTSubject
{
    use Notifiable;

    use SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'username', 'role_id', 'oauth_provider', 'oauth_uid', 'is_verified', 'organization_id', 'is_active',
        'name_ar', 'profile_image_id', 'user_phone', 'id', 'main_page_id', 'last_login', 'disclosure_file_id',
        'language_id', 'job_title_id', 'nickname_id', 'user_title_id','chat_user_id','disclosure_url','job_id','job_title','responsible_administration','transfer_no',
        'is_blocked','blacklist_file_id','blacklist_reason'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function rules($action, $id = null, $multiSaveData = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'name_ar' => 'required_without:name',
                    'name' => 'required_without:name_ar',
                    'password' => 'required',
                    'email' => 'required|email',
                    'role_id' => 'required',
                    'username' => 'required|unique:users,username,NULL,id,deleted_at,NULL',
                    'language_id' => 'required',
                );
            case 'update':
                return array(
                    'name_ar' => 'required_without:name',
                    'name' => 'required_without:name_ar',
                    'email' => 'required|email',
                    'role_id' => 'required',
                    'language_id' => 'required',
                    'username' => 'required|unique:users,username,' . $id . ',id,deleted_at,NULL',
                );
            case 'register':
                return array(
                    'name_ar' => 'required_without:name',
                    'name' => 'required_without:name_ar',
                    'password' => 'required',
                    'language_id' => 'required',
                    'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
                );
            case 'save-multiple':
                return array(
                    '*.name_ar' => 'required_without:*.name',
                    '*.name' => 'required_without:*.name_ar',
                    '*.password' => 'required',
                    '*.email' =>
                    [
                        'required',
                        'email',
                        function ($attribute, $value, $fail) use ($multiSaveData) {
                            $uniqueEmails = array_unique(array_column($multiSaveData, 'email'));

                            if (count($uniqueEmails) !== count($multiSaveData)) {
                                $fail('Email addresses within the array must be unique.');
                            }
                        },
                    ],
                    '*.role_id' => 'required',
                    '*.username' => 'required|unique:users,username,NULL,id,deleted_at,NULL',
                    '*.language_id' => 'required',
                );

        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'register':
                return array(
                    'email.required' => ['message' => Lang::get('validation.custom.email.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.email.required', [], 'ar')],

                    'username.required' => ['message' => Lang::get('validation.custom.username.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.username.required', [], 'ar')],

                    'email.unique' => ['message' => Lang::get('validation.custom.email.unique', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.email.unique', [], 'ar')],

                    'name_ar.required_without' => ['message' => Lang::get('validation.custom.name_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.name_ar.required', [], 'ar')],

                    'name.required_without' => ['message' => Lang::get('validation.custom.name.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.name.required', [], 'ar')],

                    'password.required' => ['message' => Lang::get('validation.custom.password.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.password.required', [], 'ar')],

                    'language_id.required' => ['message' => Lang::get('validation.custom.language.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.language.required', [], 'ar')]

                );
            case 'update':
                return array(
                    'email.required' => ['message' => Lang::get('validation.custom.email.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.email.required', [], 'ar')],

                    'username.unique' => ['message' => Lang::get('validation.custom.username.unique', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.username.unique', [], 'ar')],

                    'name_ar.required_without' => ['message' => Lang::get('validation.custom.name_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.name_ar.required', [], 'ar')],

                    'name.required_without' => ['message' => Lang::get('validation.custom.name.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.name.required', [], 'ar')],

                    'password.required' => ['message' => Lang::get('validation.custom.password.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.password.required', [], 'ar')],

                    'role_id.required' => ['message' => Lang::get('validation.custom.role_id.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.role_id.required', [], 'ar')],

                    'language_id.required' => ['message' => Lang::get('validation.custom.language.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.language.required', [], 'ar')]

                );
            case 'save':
                return array(
                    'email.required' => ['message' => Lang::get('validation.custom.email.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.email.required', [], 'ar')],

                    'username.unique' => ['message' => Lang::get('validation.custom.username.unique', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.username.unique', [], 'ar')],

                    'name_ar.required_without' => ['message' => Lang::get('validation.custom.name_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.name_ar.required', [], 'ar')],

                    'name.required_without' => ['message' => Lang::get('validation.custom.name.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.name.required', [], 'ar')],

                    'password.required' => ['message' => Lang::get('validation.custom.password.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.password.required', [], 'ar')],

                    'role_id.required' => ['message' => Lang::get('validation.custom.role_id.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.role_id.required', [], 'ar')],

                    'language_id.required' => ['message' => Lang::get('validation.custom.language.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.language.required', [], 'ar')],

                );
            case 'save-multiple':
                return array(
                    '*.email.required' => ['error' => Lang::get('validation.custom.email.required', [], 'en')
                        , 'error_ar' => Lang::get('validation.custom.email.required', [], 'ar')],

                    '*.username.unique' => ['error' => Lang::get('validation.custom.username.unique', [], 'en')
                        , 'error_ar' => Lang::get('validation.custom.username.unique', [], 'ar')],

                    '*.name_ar.required_without' => ['error' => Lang::get('validation.custom.name_ar.required', [], 'en')
                        , 'error_ar' => Lang::get('validation.custom.name_ar.required', [], 'ar')],

                    '*.name.required_without' => ['error' => Lang::get('validation.custom.name.required', [], 'en')
                        , 'error_ar' => Lang::get('validation.custom.name.required', [], 'ar')],

                    '*.password.required' => ['error' => Lang::get('validation.custom.password.required', [], 'en')
                        , 'error_ar' => Lang::get('validation.custom.password.required', [], 'ar')],

                    '*.role_id.required' => ['error' => Lang::get('validation.custom.role_id.required', [], 'en')
                        , 'error_ar' => Lang::get('validation.custom.role_id.required', [], 'ar')],

                    '*.language_id.required' => ['error' => Lang::get('validation.custom.language.required', [], 'en')
                        , 'error_ar' => Lang::get('validation.custom.language.required', [], 'ar')],

                );        
        }
    }

    public function getUsernameForPasswordReset()
    {
        return $this->username;
    }

    public function role()
    {
        return $this->belongsTo('Models\Role');
    }

    public function image()
    {
        return $this->belongsTo('Models\Image', 'profile_image_id');
    }

    public function organization()
    {
        return $this->belongsTo('Models\Organization', 'organization_id');
    }

    public function mainRight()
    {
        return $this->belongsTo('Models\Right', 'main_page_id');
    }

    public function committes()
    {
        return $this->hasMany('Models\CommitteeUser');
    }

    public function meetings()
    {
        return $this->hasMany('Models\Meeting', 'created_by');
    }

    public function meetingParticipants()
    {
        return $this->hasMany('Models\MeetingParticipant');
    }

    public function meetingOrganizers()
    {
        return $this->hasMany('Models\MeetingOrganiser');
    }

    public function tasks()
    {
        return $this->hasMany('Models\TaskManagement', 'assigned_to');
    }

    public function verificationTokens(){
        return $this->hasMany('Models\UserVerificationToken','user_id');
    }

    public function ownedDirectories()
    {
        return $this->hasMany('Models\Directory','directory_owner_id');
    }

    public function ownedFiles()
    {
        return $this->hasMany('Models\File','file_owner_id');
    }

    public function directoryAccess(){
        return $this->hasMany('Models\StorageAccess');
    }

    public function nickname()
    {
        return $this->belongsTo('Models\Nickname', 'nickname_id');
    }

    public function userTitle()
    {
        return $this->belongsTo('Models\UserTitle', 'user_title_id');
    }

    public function jobTitle()
    {
        return $this->belongsTo('Models\JobTitle', 'job_title_id');
    }


    protected static function boot() {
        parent::boot();

        static::deleting(function ($user) {
            $relatedRelations = ['committes', 'meetings', 'meetingParticipants', 'meetingOrganizers','directoryAccess','ownedFiles','ownedDirectories'];
            foreach ($relatedRelations as $relatedRelation) {
                if ($user->$relatedRelation()->count() > 0) {
                    throw new Exception("Model have child records");
                }
            }

        });
    }

    public function disclosureFile(){
        return $this->belongsTo('Models\File','disclosure_file_id');
    }

        /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        $customClaims = ['user_id' => $this->id];
        // login into chat app
        if ($this->chat_user_id) {
            $loginResponse = ChatConnector::login(['username' => $this->username,'app_id' => config('chat.chatAppId')]);
            if ($loginResponse['is_success']) {
                $customClaims['chat_token'] = $loginResponse['response']['token'];
            }
        }
        return $customClaims;
    }

}
