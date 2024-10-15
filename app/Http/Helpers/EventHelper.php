<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use App\Events\SendNotificationEvent;

class EventHelper
{
    

    public function __construct()
    {
    
    }

    public static function fireEvent($firingData,$eventClassName)
    {
        try{
            event(new $eventClassName($firingData));
        }
         catch (\Exception $e) {
                report($e);
            }
    }


  
    
    
}
