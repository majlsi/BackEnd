<?php

namespace Repositories;

use Carbon\Carbon;
class UserVerificationTokenRepository extends BaseRepository
{

    public function model()
    {
        return 'Models\UserVerificationToken';
    }

    public function checkCode($verificationCode,$userId)
    {
        return $this->model
            ->where('verification_code', $verificationCode)
            ->where('user_id', $userId)
            ->where('is_used',0)
            ->where('expire_date', '>=', Carbon::now())->first();
    }

    public function getLastVerificationCode($userId){
        return $this->model
        ->where('user_id', $userId)
        ->where('is_used',0)
        ->where('expire_date', '>=', Carbon::now())->orderBy('id','DESC')->first();
    }
}
