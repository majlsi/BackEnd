<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Helpers\SecurityHelper;
use Helpers\SignatureHelper;
use Illuminate\Http\Request;
use Services\UserService;
use Services\VoteService;

class SignatureController extends Controller
{

    private $signatureHelper, $securityHelper, $userService, $voteService;

    public function __construct(SignatureHelper $signatureHelper,
        SecurityHelper $securityHelper, UserService $userService, VoteService $voteService) {
        $this->signatureHelper = $signatureHelper;
        $this->securityHelper = $securityHelper;
        $this->userService = $userService;
        $this->voteService = $voteService;
    }

    public function getUserSignatures(Request $request, $decesionId)
    {
        $data = $request->all();
        $signatures = [];
        $user = $this->securityHelper->getCurrentUser();
        $decesion = $this->voteService->getById($decesionId);
        if ($decesion) {
            $signatures = $this->signatureHelper->getUserSignatures($user->organization, $user->email, $decesion->document_id);
            return response()->json($signatures, 200);
        }

        return response()->json(['error' => 'You can\'t access'], 400);
    }

    public function sendDocumentSignatureCode(Request $request, $decesionId, $lang)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $decesion = $this->voteService->getById($decesionId);
        if ($decesion) {
            $sent = $this->signatureHelper->sendDocumentSignatureCode($user->organization, $user->email, $decesion->document_id, $data, $lang);
            if ($sent == true) {
                return response()->json([], 200);
            } else {
                return response()->json([], 400);
            }

        }

        return response()->json(['error' => 'You can\'t access'], 400);
    }

    public function verifyCode(Request $request, $decesionId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $decesion = $this->voteService->getById($decesionId);
        if ($decesion) {
            $result = $this->signatureHelper->verifyCode($user->organization, $user->email, $decesion->document_id, $data);
            return response()->json($result, 200);
        }

        return response()->json(['error' => 'You can\'t access'], 400);
    }

    public function getDocument(Request $request, $decesionId, $lang)
    {
        $user = $this->securityHelper->getCurrentUser();
        $decesion = $this->voteService->getById($decesionId);
        if ($decesion) {
            $document = $this->signatureHelper->getDocument($user->organization, $user->email, $decesion->document_id, $lang);
            return response()->json($document, 200);
        }

        return response()->json(['error' => 'You can\'t access'], 400);
    }

    public function saveSignature(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $sent = $this->signatureHelper->saveSignature($user->organization, $user->email, $data);
        if ($sent == true) {
            return response()->json([], 200);
        } else {
            return response()->json([], 400);
        }

        return response()->json(['error' => 'You can\'t access'], 400);
    }

    public function reject(Request $request, $decesionId, $documentFieldId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $decesion = $this->voteService->getById($decesionId);
        if ($decesion) {
            $reject = $this->signatureHelper->reject($user->organization, $user->email, $data, $documentFieldId,$decesion->document_id);
            if ($reject == true) {
                return response()->json([], 200);
            } else {
                return response()->json([], 400);
            }
        }

        return response()->json(['error' => 'You can\'t access'], 400);
    }

    public function sign(Request $request, $decesionId, $documentFieldId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $decesion = $this->voteService->getById($decesionId);
        if ($decesion) {
            $reject = $this->signatureHelper->sign($user->organization, $user->email, $data, $documentFieldId,$decesion->document_id);
            if ($reject == true) {
                return response()->json([], 200);
            } else {
                return response()->json([], 400);
            }
        }

        return response()->json(['error' => 'You can\'t access'], 400);
    }

}
