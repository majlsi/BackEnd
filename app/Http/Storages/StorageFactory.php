<?php

namespace Storages;


class StorageFactory
{
    public static function createStorage() {
        
        $disk =  config('app.default-disk');

        return new BaseStorage($disk);
    }
}