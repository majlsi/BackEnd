<?php

namespace App\Http\Controllers;

use Helpers\SecurityHelper;
use Helpers\SignatureHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Models\Approval;
use Services\ApprovalService;

class ApprovalController extends Controller
{
    private ApprovalService $approvalService;
    private $securityHelper;
    private $signatureHelper;

    public function __construct(
        ApprovalService $approvalService,
        SecurityHelper $securityHelper,
        SignatureHelper $signatureHelper,
    ) {
        $this->approvalService = $approvalService;
        $this->securityHelper = $securityHelper;
        $this->signatureHelper = $signatureHelper;
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        return response()->json($this->approvalService->getPagedList($filter), 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $errors = [];
        $validator = Validator::make($data, Approval::rules('save'), Approval::messages('save'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        $approval =  $this->approvalService->create($data);
        if(isset($approval)){
            return response($approval, 200);
        } else {
            return response([
                'error' => [
                    [
                        'message_ar' => 'فشل إضافة موافقة جديدة',
                        'message' => 'Failed to add new Approval'
                    ]
                ]
            ], 400);
        }
    }

    public function update(Request $request, Approval $approval)
    {
        $data = $request->all();
        $errors = [];
        $validator = Validator::make($data, Approval::rules('update'), Approval::messages('update'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        $approval =  $this->approvalService->update($approval->id, $data);
        if (isset($approval)) {
            return response(["Results" => $approval, "message" => [
                'message_ar' => 'تم تعديل الموافقة',
                'message' => 'Edit Approval successfully'
            ]], 200);
        } else {
            return response([
                'error' => [
                    [
                        'error_ar' => 'فشل تعديل موافقة',
                        'error' => 'Failed to edit Approval'
                    ]
                ]
            ], 400);
        }
    }

    public function show(Approval $approval)
    {
        return response($this->approvalService->getApprovalData($approval), 200);
    }

    public function destroy(Approval $approval)
    {
        $this->approvalService->delete($approval->id);
        return response(["success" => true], 200);
    }

    public function getApprovalDocumentSlides (int $id){
        $document = $this->approvalService->getById($id);
        if ($document) {
            $data = $this->approvalService->getApprovalDocumentSlides($document);
            return response()->json($data, 200);
        }
        return response()->json(['error' => 'Document not found', 'error_ar' => 'المستند غير موجود'], 404);

    }

    public function updateApprovalMembers(Request $request) {
        $data = $request->all();
        $updatedData = $this->approvalService->updateApprovalSignaturesPlaces($data);
        return response()->json($updatedData, 200);
    }

    public function loginToDigitalSignature($approvalId) {
        $approval = $this->approvalService->getById($approvalId);
        if($approval) {
            $userToken = $this->approvalService->loginUserToDigitalSignature($approval);
            if($userToken) {
                return response()->json($userToken, 200);
            }
        }
        
        return response()->json(null, 400);
    }

    public function downloadApprovalPdf(Request $request, int $approvalId)
    {
        $approval = $this->approvalService->getById($approvalId);

        if ($approval && $approval->signature_document_id) {
            $user = $this->securityHelper->getCurrentUser();
            $file = $this->signatureHelper->getDocumentBinary(
                $user->organization,
                $approval->signature_document_id,
                $user->email
            );

            return $file;
        }
        return response()->json([
            'error' => [
                [
                    'error_ar' => 'فشل تحميل موافقة',
                    'error' => 'Failed to download approval'
                ]
            ]
        ], 400);
    }

}
