<?php

use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\RequestController;

Route::group(
    ['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {

        Route::post("requests/add-committee", [RequestController::class, 'getCommitteeRequestsPagedList']);
        Route::post("requests/add-committee/filtered-list", [RequestController::class, 'getCommitteeRequestsPagedList'])->middleware('checkAuthorization:' . config('rights.committeeRequests'));
        Route::post("requests/update-committee/filtered-list", [RequestController::class, 'getCommitteeUpdateRequestsPagedList'])->middleware('checkAuthorization:' . config('rights.committeeRequests'));
        Route::post("requests/add-member-committee", [RequestController::class, 'getAddMemberCommitteeRequestsPagedList']);
        Route::post("requests/add-member-committee/filtered-list", [RequestController::class, 'getAddMemberCommitteeRequestsPagedList']);
        Route::post("requests/delete-member-committee/filtered-list", [RequestController::class, 'getDeleteMemberCommitteeRequestsPagedList']);
        Route::post("requests/delete-file/filtered-list", [RequestController::class, 'getDeleteFileRequestsPagedList']);
        Route::post("requests/unfreeze-committee-member/filtered-list", [RequestController::class, 'getUnFreezeMemberRequestsPagedList']);
        Route::post("requests/filtered-list", [RequestController::class, 'getPagedList']);
        Route::post("committee-requests/filtered-list", [RequestController::class, 'getPagedPendingCommitteesList']);
        Route::post("requests", [RequestController::class, 'store']);
        Route::put("requests/{id}", [RequestController::class, 'update']);
        Route::get("requests/{id}", [RequestController::class, 'show']);
        Route::delete("requests/{id}", [RequestController::class, 'destroy']);
        //get Variable from enviroment
        Route::get('/get-addCommitteeFeature-variable', [RequestController::class, 'getAddCommitteeFeatureVariable']);
        Route::get('/get-addUserFeature-variable', [RequestController::class, 'getAddUserFeatureVariable']);
        Route::get('/get-additional-user-fields-variable', [RequestController::class, 'getAdditionalUserFieldsVariable']);
        Route::get('/get-delete-file-variable', [RequestController::class, 'getDeleteFileFeatureVariable']);
        Route::get('/get-remove-committee-code-variable', [RequestController::class, 'getRemoveCommitteeCodeFeatureVariable']);

        //! create delete request 
        Route::post("requests/delete-user/{id}", [RequestController::class, 'createDeleteUserRequest']);

        Route::post('request/delete-file', [RequestController::class, 'deleteFileRequest']);
        Route::post('request/unFreeze-committee', [RequestController::class, 'unFreezeCommitteesRequest']);

        Route::get('/get-deleteUserFeature-variable', [RequestController::class, 'getDeleteCommitteeFeatureVariable']);

        Route::get('requests/{committeeId}/users', [RequestController::class, 'getCommitteeUsersList']);

        Route::post("requests/add-member-committee/{id}/accept", [RequestController::class, 'acceptAddUserRequest']);

        Route::post("requests/add-member-committee/{id}/reject", [RequestController::class, 'rejectAddUserRequest']);

        Route::post("requests/delete-member-committee/{id}/accept", [RequestController::class, 'acceptDeleteUserRequest']);

        Route::post("requests/delete-member-committee/{id}/reject", [RequestController::class, 'rejectDeleteUserRequest']);


        Route::post('requests/delete-file/{id}/accept', [RequestController::class, 'acceptDeleteFileRequest']);
        Route::post('requests/delete-file/{id}/reject', [RequestController::class, 'rejectDeleteFileRequest']);

        Route::get('unfreeze-members-requests/{id}', [RequestController::class, 'showUnFreezeMemberRequest']);
        Route::post('requests/{id}/reject', [RequestController::class, 'rejectRequest']);
        Route::post('requests/{id}/create-committee', [RequestController::class, 'AcceptAddCommitteeRequest']);
        Route::post('requests/add-committee-members', [RequestController::class, 'addCommitteeMembersRequest']);

        Route::get('/get-work-feature-variable', [CommitteeController::class, 'getWorkDoneByCommitteeFeatureVariable']);

        Route::post('requests/update-committee/{id}/accept', [RequestController::class, 'acceptUpdateCommitteeRequest']);
        
        Route::get('export-add-committees-requests', [RequestController::class, 'exportAddCommitteeRequests']);
        Route::get('export-add-member-to-committees-requests', [RequestController::class, 'exportAddMemberToCommitteeRequests']);
        Route::get('export-delete-member-from-committees-requests', [RequestController::class, 'exportDeleteMemberFromCommitteeRequests']);
        Route::get('export-delete-documents-requests', [RequestController::class, 'exportDeleteDocumentsRequests']);
        Route::get('export-unfreeze-members-requests', [RequestController::class, 'exportUnfreezeMembersRequests']);
    }
);
