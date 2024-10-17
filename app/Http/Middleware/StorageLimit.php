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
use Services\FileService;

/**
 * Description of CheckOrganizationAccess
 *
 * @author Mohamed
 */
class StorageLimit
{
    private $scurityHelper , $userService  ,
             $fileService ;

    public function __construct(SecurityHelper $scurityHelper, UserService $userService ,FileService $fileService )
    {
        $this->scurityHelper = $scurityHelper;
        $this->userService = $userService;
        $this->fileService = $fileService;

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
        $user= $this->scurityHelper->getCurrentUser();
        if ($user) {
            try {
                $storage = $this->fileService->getOriganizationStorage($user->organization_id);
                if(isset($storage->directory_quota)){
                    $size = $storage->used_size;
                    if(isset($request->files)){
                        foreach (array_flatten($request->files->all()) as $file) {
                            $size += $file->getClientSize(); // size in bytes!
                        }
                    }
                    if(isset($request->file)){
                        $size += $file->getClientSize(); // size in bytes!
                    }
                    $size_gigabytes = $size/pow(1024,3);
                    // quota check
                    if($storage->directory_quota>$size_gigabytes){
                        return $next($request);
                    }
                    else{
                        return response()->json(['error' => [[['message_ar' =>"تجاوزت المساحة المتاحة",'message'=>'storage quota exceeded']]]], 400);
                    }
            
                }
                return $next($request);
            } catch (\Exception $e) {
                return $next($request);
            }
        } else {
            return response()->json(['message' => ["Not Allowed"]], 401);
        } 
    }       
}