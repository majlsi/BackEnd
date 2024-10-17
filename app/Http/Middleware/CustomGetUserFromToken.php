<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Middleware;


use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\Authenticate;
use Illuminate\Http\JsonResponse;

use JWTAuth;

/**
 * Description of CustomGetUserFromToken
 *
 * @author yasser.mohamed
 */
class CustomGetUserFromToken extends Authenticate
{
        /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if (!$token = $this->auth->setRequest($request)->getToken()) {
            return response()->json([
                'message' => 'token not provided',
            ],400);
            //return $this->respond('tymon.jwt.absent', 'token_not_provided', 400);
        }

        try {
            $payload = JWTAuth::parseToken()->getPayload();

        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'token expired',
            ],401);
            // return $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'token invalid',
            ],401);
            // return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
        }
        // allow meeting guests and normal users
        if (!$payload->get('user_id') && !$payload->get("meeting_guest_id")) {
            return response()->json([
                'message' => 'user not found',
            ],404);
            // return $this->respond('tymon.jwt.user_not_found', 'user_not_found', 404);
        }
        
        // $this->events->fire('tymon.jwt.valid', $payload->get('user_id'));

        return $next($request);
    }
}