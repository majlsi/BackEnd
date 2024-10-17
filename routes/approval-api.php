<?php
use App\Http\Controllers\ApprovalController;

Route::group(
    ['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
        Route::group(['prefix' => 'admin'], function () {

            Route::post("approvals/filtered-list", [ApprovalController::class, 'getPagedList']);
            Route::post("approvals", [ApprovalController::class, 'store']);
            Route::put("approvals/{approval:id}", [ApprovalController::class, 'update']);
            Route::get("approvals/{approval:id}", [ApprovalController::class, 'show']);
            Route::delete("approvals/{approval:id}", [ApprovalController::class, 'destroy']);
            Route::get('approvals/{document_id}/slides', 'ApprovalController@getApprovalDocumentSlides');
            Route::get('approvals/{approval:id}/signature-user-login', [ApprovalController::class, 'loginToDigitalSignature']);
            Route::get('approvals/{approval:id}/download-approval-pdf', [ApprovalController::class, 'downloadApprovalPdf']);
            Route::put('approval-members', 'ApprovalController@updateApprovalMembers');

        });
    }
);