<?php

namespace Services;

use Repositories\DocumentRepository;
use Repositories\DocumentUserRepository;
use Repositories\FileRepository;
use Repositories\DirectoryRepository;
use Helpers\NotificationHelper;
use Helpers\EventHelper;
use Helpers\StorageHelper;
use Helpers\SecurityHelper;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Jobs\HandleDocument;
use File;

class DocumentService extends BaseService
{
    private $documentUserRepository, $notificationHelper, $eventHelper, $storageHelper,
        $securityHelper, $fileRepository,$directoryRepository;

    public function __construct(DatabaseManager $database, DocumentRepository $repository, DocumentUserRepository $documentUserRepository,
        NotificationHelper $notificationHelper, EventHelper $eventHelper,
        StorageHelper $storageHelper, SecurityHelper $securityHelper,
        FileRepository $fileRepository, DirectoryRepository $directoryRepository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->documentUserRepository = $documentUserRepository;
        $this->notificationHelper = $notificationHelper;
        $this->eventHelper = $eventHelper;
        $this->storageHelper = $storageHelper;
        $this->securityHelper = $securityHelper;
        $this->fileRepository = $fileRepository;
        $this->directoryRepository = $directoryRepository;
    }

    public function prepareCreate(array $data)
    {
        $documentUsers = [];
        $documentUsersIds = [];
        $user = $this->securityHelper->getCurrentUser();
        if(isset($data['document_users_ids'])){
            $documentUsersIds = $data['document_users_ids'];
            unset($data['document_users_ids']);
        }
        // create file
        $storageFile =  $this->storageHelper->mapSystemFile($data['document_name'],$data['document_url'],0 ,$user);
        $attachmentFile = $this->fileRepository->create($storageFile);
        $data['file_id']  =  $attachmentFile->id;
        // create document
        $document = $this->repository->create($data);

        if(count($documentUsersIds) > 0){
            foreach ($documentUsersIds as $key => $value) {
                $documentUsers[$key]['user_id'] = $value;
                $documentUsers[$key]['document_id'] = $document->id;
                $documentUsers[$key]['document_status_id'] = config('documentStatuses.new');
            }
            // add creator
            $documentUsers[] = ['user_id' => $document->added_by,'document_id' => $document->id,'document_status_id' => config('documentStatuses.new')];
            // create document reviewers
            $document->documentUsers()->createMany($documentUsers);
        }
        // convert document to images
        HandleDocument::dispatch($document,true);

        return $document;
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $documentUsers = [];
        $documentUsersIds = [];

        if(isset($data['document_users_ids'])){
            $documentUsersIds = $data['document_users_ids'];
            $documentUsersIds[] = $model->added_by;
            // update document reviewers
            $oldReviewersIds = array_column($model->reviewres->toArray(), 'id');
            $deletedReviewersIds = array_diff($oldReviewersIds,$documentUsersIds);
            $newReviewersIds = array_diff($documentUsersIds,$oldReviewersIds);

            foreach ($newReviewersIds as $key => $value) {
                $documentUsers[$key]['user_id'] = $value;
                $documentUsers[$key]['document_id'] = $model->id;
                $documentUsers[$key]['document_status_id'] = config('documentStatuses.new');
            }
            // create new reviewers
            if(count($documentUsers) > 0){
                $model->documentUsers()->createMany($documentUsers);
            }
            // delete Reviewers
            if(count($deletedReviewersIds) > 0){
                $model->documentUsers()->whereIn('user_id',$deletedReviewersIds)->delete();
            }
            unset($data['document_users_ids']);
        }
        if($model->document_status_id != config('documentStatuses.new')) {
            unset($data['document_url']);
            unset($data['document_name']);
        } else if (isset($data['document_url']) && file_exists( public_path().'/'.$data['document_url'])){
            $storageFile =  $this->storageHelper->mapSystemFile($data['document_name'],$data['document_url'],0 ,$model->creator);
            if(!$model->file_id){
                $attachmentFile = $this->fileRepository->create($storageFile);
                $data['file_id']  =  $attachmentFile->id;
            } else {
                $this->fileRepository->update($storageFile,$model->file_id);
                unset($data['file_id']);
            }
            
        }

        // update document data
        $this->repository->update($data, $model->id);
        $document = $this->getById($model->id);

        // convert document to images
        if(isset($data['document_url'])){
            HandleDocument::dispatch($document,false);
        }
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function getPagedList($filter,$userId,$organizationId){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "documents.id";
        } else if ($filter->SortBy == 'id') {
            $filter->SortBy = "documents.id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        return $this->repository->getDocumentsPagedList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId,$userId);
    }

    public function getDocumentSlides($document)
    {
        $imageExtensions = ['jpeg', 'jpg', 'png'];
        $videoExtensions = ['avi', 'mov', 'mp4', 'wmv'];
        $url = $document["document_url"];
        $fileUrl = public_path($url);
        $ext = pathinfo(public_path() . $document->document_url, PATHINFO_EXTENSION);
        $path = public_path() . '/uploads/documents/' . $document->id;
        $data = [];
        $data['document_notes'] = null;
        if (File::isDirectory($path)) {
            $filesInFolder = \File::files($path);
            natsort($filesInFolder);
            foreach ($filesInFolder as $imgPath) {
                $images = [];
                $imageDetails = pathinfo($imgPath);
                $imagedetails = getimagesize($imgPath);
                $width = $imagedetails[0];
                $height = $imagedetails[1];
                $imageName = $imageDetails['basename'];
                $images = "/uploads/documents/$document->id/".urlencode($imageName);
                if ($imageDetails['extension'] == 'json') {
                    $data['document_notes'] = $images;
                } else {
                    $data['document_images'][] = $images;
                    $data['document_images_with_size'][] = ['url' => $images,'width' => $width,'height' => $height];
                }

            }

        }

        return $data;
    }
    
    public function updateStatusOfDocumentToDelay(){
        // update document user satatus to delay
        $documentsUsers = $this->documentUserRepository->getDocumentUsersWithDelayedDate()->toArray();
        if (count($documentsUsers)) {
            $documentsUsersIds = array_column($documentsUsers,'id');
            $this->documentUserRepository->updateDocumentUsersStatusToDelay($documentsUsersIds);
        }
        // update document status
        $documents = $this->repository->getAllDocumentsWithReviewEndDateLessCurrentDate();
        foreach ($documents as $key => $document) {
            $documentUsersStatusIds = array_column($document->documentUsers->toArray(),'document_status_id');
            if(!in_array(config('documentStatuses.new'),$documentUsersStatusIds) && !in_array(config('documentStatuses.inProgress'),$documentUsersStatusIds)){
                // create new directory
                if($document->creator->organization->enable_meeting_archiving){
                    $this->createDirectoryForDocument($document);
                }
                // update status to complete
                $this->repository->update(['document_status_id' => config('documentStatuses.complete')], $document->id);
            }
        }        
    }

    public function getDocumentDetails($id,$userId){
        return $this->repository->getDocumentDetails($id,$userId);
    }

    public function CheckErrorInDeleteDocumentUsers($document,$documentData){
        $documentData['document_users_ids'][] = $document->added_by;
        $currentDocumentUsersIds = array_column($document->reviewres->toArray(), 'id');
        $newDocumentUsersIds = isset($documentData['document_users_ids']) ? $documentData['document_users_ids'] : [];
        $deletedUsersIds = array_diff($currentDocumentUsersIds, $newDocumentUsersIds);
        if(count($deletedUsersIds) > 0){
            $results = $this->repository->getDocumentUsersHaveStatusNotNew($document->id,$deletedUsersIds);
            return $results == 0? false: true;
        } else {
            return false;
        }
    }

    public function getStartedDocuments(){
        return $this->repository->getStartedDocuments();
    }

    public function getDocumentDataWithCanSendNotificationFlag($documentId){
        return $this->repository->getDocumentDataWithCanSendNotificationFlag($documentId);
    }

    public function sendNotificationForChangeDocument($document,$user,$notificationType){
        try {
            $notificationData =  $this->notificationHelper->prepareNotificationDataForDocumentation($document,$user,$notificationType);
            $this->eventHelper->fireEvent($notificationData, 'App\Events\ChangeDocumentFileEvent');
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function getDocumentsResultStatusStatisticsForUser($userId,$documentStatusId){
        return $this->repository->getDocumentsResultStatusStatisticsForUser($userId,$documentStatusId)->count();
    }

    public function getDocumentsResultStatusStatisticsForOrganization($organizationId,$documentStatusId){
        return $this->repository->getDocumentsResultStatusStatisticsForOrganization($organizationId,$documentStatusId)->count();
    }

    public function getDocumentsResultStatusStatisticsForCommittee($committeeId,$documentStatusId){
        return $this->repository->getDocumentsResultStatusStatisticsForCommittee($committeeId,$documentStatusId)->count();
    }

    public function getLimitOfDocumentsForUser($userId){
        return $this->repository->getLimitOfDocumentsForUser($userId);
    }

    public function getLimitOfDocumentsForOrganization($organizationId){
        return $this->repository->getLimitOfDocumentsForOrganization($organizationId);
    }

    public function getLimitOfDocumentsForCommittee($committeeId){
        return $this->repository->getLimitOfDocumentsForCommittee($committeeId);
    }

    public function createDirectoryForDocument($document){
        $directory = $this->storageHelper->createDocumentDirectory($document,$document->creator);

        $directory = $this->directoryRepository->create($directory->toArray());

        $directoryBreakDowns[] = ['parent_id' => $directory->id,'level'=>'0'];
        $directory->parentBreakDowns()->createMany($directoryBreakDowns);

        // create Storage Access And Files for Directory
        $this->createStorageAccessAndFilesForDirectory($document,$directory);
    }

    private function createStorageAccessAndFilesForDirectory($document,$directory){
        $storageAccess = [];
        $systemFile = $document->file_id;
        foreach($document->reviewres as $index => $reviewer){
            $storageAccess[] = ['user_id'=>$reviewer->id,'can_read'=> true , 'can_edit'=> false, 'can_delete'=> false];
        }
        if($document->file_id){
            $this->fileRepository->update(['is_system' => 0,'directory_id' => $directory->id],$systemFile);
        }
        $directory->storageAccess()->createMany($storageAccess);
    }
}