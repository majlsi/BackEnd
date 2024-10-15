<?php
namespace App\Resolvers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Models\User;


class UserResolver implements \OwenIt\Auditing\Contracts\UserResolver
{
  
    /**
     * {@inheritdoc}
     */
    public static function resolve()
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            if (!$payload->get('user_id')) {
                return null;
            }
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $ex) {
            return null;
        }
        return User::where('id',$payload->get('user_id'))->first();
    }

}