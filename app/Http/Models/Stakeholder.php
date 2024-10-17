<?php

namespace Models;

use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lang;
use OwenIt\Auditing\Contracts\Auditable;

class Stakeholder extends Authenticatable implements Auditable
{
    use Notifiable;

    use SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    /**
     * The message_ars that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date_of_birth', 'identity_number', 'share', 'user_id', 'is_active'
    ];


    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'date_of_birth' => 'required|date',
                    'identity_number' => 'required',
                    'share' => 'required|numeric|min:0|max:100',
                );
            case 'update':
                return array(
                    'date_of_birth' => 'required|date',
                    'identity_number' => 'required',
                    'share' => 'required|numeric|min:0|max:100',
                );
            case 'activate-deactivate':
                return array(
                    'is_active' => 'required|boolean',
                    'stakesholder_id' => 'required|numeric|exists:stakeholders,id',
                );
        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'update':
                return array(
                    'date_of_birth.required' => [
                        'message_ar' =>  Lang::get('validation.custom.date_of_birth.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.date_of_birth.required', [], 'en'),
                    ],
                    'identity_number.required' => [
                        'message_ar' => Lang::get('validation.custom.identity_number.required', [], 'ar'),
                        'message' => Lang::get('validation.custom.identity_number.required', [], 'en')
                    ],
                    'share.required' => [
                        'message_ar' => Lang::get('validation.custom.share.required', [], 'ar'),
                        'message' => Lang::get('validation.custom.share.required', [], 'en')
                    ],
                    'share.numeric' => [
                        'message_ar' => Lang::get('validation.custom.share.numeric', [], 'ar'),
                        'message' => Lang::get('validation.custom.share.numeric', [], 'en')
                    ],
                    'share.min' => [
                        'message_ar' => Lang::get('validation.custom.share.min', [], 'ar'),
                        'message' => Lang::get('validation.custom.share.min', [], 'en')
                    ],
                    'share.max' => [
                        'message_ar' => Lang::get('validation.custom.share.max', [], 'ar'),
                        'message' => Lang::get('validation.custom.share.max', [], 'en')
                    ],
                );
            case 'save':
                return array(
                    'date_of_birth.required' => [
                        'message_ar' => Lang::get('validation.custom.date_of_birth.required', [], 'ar'),
                        'message' => Lang::get('validation.custom.date_of_birth.required', [], 'en')
                    ],
                    'identity_number.required' => [
                        'message_ar' => Lang::get('validation.custom.identity_number.required', [], 'ar'),
                        'message' => Lang::get('validation.custom.identity_number.required', [], 'en')
                    ],
                    'share.required' => [
                        'message_ar' => Lang::get('validation.custom.share.required', [], 'ar'),
                        'message' => Lang::get('validation.custom.share.required', [], 'en')
                    ],
                    'share.numeric' => [
                        'message_ar' => Lang::get('validation.custom.share.numeric', [], 'ar'),
                        'message' => Lang::get('validation.custom.share.numeric', [], 'en')
                    ],
                    'share.min' => [
                        'message_ar' => Lang::get('validation.custom.share.min', [], 'ar'),
                        'message' => Lang::get('validation.custom.share.min', [], 'en')
                    ],
                    'share.max' => [
                        'message_ar' => Lang::get('validation.custom.share.max', [], 'ar'),
                        'message' => Lang::get('validation.custom.share.max', [], 'en')
                    ],
                );
            case 'activate-deactivate':
                return array(
                    'is_active.required' => [
                        'message_ar' => Lang::get('validation.custom.is_active.required', [], 'ar'),
                        'message' => Lang::get('validation.custom.is_active.required', [], 'en')
                    ],
                    'stakesholder_id.required' => [
                        'message_ar' => Lang::get('validation.custom.stakesholder_id.required', [], 'ar'),
                        'message' => Lang::get('validation.custom.stakesholder_id.required', [], 'en')
                    ],
                    'stakesholder_id.exists' => [
                        'message_ar' => Lang::get('validation.custom.stakesholder_id.exists', [], 'ar'),
                        'message' => Lang::get('validation.custom.stakesholder_id.exists', [], 'en')
                    ],
                );
        }
    }


    public function user()
    {
        return $this->belongsTo('Models\User');
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::deleting(function ($user) {
    //         $relatedRelations = ['committes', 'meetings', 'meetingParticipants', 'meetingOrganizers', 'directoryAccess', 'ownedFiles', 'ownedDirectories'];
    //         foreach ($relatedRelations as $relatedRelation) {
    //             if ($user->$relatedRelation()->count() > 0) {
    //                 throw new Exception("Model have child records");
    //             }
    //         }
    //     });
    // }
}
