<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Helpers\UploadHelper;
use Helpers\SecurityHelper;
use Helpers\StorageHelper;
use Services\MeetingService;
use Services\DirectoryService;
use Services\FileService;
use Services\ChatService;
use Storages\StorageFactory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Services\CommitteeService;
use Validator;

class UploadController extends Controller
{
    private $chatService;
    private $storage;
    private $meetingService;
    private $fileService;
    private $storageHelper;
    private $directoryService;
    private $securityHelper;
    private $committeeService;
    public function __construct(
        ChatService $chatService,
        MeetingService $meetingService,
        StorageHelper $storageHelper,
        DirectoryService $directoryService,
        FileService $fileService,
        CommitteeService $committeeService,
        SecurityHelper $securityHelper
    )
    {
        $this->chatService = $chatService;
        $this->meetingService = $meetingService;
        $this->storageHelper = $storageHelper;
        $this->storage = StorageFactory::createStorage();
        $this->directoryService = $directoryService;
        $this->fileService = $fileService;
        $this->committeeService = $committeeService;
        $this->securityHelper = $securityHelper;
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,jpg,png|max:' . config('attachment.profile_image_size')
        ]);
       
        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }

        return response()->json(["url" => UploadHelper::uploadFile($request, 'file')], 200);

    }

    public function uploadPresentationNotes(Request $request)
    {

        return response()->json(["url" => UploadHelper::uploadPresentationNotes($request, 'file')], 200);

    }

    public function uploadFiles(Request $request)
    {
        $data = $request->all();
        $user  =  $this->securityHelper->getCurrentUser();

        if ($data['files']) {
            $validator = Validator::make($request->all(), [
                'files.*' => 'required|mimes:jpeg,jpg,png,pdf,txt,doc,docx,odt,xls,xlsx,ppt,pptx,avi,mov,mp4,wmv,rtf|max:' . config('attachment.file_size'),
            ], UploadHelper::message());
            if ($validator->fails()) {
                return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
            }
            $result = [];
            $directory = null;
            $directory_path = '/uploads/attachments';
            $directory_id = null;

            $path = $directory_path;
            $urls  = $this->storage->uploadFiles($request,$path,'files');




            return response()->json(["urls" => $urls], 200);
        } else {
            return response()->json(['error' => 'There are no files'], 400);
        }

    }

    public function getFile(Request $request)
    {
        $data = $request->all();
        if (isset($data['path'])) {
            return response()->file($data['path']);
        } else {
            return response()->json(['error' => 'There are no files'], 400);
        }
    }


    public function convertPdfToImagesURL(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:pdf|max:' . config('attachment.file_size'),
        ],UploadHelper::fileMessage());

        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }

        $pdfFilePath = UploadHelper::uploadFile($request, 'file');
        $fileName = pathinfo(public_path() . '/' . $pdfFilePath, PATHINFO_FILENAME);
        $folderName = '/uploads/convertedImages/'.$fileName ;
        $path = public_path() . $folderName;
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }
        $imagesPaths = UploadHelper::convertPdfToImages(public_path() . '/' . $pdfFilePath, $path, $fileName, true,url($folderName));
        return response()->json(["urls" => $imagesPaths], 200);

    }

    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:pdf|max:' . config('attachment.file_size')
        ]);
       
        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }

        return response()->json(["url" => UploadHelper::uploadFile($request, 'file')], 200);

    }

    public function uploadFileToChat(Request $request){
        $file = Input::file('file');
        $fileName = preg_replace('/\s+/', '', $file->getClientOriginalName());
        $response = $this->chatService->uploadAttachment($file,$fileName);
        if($response['is_success']){
            return response()->json($response['response'], 200 );
        }
        return response()->json($response['response'], $response['resopnse_code'] );

    }

    public function uploadDocument(Request $request)
    {
        $data = $request->all();
        $file = $request->file('file');
        $directory_path = "/uploads/documents";

        if(isset($file) && $file ->getClientOriginalExtension() == 'docx'){
            $validator = Validator::make($request->all(), [
                'file' => 'max:' . config('attachment.file_size')
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:jpeg,jpg,png,doc,docx,odt,xls,xlsx,ppt,pptx,pdf|max:' . config('attachment.file_size')
            ]);
        }

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }

        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);
    }
    public function uploadDisclosure(Request $request)
    {
        $file = $request->file('file');
        $directory_path = "/uploads/disclosures";

        if(isset($file) && $file ->getClientOriginalExtension() == 'docx'){
            $validator = Validator::make($request->all(), [
                'file' => 'max:' . config('attachment.file_size')
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:jpeg,jpg,png,doc,docx,odt,xls,xlsx,ppt,pptx,pdf|max:' . config('attachment.file_size')
            ]);
        }

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }

        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);
    }

    public function uploadOrganizationLogo(Request $request)
    {
        $directory_path = "/uploads/organizations";
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,jpg,png|max:' . config('attachment.profile_image_size')
        ]);
       
        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }
        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);
    }

    public function uploadProfileImage(Request $request){
        $directory_path = "/uploads/users";
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,jpg,png|max:' . config('attachment.profile_image_size')
        ]);
       
        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }

        return response()->json(["url" =>  $this->storage->uploadFile($request, $directory_path)], 200);
    }

    public function uploadMomTemplateLogo(Request $request)
    {
        $directory_path = '/uploads/mom-templates';
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,jpg,png|max:' . config('attachment.profile_image_size')
        ]);
       
        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }

        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);
    }
    
    public function uploadCircularDecisionsAttachment(Request $request){
        $data = $request->all();
        if (isset($data['files'])) {
            $validator = Validator::make($request->all(), [
                'files.*' => 'required|mimes:jpeg,jpg,png,pdf,txt,doc,docx,odt,xls,xlsx,ppt,pptx,avi,mov,mp4,wmv,rtf|max:' . config('attachment.file_size'),
            ], UploadHelper::message());
            if ($validator->fails()) {
                return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
            }
            $path = '/uploads/circular-decisions';
            $urls  = $this->storage->uploadFiles($request,$path,'files');
            return response()->json(["urls" => $urls], 200);
        } else {
            return response()->json(['error' => 'There are no files'], 400);
        }
    }

    public function uploadChatLogo(Request $request){
        $directory_path = "/uploads/chat";
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,jpg,png|max:' . config('attachment.profile_image_size')
        ]);
        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }
        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);
    }
    
    public function uploadSystemPdf(Request $request)
    {
        $directory_path = "/uploads/pdf";
        $error = $this->validatePdfFile($request);
        if($error){
            return response()->json(["error" => $error], 400);
        }
        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);       
    }
    
    public function uploadMomPdf(Request $request)
    {
        $directory_path = "/uploads/mom_pdf";
        $error = $this->validatePdfFile($request);
        if($error){
            return response()->json(["error" => $error], 400);
        }
        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);
    }

    private function validatePdfFile(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:pdf|max:' . config('attachment.file_size')
        ]);
        return $validator->fails()? $validator->errors()->all() : null;
    }

    public function uploadApproval(Request $request)
    {
        $data = $request->all();
        $file = $request->file('file');
        $directory_path = "/uploads/approvals";

        if(isset($file) && $file ->getClientOriginalExtension() == 'docx'){
            $validator = Validator::make($request->all(), [
                'file' => 'max:' . config('attachment.file_size')
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:jpeg,jpg,png,doc,docx,odt,xls,xlsx,ppt,pptx,pdf|max:' . config('attachment.file_size')
            ]);
        }

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }

        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);
    }

    public function uploadEvidenceDocument(Request $request)
    {
        $file = $request->file('file');
        $directory_path = "/uploads/evidence-document";

        if(isset($file) && $file ->getClientOriginalExtension() == 'docx'){
            $validator = Validator::make($request->all(), [
                'file' => 'max:' . config('attachment.file_size')
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:jpeg,jpg,png,doc,docx,odt,xls,xlsx,ppt,pptx,pdf|max:' . config('attachment.file_size')
            ]);
        }

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }

        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);
    }
    public function uploadBlockDocument(Request $request)
    {
        $file = $request->file('file');
        $directory_path = "/uploads/block-document";

        if(isset($file) && $file ->getClientOriginalExtension() == 'docx'){
            $validator = Validator::make($request->all(), [
                'file' => 'max:' . config('attachment.file_size')
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:jpeg,jpg,png,doc,docx,odt,xls,xlsx,ppt,pptx,pdf|max:' . config('attachment.file_size')
            ]);
        }

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }

        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);
    }

    public function uploadCommitteeDocument(Request $request)
    {
        $file = $request->file('file');
        $committeeId = $request->get('id');
        $committee = $this->committeeService->getById($committeeId);
        $directory_path = '';
        if (!isset($committee['directory_id'])) {
            $this->committeeService->createCommitteeDirectory($committeeId);
            $committee = $this->committeeService->getById($committeeId);
        }
        $directory = $this->directoryService->getById($committee['directory_id']);
        $directory_path = $directory->directory_path;

        if (isset($file) && $file->getClientOriginalExtension() == 'docx') {
            $validator = Validator::make($request->all(), [
                'file' => 'max:' . config('attachment.file_size')
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:jpeg,jpg,png,doc,docx,odt,xls,xlsx,ppt,pptx,pdf|max:' . config('attachment.file_size')
            ]);
        }

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->all()], 400);
        }

        return response()->json(["url" => $this->storage->uploadFile($request, $directory_path)], 200);
    }
}
