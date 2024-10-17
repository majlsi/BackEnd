<?php

namespace Storages;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Storage;

interface IStorage
{

    public function uploadFile(Request $request, $path);

    public function createDirectory($path, $rootPath = '/uploads/');

    public function getFileType($path);

    public function download($path,$file_name);

    public function getSize($path);

    public function downloadDirectoy($directory);

    public function uploadFiles(Request $request, $path, $fileName);
}