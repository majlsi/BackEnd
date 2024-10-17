<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Middleware;

use Helpers\SecurityHelper;
use JWTAuth;
use Services\UserService;
use Services\DirectoryService;
use Services\FileService;

/**
 * Description of CheckOrganizationAccess
 *
 * @author Mohamed
 */
class StorageAccessCheck
{
    private $scurityHelper , $userService , $directoryService ,
             $fileService ;

    public function __construct(SecurityHelper $scurityHelper, UserService $userService,DirectoryService $directoryService ,FileService $fileService )
    {
        $this->scurityHelper = $scurityHelper;
        $this->userService = $userService;
        $this->directoryService = $directoryService;
        $this->fileService = $fileService;

    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next,$rightId)
    {
        $account= $this->scurityHelper->getCurrentUser();
        if ($account) {
            try {
                if(isset($request->route()->parameters()['directoryId'])){
                    $directoryId = $request->route()->parameters()['directoryId'];
                    $canAccess = $this->directoryService->hasRight($directoryId,$account->id,$rightId);
                    if(!($canAccess == true)){
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                    else{
                        return $next($request);
                    }
                }
                else if(isset($request->route()->parameters()['fileId'])){
                    $fileId = $request->route()->parameters()['fileId'];
                    $canAccess = $this->fileService->hasRight($fileId,$account->id,$rightId);
                    if(!($canAccess == true)){
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                    else{
                        return $next($request);
                    }
                }
                else{
                    return $next($request);
                }
            } catch (\Exception $e) {
                return $next($request);
            }
        } else {
            return response()->json(['message' => ["Not Allowed"]], 401);
        } 
    }       
}