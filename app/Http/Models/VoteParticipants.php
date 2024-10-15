<?php

namespace Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class VoteParticipants extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    use HasFactory;

    protected $fillable  = ['meeting_guest_id', 'user_id', 'vote_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'vote_participants';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
            case 'update':
                return array(
                    'vote_id' => 'required|numeric',
                );
        }
    }

    public function users()
    {
        return $this->belongsTo('Models\User', 'user_id');
    }

    public function guests()
    {
        return $this->belongsTo('Models\MeetingGuest', 'meeting_guest_id');
    }

    public function votes()
    {
        return $this->belongsTo('Models\Vote', 'vote_id');
    }
}
