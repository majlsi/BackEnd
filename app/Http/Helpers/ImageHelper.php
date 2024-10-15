<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class ImageHelper
{


    public function __construct( )
    {
    }

    public function prepareLogoDataOnCreate($data){
        $imageData = [];
        
        if(isset($data['logo_image'])){
            $imageData = $data['logo_image'];
        }

        return $imageData;
    }

    public function profileImageForUsersCreatedByAdmin(){
        return $profileImage = ['original_image_url' => 'img/logo_large.png',
         'image_url' => 'img/logo_large.png'];
    }
}
