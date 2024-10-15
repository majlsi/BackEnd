<?php

namespace Storages;
use Illuminate\Http\Request;
use Storage;
use Input;
class BaseStorage implements IStorage {

    protected $disk;

    public function __construct($config){
        $this->disk = Storage::disk($config);
    }
    public function uploadFile(Request $request, $path)
    {
        if (Input::hasFile('file')) {
            $file = Input::file('file');
            $file_name =  basename($file->getClientOriginalName(), '.' .$file->getClientOriginalExtension());
            $name = preg_replace('/\s+/', '_', $file_name);
            $filename = time() .'_' . $name;
            $filename =  preg_replace('/[^A-Za-z0-9\-]/', '', $filename );
            $filename =  $filename  . "." . $file->getClientOriginalExtension();
            $exists = $this->disk->putFileAs($path,$file,$filename);;
            return $exists;
        } else {
            return null;
        }
    }

    public function createDirectory($path,$rootPath = '/uploads/'){
        $name = preg_replace('/\s+/', '_', $path);
        $directory =  $rootPath . $name . time() . '/';
	    $exists = $this->disk->makeDirectory($directory,0775,true,true);
        if($exists){
            return $directory;
        }
        else {
            return null;
        }
    }

    public function getSize($path){
        $size = $this->disk->size($path);
        return $size;
    }


    public function getFileType($path){

        $file_type_id = null;

        $terms = explode(".", $path);

        $ext_index = count($terms) - 1; 

        $ext = strtolower($terms[$ext_index]);

        $ext_info = config('fileTypes.' . $ext);

        return $ext_info;
    }

    public function download($path,$file_name){
        $file_name = mb_convert_encoding($file_name,'ASCII');
        return $this->disk->download($path,$file_name);
    }


    public function downloadDirectoy($directory)
    {
        return response()->streamDownload(function () use ($directory) {
            $options = new \ZipStream\Option\Archive();
            $options->setSendHttpHeaders(false);
            
            // create a new zipstream object
            $zip = new \ZipStream\ZipStream($directory['directory_name'] . '.zip', $options);
            $this->zipDirectory($zip,$directory);

            return $zip->finish();
        },str_replace('/','%2F',$directory['directory_name']) . '.zip');
    }

    public function uploadFiles(Request $request, $path, $fileName){
        if (Input::hasFile($fileName)) {
            $files = Input::file('files');
            $filesPaths = [];
            foreach ($files as $key => $file) {
                $file_name =  basename($file->getClientOriginalName(), '.' .$file->getClientOriginalExtension());
                $name = preg_replace('/\s+/', '_', $file_name);
                $filename = time() .'_' . $name;
                $filename =  preg_replace('/[^A-Za-z0-9\-]/', '', $filename );
                $filename =  $filename  . "." . $file->getClientOriginalExtension();
                $filesPaths[] = $this->disk->putFileAs($path,$file,$filename);
            }
            return $filesPaths;
        } else {
            return null;
        }
    }



    function zipDirectory($zip,$directory,$parentPath = ''){

        if(isset($directory['files'])){
            foreach ($directory['files'] as  $file) {
                # code...
                $disk_base_path = $this->disk->getDriver()->getAdapter()->getPathPrefix();
                $disk_base_path = $disk_base_path?$disk_base_path:'';
                $terms = explode(".", $file['file_path']);

                $ext_index = count($terms) - 1; 
        
                $ext = $terms[$ext_index];
                $zip->addFileFromPath($parentPath .  $file['file_name'] . '.' . $ext, $disk_base_path .  $file['file_path']);
            }
        }

        if(isset($directory['children'])){
            foreach($directory['children'] as $childDirectory){
                $path = $parentPath . $childDirectory['directory_name'] . '/';
    
                $this->zipDirectory($zip,$childDirectory,$path);
            }
        }

    }

    public function uploadFileByName($file, $path)
    {
        $file_name =  basename($file->getClientOriginalName(), '.' . $file->getClientOriginalExtension());
        $name = preg_replace('/\s+/', '_', $file_name);
        $filename = time() . '_' . $name;
        $filename =  preg_replace('/[^A-Za-z0-9\-]/', '', $filename);
        $filename =  $filename  . "." . $file->getClientOriginalExtension();
        $exists = $this->disk->putFileAs($path, $file, $filename);;
        return $exists;
    }

}