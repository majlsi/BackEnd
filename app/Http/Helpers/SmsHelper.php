<?php

namespace Helpers;

use Illuminate\Support\Facades\Config;

class SmsHelper
{

    public static function getSmsBody($view_name, $dataArray)
    {
        $html = view($view_name, $dataArray)->render();
        return $html;
    }
}