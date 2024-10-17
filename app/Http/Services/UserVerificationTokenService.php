<?php

namespace Services;

use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Repositories\UserVerificationTokenRepository;
use \Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class UserVerificationTokenService extends BaseService
{

    public function __construct(DatabaseManager $database, UserVerificationTokenRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
    }

    public function prepareCreate(array $data)
    {
        $data["verification_code"] = strtolower(Str::random(config('twoFactorAuthVerification.codeLength')));
        $data["expire_date"] = Carbon::now()->addMinutes(config('twoFactorAuthVerification.expirationDurationInMinute'));
        return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }
    public function checkCode($verificationCode, $userd)
    {
        return $this->repository->checkCode($verificationCode, $userd);
    }

    public function getLastVerificationCode($userId){
        return $this->repository->getLastVerificationCode($userId);
    }
}
