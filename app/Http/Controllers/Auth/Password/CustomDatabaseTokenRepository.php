<?php
namespace App\Http\Controllers\Auth\Password;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class CustomDatabaseTokenRepository extends DatabaseTokenRepository
{

    // Overrides the standard token creation function
    public function create(CanResetPasswordContract $user)
    {
        $username = $user->getUsernameForPasswordReset();

        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();
        $payload=$this->getPayload($username, $token);
        $this->getTable()->insert($payload);

        return $payload;
    }

    protected function deleteExisting(CanResetPasswordContract $user)
    {
        return $this->getTable()->where('username', $user->getUsernameForPasswordReset())->delete();
    }

    protected function getPayload($username, $token)
    {
        return ['username' => $username, 'token' => $token, 'created_at' => new Carbon,'reset_code' =>rand(1000, 9999)];
    }

    public function exists(CanResetPasswordContract $user, $token)
    {
        $username = $user->getUsernameForPasswordReset();

        $record = (array) $this->getTable()->where('token', $token)->first();

        return $record && ! $this->tokenExpired($record["created_at"]) ;
    }

}