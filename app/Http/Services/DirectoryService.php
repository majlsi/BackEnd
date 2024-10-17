<?php

namespace Services;

use Repositories\DirectoryRepository;
use Repositories\FileRepository;
use Repositories\StorageAccessRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;

class DirectoryService extends BaseService
{

    protected $fileRepositroy;
    protected $storageAccessRepository;
    public function __construct(DatabaseManager $database, DirectoryRepository $repository,FileRepository $fileRepositroy,
    StorageAccessRepository $storageAccessRepository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->fileRepositroy = $fileRepositroy;
        $this->storageAccessRepository = $storageAccessRepository;
    }

    public function prepareCreate(array $data)
    {
        $Directory = $this->repository->create($data);

        $directoryBreakDowns[] = ['parent_id' => $Directory->id,'level'=>'0'];

        // static rights
        if(isset($data["parent_directory_id"])){

            $parent = $this->repository->find($data["parent_directory_id"]);

            $parentBreakDown = $parent->parentBreakDowns->toArray();

            foreach ($parentBreakDown as $key => $value) {
                # code...
                $directoryBreakDowns[$key+1]['parent_id']= $value['parent_id'];
                $directoryBreakDowns[$key+1]['level']= $value['level']+1;
            }

        }
        $Directory->parentBreakDowns()->createMany($directoryBreakDowns);

        return $Directory;
    }

    public function getUserDirectories($userId)
    {
        $Directories = $this->repository->getUserDirectories($userId);
        return $Directories;
    }

    public function addStorageAccess($directoryId,$data)
    {
        $directory = $this->repository->find($directoryId);

        foreach($data as $index => $storageAccess){
            if($directory->directory_owner_id == $storageAccess['user_id'])
            {
                unset($data[$index]);
            }
        }

        $directory->storageAccess()->createMany($data);
        return $directory;
    }

    public function removeStorageAccess($storageAccessId)
    {
        return $this->storageAccessRepository->delete($storageAccessId);
    }


    public function getStorageAccess($directoryId)
    {
        $directory = $this->repository->find($directoryId);
        $directoryAccess =   $directory->storageAccess()->with('user')->get();
        return $directoryAccess;
    }


    public function getDetails($userId,$id)
    {
        $directory = [];
        $directories = $this->repository->getDetails($userId,$id)->toArray();
        $directory = $directories[0];
        $directory['can_read'] = in_array(true ,array_column($directories, 'can_read'));
        $directory['can_edit'] = in_array(true ,array_column($directories, 'can_edit'));
        $directory['can_delete'] = in_array(true ,array_column($directories, 'can_delete'));
        $directory['can_share'] = in_array(true ,array_column($directories, 'can_share'));

        $ancestors = [];
        foreach ($directories as $index => $ancestor) {
            # code...
            if($index != 0 && $ancestor['can_read']){
                $ancestors[$index - 1] = $ancestor;
            }
        }
        $directory['ancestors']= array_reverse($ancestors);
        return $directory;
    }




    public function prepareUpdate(Model $model, array $data)
    {
        $files=[];
        $directories = [];
        if (isset($data["files"])) {
            $DirectoryAttendees=$data["files"];
            unset($data["files"]);
        }
        if (isset($data["child_directories"])) {
            $DirectoryOrganizers=$data["child_directories"];
            unset($data["child_directories"]);
        }
        $this->repository->update($data, $model->id);  
    }

    public function prepareDelete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function hasRight($directoryId,$userId,$rightId){
        $canAccess = false;

        $directory =  $this->repository->getDirectoryRights($directoryId,$userId);

        if($directory && $directory[$rightId]){
            $canAccess = true;
        }

        return $canAccess;
    }

    public function getChildrenDirectoriesWithFiles($directoryId,$userId){
        $root = null;
        $directories = $this->repository->getChildrenDirectoriesWithFiles($directoryId,$userId)->toArray();
        if(count($directories)> 0){
            $root = $directories[0];
            $root['children'] = $this->buildTree($directories,$directoryId);
        }

        return  $root ;
    }

    public function getMyDirectoires($user_id,$filter){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "directories.created_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }

        return $this->repository->getMyDirectoires($filter->PageNumber, $filter->PageSize, $filter->SearchObject, $filter->SortBy, $filter->SortDirection, $user_id);

    }


    public function getShared($user_id,$filter){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "storage_accesses.created_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }

        return $this->repository->getShared($filter->PageNumber, $filter->PageSize, $filter->SearchObject, $filter->SortBy, $filter->SortDirection, $user_id);

    }


    public function getRecent($user_id,$filter){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "directories.updated_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }

        return $this->repository->getRecent($filter->PageNumber, $filter->PageSize, $filter->SearchObject, $filter->SortBy, $filter->SortDirection, $user_id);

    }






    function buildTree(array &$elements, $parentId = 0) {
        $branch = array();

        foreach ($elements as $index => $element) {
            if ($element['parent_directory_id'] == $parentId) {
                $children =  $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
                unset($elements[$index]);
            }
        }
        return $branch;
    }
    
    public function getDetailsDirectories($directoryId,$user_id,$filter){
        if (isset($filter->SearchObject)) {
            $params = $filter->SearchObject;
        } else {
            $params = [];
        }
        $params['directory_id'] = $directoryId;
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "directories.updated_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }

        $data = $this->repository->getDetailsDirectories($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $user_id);
 
        $data->Results = array_map(function ($el) {
            $el['directory_users'] = array_merge($el['directory_users'] ,$el['parent_directory']['directory_users']);
            unset($el['parent_directory']);
            return $el;
        }, $data->Results->toArray());
        return $data;
    }

    public function getStorageAccessById($storageAccessId)
    {
        return $this->storageAccessRepository->find($storageAccessId,array('*'));
    }

    public function getFilesForDirectory($directoryId)
    {
        return $this->repository->getFilesForDirectory($directoryId);
    }
}
