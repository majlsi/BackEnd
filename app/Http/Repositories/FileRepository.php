<?php

namespace Repositories;

use DB;

class FileRepository extends BaseRepository {

    /**
     * Determine the model of the repository
     *
     */
    public function model() {
        return 'Models\File';
    }



    public function getMyFilesQuery($searchObj, $userId)
    {
        $files = $this->model->with(['fileUsers' => function ($query) {
            $query->selectRaw('users.*, images.image_url')
            ->leftJoin('images', 'users.profile_image_id', 'images.id');
        }])->selectRaw('distinct files.*, files.file_name as name, 1 as can_edit');
        $files->with('fileType')->where('file_owner_id', $userId)
            ->where('is_system', '=', false)->whereNull('directory_id');

        if (config('customSetting.deleteFile')) {
            $files->leftJoin('requests', function ($join) {
                $join->on('files.id', '=', 'requests.target_id')
                ->where('requests.request_type_id', '=', config('requestTypes.deleteFile'));
            })->selectRaw('IF(COUNT(requests.id) > 0, 0, 1) as can_delete')
            ->groupBy('files.id', 'files.file_name');
        } else {
            $files->selectRaw('1 as can_delete');
        }

        return $files;
    }

    public function getMyNewFilesQuery($searchObj,$userId){
        $files = $this->model
        ->with(['fileUsers' => function ($query){
            $query->selectRaw('users.*, images.image_url')
            ->leftJoin('images','users.profile_image_id','images.id');
        }])
        ->selectRaw('files.*,files.file_name as name,1 as can_edit ,1 as can_delete,1 as can_share')
        ->with('fileType')
        ->where('is_system','=',false)
        ->where('file_owner_id',$userId);
        return $files;
    }

    public function getMyNewFiles($pageNumber, $pageSize,$searchObj ,$userId)
    {
        $query = $this->getMyNewFilesQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, 'files.created_at', 'desc');
    }

    public function getMyFiles($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->getMyFilesQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getSharedQuery($searchObj,$userId){
        $files = $this->model
        ->with(['fileUsers' => function ($query){
            $query->selectRaw('users.*, images.image_url')
            ->leftJoin('images','users.profile_image_id','images.id');
        }])
        ->selectRaw('files.*,files.file_name as name,storage_accesses.can_read,storage_accesses.can_delete,storage_accesses.can_edit,storage_accesses.can_edit as can_share ,storage_accesses.created_at')
        ->with('fileType')
        ->join('storage_accesses','storage_accesses.file_id','files.id')
        ->where('is_system','=',false)
        ->where('storage_accesses.user_id',$userId)
        ->where('storage_accesses.can_read',true);
        return $files;
    }

    public function getShared($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->getSharedQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }


    public function getRecentQuery($searchObj,$userId){
        $files = $this->model
        ->with(['fileUsers' => function ($query){
            $query->selectRaw('users.*, images.image_url')
            ->leftJoin('images','users.profile_image_id','images.id');
        }])
        ->selectRaw('distinct files.*,files.file_name as name,
        max(case when(files.file_owner_id =' . $userId . ' or folders.directory_owner_id =' . $userId . ') then 1 else (parentAccess.can_edit or fileAccess.can_edit) end) over(PARTITION BY files.id) as can_edit,
        max(case when(files.file_owner_id =' . $userId .' or folders.directory_owner_id =' . $userId .') then 1 else (parentAccess.can_edit or fileAccess.can_edit) end) over(PARTITION BY files.id) as can_share')
        ->with('fileType')
        ->leftJoin('storage_accesses as fileAccess',function($join) use ($userId) {
            $join->on('fileAccess.file_id', '=', 'files.id');
            $join->where('fileAccess.user_id', '=', $userId);
        })
        ->leftJoin('directories','directories.id','files.directory_id')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses as parentAccess',function($join) use ($userId) {
            $join->on('parentAccess.directory_id', '=', 'folders.id');
            $join->where('parentAccess.user_id', '=', $userId);
        });

        if (config('customSetting.deleteFile')) {
            $files->leftJoin('requests', function ($join) {
                $join->on('files.id', '=', 'requests.target_id')
                ->where('requests.request_type_id', '=', config('requestTypes.deleteFile'));
            })->selectRaw('IF(COUNT(requests.id) > 0, 0, max(case when(files.file_owner_id =' . $userId . ' or folders.directory_owner_id =' . $userId . ') then 1 else (parentAccess.can_delete or fileAccess.can_delete) end) over(PARTITION BY files.id)) as can_delete')
            ->groupBy(
                'files.id',
                'files.file_name',
                'folders.directory_owner_id',
                'parentAccess.can_edit',
                'fileAccess.can_edit',
                'parentAccess.can_delete',
                'fileAccess.can_delete'
            );
        } else {
            $files->selectRaw('max(case when(files.file_owner_id =' . $userId . ' or folders.directory_owner_id =' . $userId . ') then 1 else (parentAccess.can_delete or fileAccess.can_delete) end) over(PARTITION BY files.id) as can_delete');
        }

        $files->where('is_system', '=', false)
        ->whereRaw('(files.file_owner_id =? OR folders.directory_owner_id = ?  OR (fileAccess.user_id =? and fileAccess.can_read = 1 )OR( parentAccess.user_id= ? and parentAccess.can_read = 1 ))',array($userId,$userId,$userId,$userId));
        return $files;
    }

    public function getRecent($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->getRecentQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }


    public function getDetailsFilesQuery($searchObj,$userId){
        $files = $this->model
        ->with(['fileUsers' => function ($query){
            $query->selectRaw('users.*, images.image_url')
            ->leftJoin('images','users.profile_image_id','images.id');
        }])
        ->selectRaw('distinct files.*,files.file_name as name,
        max(case when(files.file_owner_id =' . $userId . ' or folders.directory_owner_id =' . $userId . ') then 1 else (parentAccess.can_edit or fileAccess.can_edit) end) over(PARTITION BY files.id) as can_edit,
        max(case when(files.file_owner_id =' . $userId .' or folders.directory_owner_id =' . $userId .') then 1 else (parentAccess.can_edit or fileAccess.can_edit) end) over(PARTITION BY files.id) as can_share')
        ->with('fileType')
        ->leftJoin('storage_accesses as fileAccess',function($join) use ($userId) {
            $join->on('fileAccess.file_id', '=', 'files.id');
            $join->where('fileAccess.user_id', '=', $userId);
        })
        ->leftJoin('directory_break_downs','files.directory_id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses as parentAccess',function($join) use ($userId) {
            $join->on('parentAccess.directory_id', '=', 'folders.id');
            $join->where('parentAccess.user_id', '=', $userId);
        });

        if (config('customSetting.deleteFile')) {
            $files->leftJoin('requests', function ($join) {
                $join->on('files.id', '=', 'requests.target_id')
                ->where('requests.request_type_id', '=', config('requestTypes.deleteFile'));
            })->selectRaw('IF(COUNT(requests.id) > 0, 0, max(case when(files.file_owner_id =' . $userId . ' or folders.directory_owner_id =' . $userId . ') then 1 else (parentAccess.can_delete or fileAccess.can_delete) end) over(PARTITION BY files.id)) as can_delete')
            ->groupBy(
                'files.id',
                'files.file_name',
                'folders.directory_owner_id',
                'parentAccess.can_edit',
                'fileAccess.can_edit',
                'parentAccess.can_delete',
                'fileAccess.can_delete'
            );
        } else {
            $files->selectRaw('max(case when(files.file_owner_id =' . $userId . ' or folders.directory_owner_id =' . $userId . ') then 1 else (parentAccess.can_delete or fileAccess.can_delete) end) over(PARTITION BY files.id) as can_delete');
        }

        $files->where('is_system', '=', false)
        ->where('files.directory_id',$searchObj['directory_id']);
       return $files;
    }
    public function getDetailsFiles($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->getDetailsFilesQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function searchFilesQuery($searchObj,$userId){
        $term = '';
        if(isset($searchObj['term'])){
            $term = $searchObj['term'];
        }
        $files = $this->model->selectRaw('distinct files.*,files.file_name as name')
        ->with('fileType')
        ->leftJoin('storage_accesses as fileAccess','fileAccess.file_id','files.id')
        ->leftJoin('directories','directories.id','files.directory_id')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses as parentAccess','folders.id','parentAccess.directory_id')
        ->where('files.file_name','LIKE','%'.$term.'%')
        ->where('is_system','=',false)
        ->whereRaw('(files.file_owner_id =? OR folders.directory_owner_id = ?  OR (fileAccess.user_id =? and fileAccess.can_read = 1 )OR( parentAccess.user_id= ? and parentAccess.can_read = 1 ))',array($userId,$userId,$userId,$userId));
        return $files;
    }
    public function searchFiles($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->searchFilesQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }
    public function getFileRights($fileId,$userId)
    {
        $file = $this->model->selectRaw('distinct files.*,files.file_name as name,
        max(case when(files.file_owner_id =' . $userId .' or folders.directory_owner_id =' . $userId .') then 1 else (parentAccess.can_read or fileAccess.can_read) end) over(PARTITION BY files.id) as can_read,
        max(case when(files.file_owner_id =' . $userId .' or folders.directory_owner_id =' . $userId .') then 1 else (parentAccess.can_edit or fileAccess.can_edit) end) over(PARTITION BY files.id) as can_edit,
        max(case when(files.file_owner_id =' . $userId .' or folders.directory_owner_id =' . $userId .') then 1 else (parentAccess.can_delete or fileAccess.can_delete) end) over(PARTITION BY files.id) as can_delete,
        max(case when(files.file_owner_id =' . $userId .' or folders.directory_owner_id =' . $userId .') then 1 else (parentAccess.can_edit or fileAccess.can_edit) end) over(PARTITION BY files.id) as can_share')
        ->leftJoin('storage_accesses as fileAccess',function($join) use ($userId) {
            $join->on('fileAccess.file_id', '=', 'files.id');
            $join->where('fileAccess.user_id', '=', $userId);
        })
        ->leftJoin('directory_break_downs','files.directory_id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses as parentAccess',function($join) use ($userId) {
            $join->on('parentAccess.directory_id', '=', 'folders.id');
            $join->where('parentAccess.user_id', '=', $userId);
        })
        ->where('files.id',$fileId)->first();
       return $file;
    }
    public function searchGlobalQuery($searchObj,$userId){
        $term = '';
        if(isset($searchObj['term'])){
            $term = $searchObj['term'];
        }
        $files = $this->model->with('fileType')
        ->selectRaw('files.id as id,files.file_name as name, 1 as is_file,file_types.file_type_icon as icon,file_types.file_type_ext as ext, users.name as owner, users.name_ar as owner_ar,files.created_at as created_at,files.updated_at as updated_at')
        ->leftJoin('users','users.id','files.file_owner_id')
        ->leftJoin('storage_accesses as fileAccess','fileAccess.file_id','files.id')
        ->leftJoin('file_types','file_types.id','files.file_type_id')
        ->leftJoin('directories','directories.id','files.directory_id')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses as parentAccess','folders.id','parentAccess.directory_id')
        ->where('files.file_name','LIKE','%'.$term.'%')
        ->where('is_system','=',false)
        ->whereRaw('(files.file_owner_id =? OR folders.directory_owner_id = ?  OR (fileAccess.user_id =? and fileAccess.can_read = 1 )OR( parentAccess.user_id= ? and parentAccess.can_read = 1 ))',array($userId,$userId,$userId,$userId))
        ->distinct();
        $query = DB::table('directories')
        ->selectRaw('directories.id as id,directories.directory_name as name,0 as is_file, "" as icon, "" as ext ,users.name as owner, users.name_ar as owner_ar,directories.created_at as created_at,directories.updated_at as updated_at')
        ->leftJoin('users','users.id','directories.directory_owner_id')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses as parentAccess',function($join) use ($userId) {
            $join->on('parentAccess.directory_id', '=', 'folders.id');
            $join->where('parentAccess.user_id', '=', $userId);
        })
        ->whereNull('directories.deleted_at')
        ->where('directories.directory_name','LIKE','%'.$term.'%')
        ->whereRaw('(folders.directory_owner_id = ? OR( parentAccess.user_id= ? and parentAccess.can_read = 1 ))',array($userId,$userId))
        ->distinct()
        ->union($files);
        return $query;
    }
    public function searchGlobal($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->searchGlobalQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getOriganizationStorage($organization_id){
        $query = $this->model->selectRaw('sum(files.file_size) as used_size')
        ->where('files.organization_id', '=', $organization_id);
        if(config('app.include-system-files-in-quota') == false){
            $query = $query->where('files.is_system','!=',1);
        }
        $used_size = $query->first();
        $query = DB::table('organizations')->select('organizations.directory_quota')->where('organizations.id','=',$organization_id);
        $directory_quota = $query->first();
        $retVal =["used_size"=>0,"directory_quota"=>0];

        if($directory_quota != null){
            $retVal["directory_quota"] = $directory_quota->directory_quota;
        }
        if($used_size != null){
            $retVal["used_size"] = $used_size->used_size;
        }
        return $retVal;
    }


    public function getSharedRecentQuery($searchObj,$userId){
        $files = $this->model
        ->with(['fileUsers' => function ($query){
            $query->selectRaw('users.*, images.image_url')
            ->leftJoin('images','users.profile_image_id','images.id');
        }])
        ->selectRaw('distinct files.*,files.file_name as name,
        max(case when(files.file_owner_id =' . $userId . ' or folders.directory_owner_id =' . $userId . ') then 1 else (parentAccess.can_edit or fileAccess.can_edit) end) over(PARTITION BY files.id) as can_edit,
        max(case when(files.file_owner_id =' . $userId .' or folders.directory_owner_id =' . $userId .') then 1 else (parentAccess.can_edit or fileAccess.can_edit) end) over(PARTITION BY files.id) as can_share')
        ->with('fileType')
        ->leftJoin('storage_accesses as fileAccess',function($join) use ($userId) {
            $join->on('fileAccess.file_id', '=', 'files.id');
            $join->where('fileAccess.user_id', '=', $userId);
        })
        ->leftJoin('directories','directories.id','files.directory_id')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses as parentAccess',function($join) use ($userId) {
            $join->on('parentAccess.directory_id', '=', 'folders.id');
            $join->where('parentAccess.user_id', '=', $userId);
        });

        if (config('customSetting.deleteFile')) {
            $files->leftJoin('requests', function ($join) {
                $join->on('files.id', '=', 'requests.target_id')
                ->where('requests.request_type_id', '=', config('requestTypes.deleteFile'));
            })->selectRaw('IF(COUNT(requests.id) > 0, 0, max(case when(files.file_owner_id =' . $userId . ' or folders.directory_owner_id =' . $userId . ') then 1 else (parentAccess.can_delete or fileAccess.can_delete) end) over(PARTITION BY files.id)) as can_delete')
            ->groupBy(
                'files.id',
                'files.file_name',
                'folders.directory_owner_id',
                'parentAccess.can_edit',
                'fileAccess.can_edit',
                'parentAccess.can_delete',
                'fileAccess.can_delete'
            );
        } else {
            $files->selectRaw('max(case when(files.file_owner_id =' . $userId . ' or folders.directory_owner_id =' . $userId . ') then 1 else (parentAccess.can_delete or fileAccess.can_delete) end) over(PARTITION BY files.id) as can_delete');
        }

        $files->where('is_system', '=', false)
        ->whereRaw('((fileAccess.user_id =? and fileAccess.can_read = 1 )OR( parentAccess.user_id= ? and parentAccess.can_read = 1 ))',array($userId,$userId));
        return $files;
    }

    public function getSharedRecent($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->getSharedRecentQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }
    
    public function deleteFiles($file_ids){
        $files = $this->model
        ->whereIn('id', $file_ids)->delete();
    }


    public function getByIdOrNull($id)
    {
        return $this->model->find($id);
    }

}
