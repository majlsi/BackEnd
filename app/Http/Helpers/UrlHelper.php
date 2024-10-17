<?php

namespace Helpers;

class UrlHelper
{
    public static function getFullUrl($url)
    {
        return url($url);
    }
}
