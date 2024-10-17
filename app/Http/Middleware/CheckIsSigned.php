<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Middleware;

use Helpers\SecurityHelper;
use JWTAuth;
use Services\VoteService;

/**
 * Description of CheckIsSigned
 *
 * @author Eman
 */
class CheckIsSigned
{
    private $scurityHelper, $voteService;

    public function __construct(SecurityHelper $scurityHelper, VoteService $voteService)
    {
        $this->scurityHelper = $scurityHelper;
        $this->voteService = $voteService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $account= $this->scurityHelper->getCurrentUser();
        if ($account) {
            try {
                $decesion = $this->voteService->getById($request->route()->parameters()['decesion_id']);
                $voter=$decision->voteResults->where('user_id', $user->id)->first();
                if(($voter && $voter->is_signed === 0)){ 
                    return $next($request);
                } else {
                    return response()->json(['message' => "You have already signed",
                                             'message_ar' => 'لقد قمت بالتوقيع بالفعل'], 400);
                }
            } catch (\Exception $e) {
                return $next($request);
            }
        } else {
            return response()->json(['message' => ["Not Allowed"]], 401);
        }
    }
}
