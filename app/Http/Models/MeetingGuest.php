<?php

namespace Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Lang;
use OwenIt\Auditing\Contracts\Auditable;
use Tymon\JWTAuth\Contracts\JWTSubject;


class MeetingGuest extends Authenticatable implements Auditable, JWTSubject
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
        'meeting_id',
        'organization_id',
        'email',
        'full_name',
        'order',
        'meeting_role_id',
        'can_sign',
        'send_mom',
        'is_signature_sent',
        'is_signature_sent_individualy',
        'is_signed',
        'signature_comment',
        'meeting_attendance_status_id',
        'is_accept_absent_by_organiser',
        'chat_user_id'
    ];
    protected $table = 'meeting_guests';
    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'meeting_id' => 'required|numeric|exists:meetings,id',
                    'email' => 'required|email',
                    'order' => 'required|numeric|min:1'
                );
        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'save':
                return array(
                    'meeting_id.required' => [
                        'message_ar' =>  Lang::get('validation.custom.meeting_id.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.meeting_id.required', [], 'en'),
                    ],
                    'meeting_id.numeric' => [
                        'message_ar' =>  Lang::get('validation.custom.meeting_id.numeric', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.meeting_id.numeric', [], 'en'),
                    ],
                    'meeting_id.exists' => [
                        'message_ar' =>  Lang::get('validation.custom.meeting_id.exists', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.meeting_id.exists', [], 'en'),
                    ],
                    'email.required' => [
                        'message_ar' =>  Lang::get('validation.custom.email.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.email.required', [], 'en'),
                    ],
                    'email.email' => [
                        'message_ar' =>  Lang::get('validation.custom.email.email', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.email.email', [], 'en'),
                    ],
                    'order.required' => [
                        'message_ar' =>  Lang::get('validation.custom.order.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.order.required', [], 'en'),                        
                    ],
                    'order.numeric' => [
                        'message_ar' =>  Lang::get('validation.custom.order.numeric', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.order.numeric', [], 'en'),
                    ],
                );
        }
    }

    public function meeting()
    {
        return $this->belongsTo('Models\Meeting');
    }

    public function organization()
    {
        return $this->belongsTo('Models\Organization', 'organization_id');
    }

    public function meetingAttendanceStatus()
    {
        return $this->belongsTo('Models\MeetingAttendanceStatus', 'meeting_attendance_status_id');
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
        $customClaims = [
            'meeting_guest_id' => $this->id,
            'email' => $this->email,
            'meeting_id' => $this->meeting_id,
            'meeting_schedule_from' => $this->meeting->meeting_schedule_from,
            'meeting_schedule_to' => $this->meeting->meeting_schedule_to,
            'user_id' => null
        ];
        return $customClaims;
    }
}
