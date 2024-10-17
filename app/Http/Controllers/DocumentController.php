<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\DocumentService;
use Services\DocumentAnnotationService;
use Services\DocumentUserService;
use Services\NotificationService;
use Models\Document;
use Helpers\SecurityHelper;
use Helpers\DocumentHelper;
use Helpers\NotificationHelper;
use Jobs\NewDocumentCreatedEmail;
use Validator;
use TCPDF;
use Carbon\Carbon;

class DocumentController extends Controller {

    private $documentService;
    private $securityHelper;
    private $documentHelper;
    private $documentAnnotationService;
    private $documentUserService;
    private $notificationHelper;
    private $notificationService;

    public function __construct(DocumentService $documentService, SecurityHelper $securityHelper,
                                DocumentHelper $documentHelper, DocumentAnnotationService $documentAnnotationService,
                                DocumentUserService $documentUserService, NotificationHelper $notificationHelper,
                                NotificationService $notificationService) {
        $this->documentService = $documentService;
        $this->securityHelper = $securityHelper;
        $this->documentHelper = $documentHelper;
        $this->documentAnnotationService = $documentAnnotationService;
        $this->documentUserService = $documentUserService;
        $this->notificationHelper = $notificationHelper;
        $this->notificationService = $notificationService;
    }


    public function show(int $id){
        $user = $this->securityHelper->getCurrentUser();
        $document = $this->documentService->getDocumentDetails($id,$user->id);
        if($document){
            $document->document_users_ids = [];
            $document_users_ids = array_column($document->reviewres->toArray(), 'id');
            $index = array_search($document->added_by,$document_users_ids);
            if ($index >= 0) {
                unset($document_users_ids[$index]);
            }
            $document->document_users_ids = array_merge($document->document_users_ids,$document_users_ids);
            return response()->json($document, 200);
        } else {
            return response()->json(['error' => 'Document not found', 'error_ar' => 'هذا المستند غير موجود'], 404);
        }
    }

    public function store(Request $request){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if($user->organization_id){
            $documentData = $this->documentHelper->prepareDocumentData($data, $user, true);
            $validator = Validator::make($documentData, Document::rules('save'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $document = $this->documentService->create($documentData);
            $document = $this->documentService->getDocumentDataWithCanSendNotificationFlag($document->id);
            if($document->can_send_notification){
                $notificationData = $this->notificationHelper->prepareNotificationDataForDocumentation($document,$document->creator,config('documentNotification.startDocument'));
                $this->notificationService->sendNotification($notificationData);
                NewDocumentCreatedEmail::dispatch($document,null);
            }
            return response()->json(['message' => 'Document created successfully', 'message_ar' => 'تم إضافة المستند بنجاح'],200); 
        } else {
            return response()->json(['error' => 'You can\'t add document'], 400);
        }
    }

    public function update(Request $request, int $id){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $document = $this->documentService->getById($id);
        $newReviewersIds  = [];
        if(isset($data['document_users_ids'])){
            $documentUsersIds = $data['document_users_ids'];

            $documentUsersIds[] = $document->added_by;
            // update document reviewers
            $oldReviewersIds = array_column($document->reviewres->toArray(), 'id');
            $deletedReviewersIds = array_diff($oldReviewersIds,$documentUsersIds);
            $newReviewersIds = array_diff($documentUsersIds,$oldReviewersIds);
        }
        if($document && $user->id == $document->added_by){
            if(in_array($document->document_status_id,[config('documentStatuses.new'),config('documentStatuses.inProgress')])){
                $documentData = $this->documentHelper->prepareDocumentData($data, $user, false);
                $errorInDocumentUsers = $this->documentService->CheckErrorInDeleteDocumentUsers($document,$documentData);
                if ($errorInDocumentUsers) {
                    return response()->json(['error' => 'You can\'t delete document users', 'error_ar' => 'لا يمكن حذف أعضاء المستند'], 400);
                }
                $validator = Validator::make($documentData, Document::rules('update'));
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()], 400);
                }
                $document = $this->documentService->update($id,$documentData);
                $document = $this->documentService->getDocumentDataWithCanSendNotificationFlag($document->id);
                if($document->can_send_notification){
                    $notificationData = $this->notificationHelper->prepareNotificationDataForDocumentation($document,$document->creator,config('documentNotification.editDocument'));
                    $this->notificationService->sendNotification($notificationData);
                    if(!empty($newReviewersIds)){
                        NewDocumentCreatedEmail::dispatch($document,$newReviewersIds);
                    }
                }
                return response()->json(['message' => 'Document updated successfully', 'message_ar' => 'تم تعديل المستند بنجاح'],200);
            } else {
                return response()->json(['error' => 'You can\'t update this document', 'error_ar' => 'لا يمكن تعديل هذا المستند'], 400);
            }
             
        } else {
            return response()->json(['error' => 'You don\'t have access','error_ar' => 'ليس لديك صلاحيات'], 400);
        }
    }

    public function destroy(int $id){
        $document = $this->documentService->getById($id);
        $user = $this->securityHelper->getCurrentUser();
        if($document && $user->id == $document->added_by){
            // if ($document->document_status_id != config('documentStatuses.new')) {
            //     return response()->json(['error' => 'You can\'t delete this document','error_ar' => 'لا يمكن حذف هذا المستند'], 400);
            // }
            $document = $this->documentService->delete($id);
            return response()->json(['message' => 'Document deleted successfully', 'message_ar' => 'تم حذف المستند بنجاح'],200); 
        } else {
            return response()->json(['error' => 'You don\'t have access'], 400);
        }   
    }

    public function getpagedList(Request $request){
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->documentService->getPagedList($filter,$user->id,$user->organization_id),200);
    }

    public function completeDocument(int $id){
        $user = $this->securityHelper->getCurrentUser();
        $document = $this->documentService->getById($id);

        if($document && in_array($user->id,array_column($document->reviewres->toArray(), 'id')) && 
            in_array($document->document_status_id, [config('documentStatuses.new'),config('documentStatuses.inProgress')])){
                $documentUser = $this->documentUserService->getDocumentUserByDocumentAndUserId($document->id,$user->id);
                if ( in_array($documentUser->document_status_id,[config('documentStatuses.new'),config('documentStatuses.inProgress')])) {
                    // update document user status
                    $this->documentUserService->update($documentUser->id, ['document_status_id' => config('documentStatuses.complete')]);
                    if ($user->id == $document->added_by){
                        // create directory for docuemnt
                        if($document->creator->organization->enable_meeting_archiving){
                            $this->documentService->createDirectoryForDocument($document);
                        }
                        $this->documentService->update($id,['document_status_id' => config('documentStatuses.complete')]);
                    }
                    // send notification
                    $notificationData = $this->notificationHelper->prepareNotificationDataForDocumentation($document,$user,config('documentNotification.completeReview'));
                    $this->notificationService->sendNotification($notificationData);
                    return response()->json(['message' => 'Document review completed successfully', 'message_ar' => 'تم إنتهاء مراجعة المستند بنجاح'],200); 
                } else {
                    return response()->json(['error' => 'You don\'t have access','error_ar' => 'ليس لديك صلاحيات'], 400);
                }
        } else {
            return response()->json(['error' => 'You don\'t have access','error_ar' => 'ليس لديك صلاحيات'], 400);
        }
    }

    public function getDocumentSlides(int $id){
        $document = $this->documentService->getById($id);
        if ($document) {
            $data = $this->documentService->getDocumentSlides($document);
            return response()->json($data, 200);
        }
        return response()->json(['error' => 'Document not found', 'error_ar' => 'المستند غير موجود'], 404);

    }

    public function downloadDocument(int $id)
    {
        $baseUrl = config('app.url');
        $document = $this->documentService->getById($id);
        $user = $this->securityHelper->getCurrentUser();
        if ($document) {
            $documentSlides = $this->documentService->getDocumentSlides($document);
            $annotations = $this->documentAnnotationService->getDocumentAnnotationByDocumentId($document->id,$user->id)->groupBy('page_number')->toArray();
            //$highlights = $data['highlights'];
            $showNotes = true;
            $this->createPDF($baseUrl,$document,$documentSlides,$annotations,$showNotes);
        }
        return response()->json(['error' => 'Document not found', 'error_ar' => 'المستند غير موجود'], 404);
    }

    private function createPdf($baseUrl,$document,$documentSlides,$annotations,$showNotes){
        $colorsArray = config('documentAnnotations.colors');
        $documentUsersIds = [];
        foreach ($annotations as $key => $annotationsPerPage) {
            $documentUsersIds = array_unique(array_merge($documentUsersIds, array_column($annotationsPerPage,'document_user_id')));
        }

        $img_file = $baseUrl . $documentSlides['document_images'][0];
        list($width, $height) = getimagesize($img_file);
        $orientation = $width > $height ? 'L' : 'P';
        if ($width > $height) {
            $pageWidth = 279;
            $pageHeight = 210;
        } else {
            $pageWidth = 210;
            $pageHeight = 279;
        }
        // create new PDF document
        $pdf = new TCPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle($document->document_name);
        $pdf->SetSubject($document->document_name);
        // set default monospaced font 
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // set font
        $pdf->SetFont('times', '', 18);
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        // remove default footer
        $pdf->setPrintFooter(false);
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // get the current page break margin
        $bMargin = $pdf->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $pdf->getAutoPageBreak();
        $pdf->setPrintHeader(false);

        foreach ($documentSlides['document_images'] as $key => $value) {

            $img_file = $baseUrl . $value;
            list($width, $height) = getimagesize($img_file);
            $pdf->AddPage();

            // disable auto-page-break
            $pdf->SetAutoPageBreak(false, 0);

            $pdf->Image($img_file, 0, 0, $pageWidth, $pageHeight, '', '', '', false, 300, '', false, false, 0);
            $pdf->SetDrawColor(0, 0, 50, 0);
            $pdf->SetAlpha(0.2);
            $pdf->SetFillColor(0, 0, 100, 0);

            //Highlight
            // if (isset($highlights[$key + 1])) {
            //     foreach ($highlights[$key + 1] as $value) {
            //         $pdf->Rect(
            //             (($value['x_upper_left'] * $pageWidth) / $width) * PDF_IMAGE_SCALE_RATIO,
            //             (($value['y_upper_left'] * $pageHeight) / $height) * PDF_IMAGE_SCALE_RATIO,
            //             (($value['width'] * $pageWidth) / $width) * PDF_IMAGE_SCALE_RATIO,
            //             (($value['height'] * $pageHeight) / $height) * PDF_IMAGE_SCALE_RATIO, 'DF');
            //     }
            // }

            // text annotation
            if (isset($annotations[$key + 1]) && $showNotes) {
                foreach ($annotations[$key + 1] as $value) {
                    $index = array_search($value['document_user_id'],$documentUsersIds);
                    $index = $index? $index : 0;
                    $title = (isset($value['name_ar'])? $value['name_ar'] : $value['name']) . ' (' . Carbon::parse($value['creation_date'])->format('d F Y, g:i A') . '):';
                    $pdf->Annotation(
                        (($value['x_upper_left'] * $pageWidth) / $width) * 0.8 * PDF_IMAGE_SCALE_RATIO,
                        (($value['y_upper_left'] * $pageHeight) / $height) * 0.8 * PDF_IMAGE_SCALE_RATIO
                        , 10, 10, $value['annotation_text'],
                        array('Subtype' => 'Text', 'T' => $title,'C' => $colorsArray[$index]));
                }

            }
        }
        $pdf->SetAlpha(1);
        // restore auto-page-break status
        $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $pdf->setPageMark();

        //Close and output PDF document
        $pdf->Output($document->name, 'D');
    
    }
    
    public function updateStatusOfDocumentToDelay(){
        $this->documentService->updateStatusOfDocumentToDelay();
    }

    public function sendNotificationWhenReviewDocumentTimeStart(){
        $documents = $this->documentService->getStartedDocuments();
        foreach ($documents as $key => $document) {
            $notificationData = $this->notificationHelper->prepareNotificationDataForDocumentation($document,$document->creator,config('documentNotification.startDocument'));
            $this->notificationService->sendNotification($notificationData);
            NewDocumentCreatedEmail::dispatch($document,null);
        }
    }
}
