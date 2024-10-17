<?php

namespace Repositories;


class DirectoryRepository extends BaseRepository {

    /**
     * Determine the model of the repository
     *
     */
    public function model() {
        return 'Models\Directory';
    }

    public function getUserDirectories($userId)
    {
        $directories = $this->model
        ->with(['directoryUsers' => function ($query){
            $query->selectRaw('users.*, images.image_url')
            ->leftJoin('images','users.profile_image_id','images.id');
        }])
        ->selectRaw('distinct directories.*,directories.directory_name as name')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.directory_id')
        ->leftJoin('directories as roots','roots.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses','directories.id','storage_accesses.directory_id')
        ->where('storage_accesses.user_id', $userId)
        ->orWhere('directories.directory_owner_id',$userId)
        ->orWhere('roots.directory_owner_id',$userId)
        ->orderby('order')
        ->get();
        return $directories;
    }

    public function getDetails($userId,$id)
    {
        $directories = $this->model
        ->selectRaw('distinct folders.*,directory_break_downs.level,
        case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_edit end as can_share,
        case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_read end as can_read,
        case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_edit end as can_edit,
        case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_delete end as can_delete')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses',function($join) use ($userId) {
            $join->on('storage_accesses.directory_id', '=', 'folders.id');
            $join->where('storage_accesses.user_id', '=', $userId);

        })
        ->where('directories.id',$id)->orderBy('directory_break_downs.level')->get();
        return $directories;
    }

    public function getChildrenDirectoriesWithFiles($directoryId,$userId){
        $directories = $this->model->select('folders.*')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.parent_id')
        ->leftJoin('directories as folders','directory_break_downs.directory_id','folders.id')
        ->where('directories.id',$directoryId)
        ->distinct()->with('files')->get();
        return $directories;
    }

    public function getMyDirectoiresQuery($searchObj,$userId){
        $query = $this->model
        ->with(['directoryUsers' => function ($query){
                $query->selectRaw('users.*, images.image_url')
                ->leftJoin('images','users.profile_image_id','images.id');
            }])
        ->selectRaw('distinct directories.*,directories.directory_name as name,( select count(files.id) from files where files.directory_id = directories.id and files.deleted_at is null ) as files_count, true as can_edit ,true as can_delete,true as can_share')
        ->where('directory_owner_id',$userId)
        ->whereNull('parent_directory_id');
       return $query;
    }

    public function getMyDirectoires($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->getMyDirectoiresQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getSharedQuery($searchObj,$userId){
        $query = $this->model
        ->with(['directoryUsers' => function ($query){
                $query->selectRaw('users.*, images.image_url')
                ->leftJoin('images','users.profile_image_id','images.id');
            }])
        ->selectRaw('distinct directories.*,directories.directory_name as name, ( select count(files.id) from files where files.directory_id = directories.id and files.deleted_at is null ) as files_count ,storage_accesses.can_read,storage_accesses.can_delete,storage_accesses.can_edit, storage_accesses.can_edit as can_share ,storage_accesses.created_at')
        ->join('storage_accesses','storage_accesses.directory_id','directories.id')
        ->where('storage_accesses.user_id',$userId)
        ->where('storage_accesses.can_read',true);
       return $query;
    }

    public function getShared($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->getSharedQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }


    public function getRecentQuery($searchObj,$userId){
        $query = $this->model->with(['directoryUsers' => function ($query){
            $query->selectRaw('users.*, images.image_url')
            ->leftJoin('images','users.profile_image_id','images.id');
        }])
        ->selectRaw('distinct directories.*,directories.directory_name as name, ( select count(files.id) from files where files.directory_id = directories.id and files.deleted_at is null ) as files_count,
        max(case when(folders.directory_owner_id =' . $userId .') then 1 else parentAccess.can_edit end) over(PARTITION BY directories.id) as can_edit,
        max(case when(folders.directory_owner_id =' . $userId .') then 1 else parentAccess.can_delete end) over(PARTITION BY directories.id) as can_delete,
        max(case when(folders.directory_owner_id =' . $userId .') then 1 else parentAccess.can_edit end) over(PARTITION BY directories.id) as can_share')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses as parentAccess',function($join) use ($userId) {
            $join->on('parentAccess.directory_id', '=', 'folders.id');
            $join->where('parentAccess.user_id', '=', $userId);
        })
        ->whereRaw('(folders.directory_owner_id = ? OR( parentAccess.user_id= ? and parentAccess.can_read = 1 ))',array($userId,$userId))
        ->with(['directoryUsers' => function ($query){
            $query->selectRaw('users.*, images.image_url')
            ->leftJoin('images','users.profile_image_id','images.id');
        }])->distinct();
       return $query;
    }

    public function getRecent($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->getRecentQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getDetailsDirectoriesQuery($searchObj,$userId){
        $query = $this->model
        ->with(['directoryUsers' => function ($query){
            $query->selectRaw('users.*, images.image_url')
            ->leftJoin('images','users.profile_image_id','images.id');
        },'parentDirectory.directoryUsers' => function ($query){
                $query->selectRaw('users.*, images.image_url')
                ->leftJoin('images','users.profile_image_id','images.id');
            }])
        ->selectRaw('distinct directories.*,directories.directory_name as name,(select count(files.id) from files where files.directory_id = directories.id and files.deleted_at is null ) as files_count,
        max(case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_edit end) over(PARTITION BY directories.id) as can_edit,
        max(case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_delete end) over(PARTITION BY directories.id) as can_delete,
        max(case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_edit end) over(PARTITION BY directories.id) as can_share')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses',function($join) use ($userId) {
            $join->on('storage_accesses.directory_id', '=', 'folders.id');
            $join->where('storage_accesses.user_id', '=', $userId);
        })
        ->where('directories.parent_directory_id',$searchObj['directory_id']);
       return $query;
    }
    public function getDetailsDirectories($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->getDetailsDirectoriesQuery($searchObj,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }


    public function getDirectoryRights($directoryId,$userId){
        $query = $this->model
        ->selectRaw('distinct directories.*,directories.directory_name as name,
        max(case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_read end) over(PARTITION BY directories.id) as can_read,
        max(case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_edit end) over(PARTITION BY directories.id) as can_edit,
        max(case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_delete end) over(PARTITION BY directories.id) as can_delete,
        max(case when(folders.directory_owner_id =' . $userId .') then 1 else storage_accesses.can_edit end) over(PARTITION BY directories.id) as can_share')
        ->leftJoin('directory_break_downs','directories.id','directory_break_downs.directory_id')
        ->leftJoin('directories as folders','folders.id','directory_break_downs.parent_id')
        ->leftJoin('storage_accesses',function($join) use ($userId) {
            $join->on('storage_accesses.directory_id', '=', 'folders.id');
            $join->where('storage_accesses.user_id', '=', $userId);
        })
        ->where('directories.id',$directoryId)->first();
       return $query;
    }


    public function getFilesForDirectory($directoryId)
    {
        return $this->model
            ->leftJoin('directory_break_downs', 'directories.id', 'directory_break_downs.directory_id')
            ->leftJoin('files', 'directory_break_downs.directory_id', '=', 'files.directory_id')
            ->selectRaw('COUNT(files.id) AS files_count')
            ->where('directory_break_downs.parent_id', '=', $directoryId)
            ->first();
    }
}
