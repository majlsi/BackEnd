<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Helpers\SecurityHelper;
use Helpers\StorageHelper;
use Helpers\UploadHelper;
use Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Services\FileService;
use Services\DirectoryService;
use Storages\StorageFactory;
use Services\NotificationService;
use Models\File;
use Jobs\ShareFileEmail;
use Jobs\RemoveFileAccessEmail;
use Validator;

class FileController extends Controller {
    private $DirectoryService;

    private $FileService;
    private $securityHelper;
    private $storageHelper;
    protected $storage;
    private $notificationHelper;
    private $notificationService;


    public function __construct(FileService $FileService, SecurityHelper $securityHelper, DirectoryService $DirectoryService,StorageHelper $storageHelper,
        NotificationHelper $notificationHelper, NotificationService $notificationService) {

        $this->FileService = $FileService;
        $this->securityHelper = $securityHelper;
        $this->DirectoryService = $DirectoryService;
        $this->storageHelper = $storageHelper;
        $this->notificationHelper = $notificationHelper;
        $this->notificationService = $notificationService;
        $this->storage = StorageFactory::createStorage();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $fileId
     * @return Response
     */
    public function show($fileId) {
        $file = $this->FileService->getById($fileId);
        $file["file_type_icon"]=$file->fileType->file_type_icon;
        return response()->json($file, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request, $directoryId = null) {
        $data = $request->all();
        // dd($data);
        $user = $this->securityHelper->getCurrentUser();

        $parent_directory_path = '';
        $data["file_owner_id"] = $user->id;

        $data['organization_id'] = $user->organization_id;
        if ($directoryId != null) {
            $data["directory_id"] = $directoryId;
            $parent_directory = $this->DirectoryService->getById($directoryId);
            $parent_directory_path =  $parent_directory->directory_path;
        }

        $data['file_name_ar'] = $data['file_name'];


        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,jpg,png,doc,docx,odt,txt,rtf,xls,xlsx,ppt,pptx,pdf,avi,mov,mp4,wmv|max:'.config('attachment.file_size')
        ]);
        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }
        $path = $this->storage->uploadFile($request,$parent_directory_path);

        if ($path == null) {
            return response()->json(["error" => "The file can't be uploaded"], 400);

        }
        $data["file_path"] = $path;

        $data["file_size"] = $this->storage->getSize($path);

        $data["file_type_id"] = $this->storage->getFileType($path);


        $validator = Validator::make($data, File::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $File = $this->FileService->create($data);
        return response()->json($File, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $fileId
     * @return Response
     */
    public function update(Request $request, $fileId) {
        $data = $request->all();
        $validator = Validator::make($data, File::rules('update', $fileId));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $File = $this->FileService->update($fileId, $data);
        return response()->json(['message' => ['Item Updated successfully.']], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $fileId
     * @return Response
     */
    public function destroy($fileId) {
        $File = $this->FileService->getById($fileId);

        $this->FileService->delete ($fileId);
    }


    /**
 * download the specified resource.
 *
 * @param  int  $fileId
 * @return Response
 */
    public function download($fileId) {
        $file = $this->FileService->getById($fileId);

        return $this->storage->download($file->file_path, $file->file_name);
    }




    public function shareFile(Request $request, $fileId) {
        $data = $request->all();
        $user  =  $this->securityHelper->getCurrentUser();
        $file = $this->FileService->addStorageAccess($fileId, $data);
        // send notification
        $usersIds = array_column($data,'user_id');
        $notificationData = $this->notificationHelper->prepareNotificationDataForSharingFile(null,$file,$user,false,config('sharingNotifications.shareFile'),['users_ids' => $usersIds]);
        $this->notificationService->sendNotification($notificationData);
        ShareFileEmail::dispatch($file,$usersIds);
        return response()->json(['message' => ['Item Updated successfully.']], 200);
    }


    public function removeStorageAccess($fileId,$storageAccessId) {
        $user  =  $this->securityHelper->getCurrentUser();
        $file = $this->FileService->getById($fileId);
        $storageAccess = $this->DirectoryService->getStorageAccessById($storageAccessId);
        $this->FileService->removeStorageAccess($storageAccessId);
        // send notification
        $usersIds = [$storageAccess->user_id];
        $notificationData = $this->notificationHelper->prepareNotificationDataForSharingFile(null,$file,$user,false,config('sharingNotifications.removeFileAccess'),['users_ids' => $usersIds]);
        $this->notificationService->sendNotification($notificationData);
        RemoveFileAccessEmail::dispatch($file,$usersIds);
        return response()->json(['message' => ['Item Updated successfully.']], 200);
    }

    public function rename(Request $request,$fileId) {
        $data = $request->all();
        $file = $this->FileService->getById($fileId);
        $file->file_name = $data['name'];
        $file->file_name_ar = $data['name'];
        $file = $this->FileService->update($fileId, $file->toArray());
        return response()->json(['message' => ['Item Updated successfully.']], 200);
    }

    public function myFiles(Request $request) {
        $user = $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object)($request->all());
        $myFiles = $this->FileService->getMyFiles($userId, $filter);

        return response()->json($myFiles, 200);
    }

    public function newFiles(Request $request) {
        $user = $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object)($request->all());
        $myNewFiles = $this->FileService->getMyFiles($userId, $filter);

        return response()->json($myNewFiles, 200);
    }
    public function getShared(Request $request) {
        $user = $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object)($request->all());
        $myNewFiles = $this->FileService->getShared($userId, $filter);

        return response()->json($myNewFiles, 200);
    }


    public function getSharedRecent(Request $request) {
        $user = $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object)($request->all());
        $myNewFiles = $this->FileService->getSharedRecent($userId, $filter);

        return response()->json($myNewFiles, 200);
    }


    public function getRecent(Request $request) {
        $user = $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object)($request->all());
        $myNewFiles = $this->FileService->getRecent($userId, $filter);

        return response()->json($myNewFiles, 200);
    }

    public function addFiles(Request $request , $directoryId = null) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $directory_path = "/uploads";
        if ($directoryId != null) {
            $data["directory_id"] = $directoryId;
            $parent_directory = $this->DirectoryService->getById($directoryId);
            $directory_path = $parent_directory->directory_path;

        }
        if ($data['files']) {
            $validator = Validator::make($request->all(), [
                'files.*' => 'required|mimes:jpeg,jpg,png,pdf,txt,doc,docx,odt,xls,xlsx,ppt,pptx,avi,mov,mp4,wmv,rtf|max:'.config('attachment.file_size'),
            ], UploadHelper::message());
            if ($validator->fails()) {
                return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
            }
            $result = [];


            $path = $directory_path;
            $urls = $this->storage->uploadFiles($request, $path, 'files');
            $result = [];
            $filesData = [];
            foreach($data['files'] as $index => $file) {
                $fileData = $this->storageHelper->mapUploadFile($file, $urls, $index, $user, $directoryId);
                $filesData [] = $fileData;

            }
            $result = $this->FileService->createMany($filesData);
            return response()->json($result, 200);
        } else {
            return response()->json(['error' => 'There are no files'], 400);
        }

    }

    public function searchFiles(Request $request){
        $user = $this->securityHelper->getCurrentUser();
        $userId = $user->id;
        $filter = (object)($request->all());
        $filesResult = $this->FileService->searchFiles($userId, $filter);

        return response()->json($filesResult, 200);
    }


    public function searchGlobal(Request $request){
        $user = $this->securityHelper->getCurrentUser();
        $timeZoneDiff = $user->organization->timeZone->diff_hours;
        $userId = $user->id;
        $filter = (object)($request->all());
        $filesResult = $this->FileService->searchGlobal($userId, $filter,$timeZoneDiff);
        return response()->json($filesResult, 200);
    }

    public function getOriganizationStorage(){
        $user = $this->securityHelper->getCurrentUser();
        $organization_id = $user->organization_id;
        $origanization_storage = $this->FileService->getOriganizationStorage($organization_id);
        $hasExceededQuota = false;
        if($user->organization_id){
            $hasExceededQuota =  $this->FileService->hasExceededQuota($user->organization_id);
        }
        $origanization_storage['has_exceeded_quota'] = $hasExceededQuota; 
        return response()->json($origanization_storage, 200);
    }
}


