<?php

namespace Services;

use Repositories\FileRepository;
use Repositories\StorageAccessRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class FileService extends BaseService
{
    protected $storageAccessRepository;

    public function __construct(DatabaseManager $database, FileRepository $repository, StorageAccessRepository $storageAccessRepository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->storageAccessRepository = $storageAccessRepository;
    }

    public function prepareCreate(array $data)
    {
       return $this->repository->create($data);
    }


    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }
    public function hasRight($fileId,$userId,$rightId){
        $canAccess = false;


        $file =  $this->repository->getFileRights($fileId,$userId);

        if($file && $file[$rightId]){
            $canAccess = true;
        }




        return $canAccess;
    }


    public function addStorageAccess($fileId,$data)
    {
        $file = $this->repository->find($fileId);

        foreach($data as $index => $storageAccess ){
            if($file->file_owner_id == $storageAccess['user_id'])
            {
                unset($data[$index]);
            }
        }
        $file->storageAccess()->createMany($data);
        return $file;
    }


    public function removeStorageAccess($storageAccessId)
    {
        return $this->storageAccessRepository->delete($storageAccessId);
    }

    public function getMyFiles($user_id,$filter){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = ["files.updated_at","files.id"];
        } else {
            $filter->SortBy = [$filter->SortBy,"files.id"];
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = ["DESC","ASC"];
        }
        else{
            $filter->SortDirection = [$filter->SortDirection,"ASC"];
        }
        return $this->repository->getMyFiles($filter->PageNumber, $filter->PageSize, $filter->SearchObject, $filter->SortBy, $filter->SortDirection, $user_id);
    }

    public function getMyNewFiles($userId,$filter){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "files.updated_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        return $this->repository->getMyNewesFiles($filter->PageNumber, $filter->PageSize, $filter->SearchObject, $user_id);
    }
    
    public function getShared($user_id,$filter){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = ["files.updated_at","files.id"];
        } else {
            $filter->SortBy = [$filter->SortBy,"files.id"];
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = ["DESC","ASC"];
        }
        else{
            $filter->SortDirection = [$filter->SortDirection,"ASC"];
        }
        return $this->repository->getShared($filter->PageNumber, $filter->PageSize, $filter->SearchObject, $filter->SortBy, $filter->SortDirection, $user_id);
    }


    public function getSharedRecent($user_id,$filter){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "files.updated_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        return $this->repository->getSharedRecent($filter->PageNumber, $filter->PageSize, $filter->SearchObject, $filter->SortBy, $filter->SortDirection, $user_id);
    }


    public function getRecent($user_id,$filter){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "files.updated_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        return $this->repository->getRecent($filter->PageNumber, $filter->PageSize, $filter->SearchObject, $filter->SortBy, $filter->SortDirection, $user_id);
    }


    public function getDetailsFiles($directoryId,$user_id,$filter){
        if (isset($filter->SearchObject)) {
            $params = $filter->SearchObject;
        } else {
            $params = [];
        }
        $params['directory_id'] = $directoryId;
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = ["files.updated_at","files.id"];
        } else {
            $filter->SortBy = [$filter->SortBy,"files.id"];
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = ["DESC","ASC"];
        }
        else{
            $filter->SortDirection = [$filter->SortDirection,"ASC"];
        }

        return $this->repository->getDetailsFiles($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $user_id);

    }

    public function searchFiles($user_id,$filter){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = ["files.updated_at","files.id"];
        } else {
            $filter->SortBy = [$filter->SortBy,"files.id"];
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = ["DESC","ASC"];
        }
        else{
            $filter->SortDirection = [$filter->SortDirection,"ASC"];
        }
        return $this->repository->searchFiles($filter->PageNumber, $filter->PageSize, $filter->SearchObject, $filter->SortBy, $filter->SortDirection, $user_id);
    }

    public function searchGlobal($user_id,$filter,$timeZoneDiff){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "updated_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        $result = $this->repository->searchGlobal($filter->PageNumber, $filter->PageSize, $filter->SearchObject, $filter->SortBy, $filter->SortDirection, $user_id);
        return $result;
    }
    public function getOriganizationStorage($organization_id)
    {
        return $this->repository->getOriganizationStorage($organization_id);
    }

    public function hasExceededQuota($organization_id){
        $retVal = false;
        $storage = $this->repository->getOriganizationStorage($organization_id);
        if(isset($storage->directory_quota)){
            $size = $storage->used_size;
            $size_gigabytes = $size/pow(1024,3);
            if($storage->directory_quota<$size_gigabytes){
                $retVal = true;
            }
        }
        return $retVal;
    }

    public function createMany($filesData){
        $result = [];

        foreach($filesData as $fileData){
           $file = $this->repository->create($fileData);
           $result[] = ['id'=> $file->id, 'url'=> $file->file_path];
        }
        return $result;
    }

    public function getbyIdOrNull($id)
    {
        return $this->repository->getByIdOrNull($id);
    }

    
}