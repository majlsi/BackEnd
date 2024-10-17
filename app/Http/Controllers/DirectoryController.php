<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Helpers\SecurityHelper;
use Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Services\DirectoryService;
use Services\FileService;
use Services\NotificationService;
use Models\Directory;
use Storages\StorageFactory;
use Jobs\ShareDirecectoryEmail;
use Jobs\RemoveDirecectoryAccessEmail;
use Validator;

class DirectoryController extends Controller
{
    private $DirectoryService;
    private $securityHelper;
    protected $storage;
    private $FileService;
    private $notificationHelper;
    private $notificationService;
   
    public function __construct(DirectoryService $DirectoryService,FileService $FileService ,SecurityHelper $securityHelper,
        NotificationHelper $notificationHelper, NotificationService $notificationService)
    {
        
        $this->DirectoryService = $DirectoryService;
        $this->FileService = $FileService;
        $this->securityHelper = $securityHelper;
        $this->notificationHelper = $notificationHelper;
        $this->notificationService = $notificationService;
        $this->storage = StorageFactory::createStorage();

    }

    public function index()
    {
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $parent = null;

        return response()->json($this->DirectoryService->getUserDirectories($userId), 200);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $directoryId
     * @return Response
     */
    public function show($directoryId)
    {
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        return response()->json($this->DirectoryService->getById($directoryId), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function addOnDirectory(Request $request, $directoryId)
    {
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $data = $request->all();
        $data["parent_directory_id"]  =  $directoryId;
        $data["directory_owner_id"]  =  $userId;
        $data['organization_id'] = $user->organization_id;
        $Directory = $this->DirectoryService->getById($directoryId);


        $path =  $this->storage->createDirectory($data["directory_name"],$Directory->directory_path);

        if($path == null){
            return response()->json(["error" => "failed to create directory"], 400);
        }

        $data["directory_path"]  = $path;

        $validator = Validator::make($data, Directory::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $Directory = $this->DirectoryService->create($data);
        return response()->json($Directory, 200);
    }


        /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $data = $request->all();
        $data["directory_owner_id"]  =  $userId;
        unset($data["parent_directory_id"]);
        $data['organization_id'] = $user->organization_id;

        $path =  $this->storage->createDirectory($data["directory_name"]);

        if($path == null){
            return response()->json(["error" => "failed to create directory"], 400);
        }

        $data["directory_path"]  = $path;


        $validator = Validator::make($data, Directory::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $Directory = $this->DirectoryService->create($data);
        return response()->json($Directory, 200);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  int  $directoryId
     * @return Response
     */
    public function update(Request $request, $directoryId)
    {
        $data = $request->all();
        $validator = Validator::make($data, Directory::rules('update', $directoryId));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $Directory = $this->DirectoryService->update($directoryId, $data);
        return response()->json(['message' => ['Item Updated successfully.']], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $directoryId
     * @return Response
     */
    public function destroy($directoryId)
    {
        $Directory = $this->DirectoryService->getById($directoryId);
        if (config('customSetting.deleteFile')) {
            $files = $this->DirectoryService->getFilesForDirectory($directoryId);
            if ($files->files_count > 0) {
                $result = [
                    'errors' => [
                        [
                            'error_ar' => "لا يمكنك حذف مجلد يحتوى على ملفات",
                            'error' => 'You cannot delete a folder containing files'
                        ]
                    ]
                ];
                return response()->json($result, 400);
            }
        }
        $this->DirectoryService->delete($directoryId);
    }



    public function getDetails($directoryId)
    {
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        return response()->json($this->DirectoryService->getDetails($userId,$directoryId), 200);
    }


    public function shareFolder(Request $request,$directoryId){
        $data = $request->all();
        $user  =  $this->securityHelper->getCurrentUser();
        $directory = $this->DirectoryService->addStorageAccess($directoryId, $data);
        // send notification
        $usersIds = array_column($data,'user_id');
        $notificationData = $this->notificationHelper->prepareNotificationDataForSharingFile($directory,null,$user,true,config('sharingNotifications.shareDirecrory'),['users_ids' => $usersIds]);
        $this->notificationService->sendNotification($notificationData);
        ShareDirecectoryEmail::dispatch($directory,$usersIds);
        return response()->json(['message' => ['Item Updated successfully.']], 200);
    }

    public function removeStorageAccess($directoryId,$storageAccessId){
        $user  =  $this->securityHelper->getCurrentUser();
        $directory = $this->DirectoryService->getById($directoryId);
        $storageAccess = $this->DirectoryService->getStorageAccessById($storageAccessId);
        $this->DirectoryService->removeStorageAccess($storageAccessId);
        // send notification
        $usersIds = [$storageAccess->user_id];
        $notificationData = $this->notificationHelper->prepareNotificationDataForSharingFile($directory,null,$user,true,config('sharingNotifications.removeDirectoryAccess'),['users_ids' => $usersIds]);
        $this->notificationService->sendNotification($notificationData);
        RemoveDirecectoryAccessEmail::dispatch($directory,$usersIds);
        return response()->json(['message' => ['Item Updated successfully.']], 200);
    }


    public function rename(Request $request,$directoryId){
        $data = $request->all();
        $Directory = $this->DirectoryService->getById($directoryId);
        $Directory->directory_name = $data['name'];
        $Directory->directory_name_ar = $data['name'];
        $Directory = $this->DirectoryService->update($directoryId, $Directory->toArray());
        return response()->json(['message' => ['Item Updated successfully.']], 200);
    }


    public function download($directoryId)
    {
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $directoryWithFiles = $this->DirectoryService->getChildrenDirectoriesWithFiles($directoryId,$userId);

        return  $this->storage->downloadDirectoy($directoryWithFiles);   
    }

    public function myDirectories(Request $request){
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object) ($request->all());
        $myDirectories = $this->DirectoryService->getMyDirectoires($userId,$filter);

        return response()->json($myDirectories, 200);
    }

    public function getShared(Request $request){
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object) ($request->all());
        $myDirectories = $this->DirectoryService->getShared($userId,$filter);

        return response()->json($myDirectories, 200);
    }


    public function getRecent(Request $request){
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object) ($request->all());
        $myDirectories = $this->DirectoryService->getRecent($userId,$filter);

        return response()->json($myDirectories, 200);
    }

    public function getDetailsDirectories($directoryId , Request $request){
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object) ($request->all());
        $directories = $this->DirectoryService->getDetailsDirectories($directoryId,$userId,$filter);
        return response()->json($directories, 200);
    }

    public function getDetailsFiles($directoryId , Request $request){
        $user  =  $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object) ($request->all());
        $files = $this->FileService->getDetailsFiles($directoryId,$userId,$filter);
        return response()->json($files, 200);
    }
}
