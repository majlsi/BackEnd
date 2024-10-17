<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\DocumentAnnotationService;
use Services\DocumentService;
use Services\DocumentUserService;
use Services\NotificationService;
use Models\DocumentAnnotation;
use Helpers\SecurityHelper;
use Helpers\DocumentAnnotationHelper;
use Helpers\NotificationHelper;
use Validator;

class DocumentAnnotationController extends Controller {

    private $documentAnnotationService;
    private $securityHelper;
    private $documentAnnotationHelper;
    private $documentService;
    private $documentUserService;
    private $notificationHelper;
    private $notificationService;

    public function __construct(DocumentAnnotationService $documentAnnotationService, SecurityHelper $securityHelper,
                        DocumentAnnotationHelper $documentAnnotationHelper, DocumentService $documentService,
                        DocumentUserService $documentUserService, NotificationHelper $notificationHelper,
                        NotificationService $notificationService) {
        $this->documentAnnotationService = $documentAnnotationService;
        $this->documentService = $documentService;
        $this->securityHelper = $securityHelper;
        $this->documentAnnotationHelper = $documentAnnotationHelper;
        $this->documentUserService = $documentUserService;
        $this->notificationHelper = $notificationHelper;
        $this->notificationService = $notificationService;
    }

    public function index(int $documentId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $document = $this->documentService->getById($documentId);
        if( $document && in_array($user->id,array_column($document->reviewres->toArray(), 'id'))){
            return response()->json($this->documentAnnotationService->getDocumentAnnotationByDocumentId($documentId,$user->id),200); 
        } else {
            return response()->json(['error' => 'You don\'t have access'], 400);
        }
    }

    public function store(Request $request,int $documentId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $document = $this->documentService->getById($documentId);
        $documentUser = $this->documentUserService->getDocumentUserByDocumentAndUserId($documentId,$user->id);

        if($document && $document->document_status_id != config('documentStatuses.complete') && in_array($user->id,array_column($document->reviewres->toArray(), 'id'))
            && in_array($documentUser->document_status_id,[config('documentStatuses.new'),config('documentStatuses.inProgress')]) ){
            $documentAnnotationData = $this->documentAnnotationHelper->prepareDocumentAnnotationData($data,$user, $document, true);
            $validator = Validator::make($documentAnnotationData, DocumentAnnotation::rules('save'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $documentAnnotation = $this->documentAnnotationService->create($documentAnnotationData);
            //update document status
            if($documentUser->document_status_id == config('documentStatuses.new')){
                $this->documentUserService->update($documentUser->id, ['document_status_id' => config('documentStatuses.inProgress')]);
            }
            if($document->document_status_id == config('documentStatuses.new')){
                $this->documentService->update($documentId, ['document_status_id' => config('documentStatuses.inProgress')]);
            }
            // send notification
            $notificationData = $this->notificationHelper->prepareNotificationDataForDocumentation($document,$user,config('documentNotification.addAnnotation'));
            $this->notificationService->sendNotification($notificationData);
            return response()->json(['message' => 'Document Annotation created successfully', 'message_ar' => 'تم إضافة ملاحظة بنجاح'],200); 
        } else {
            return response()->json(['error' => 'You can\'t add document annotaion', 'error_ar' => 'لا يمكن إضافه ملاحظة'], 400);
        }
    }

    public function update(Request $request,int $documentId,int $id)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $documentAnnotation = $this->documentAnnotationService->getById($id);
        $document = $documentAnnotation->documentUser->document;
        $documentUser = $this->documentUserService->getDocumentUserByDocumentAndUserId($documentId,$user->id);

        if($documentAnnotation && $user->id == $documentAnnotation->documentUser->user_id &&
            in_array($document->document_status_id, [config('documentStatuses.new'),config('documentStatuses.inProgress')])
            && in_array($documentUser->document_status_id,[config('documentStatuses.new'),config('documentStatuses.inProgress')])){
            $documentAnnotationData = $this->documentAnnotationHelper->prepareDocumentAnnotationData($data,$user, $document, false);
            $validator = Validator::make($documentAnnotationData, DocumentAnnotation::rules('update'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $documentAnnotation = $this->documentAnnotationService->update($id,$documentAnnotationData);
            // send notification
            $notificationData = $this->notificationHelper->prepareNotificationDataForDocumentation($document,$user,config('documentNotification.editAnnotation'));
            $this->notificationService->sendNotification($notificationData);
            return response()->json(['message' => 'Document Annotation updated successfully', 'message_ar' => 'تم تعديل الملاحظة بنجاح'],200);     
        } else {
            return response()->json(['error' => 'You don\'t have access'], 400);
        }
    }

    public function destroy(int $documentId,int $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $documentAnnotation = $this->documentAnnotationService->getById($id);
        $document = $documentAnnotation->documentUser->document;
        $documentUser = $this->documentUserService->getDocumentUserByDocumentAndUserId($documentId,$user->id);

        if($documentAnnotation && $user->id == $documentAnnotation->documentUser->user_id &&
            in_array($document->document_status_id, [config('documentStatuses.new'),config('documentStatuses.inProgress')])
            && in_array($documentUser->document_status_id,[config('documentStatuses.new'),config('documentStatuses.inProgress')])){
            $this->documentAnnotationService->delete($id);
            // send notification
            $notificationData = $this->notificationHelper->prepareNotificationDataForDocumentation($document,$user,config('documentNotification.deleteAnnotation'),['annotation_id' => $id]);
            $this->notificationService->sendNotification($notificationData);
            return response()->json(['message' => 'Document annotation deleted successfully', 'message_ar' => 'تم حذف الملاحظة بنجاح'],200); 
        } else {
            return response()->json(['error' => 'You don\'t have access'], 400);
        }
    }
}