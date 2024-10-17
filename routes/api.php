<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::group(
    ['prefix' => 'v1', 'middleware' => ['throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {

        //Route::post('admin/rooms/pdf', 'AttachmentController@createPdf');
        //sign in & register
        Route::post('authenticate', function () {
            if (!config('customSetting.ldapIntegration')) {
                return app('App\Http\Controllers\Auth\AuthenticateController')->authenticate(request());
            } else {
                return app('App\Http\Controllers\Auth\LdapAuthenticationController')->authenticate(request());
            }
        });
        Route::post('authenticate/invalidate', 'Auth\AuthenticateController@invalidate');
        Route::post('authenticate/register', 'Auth\AuthenticateController@register');

        //Social login
        Route::get('social-callback/{provider}', 'Auth\AuthenticateController@handleSocialCallback');
        Route::post('social-login', 'Auth\AuthenticateController@socialLogin');

        //Forget password
        Route::post('authenticate/forget-password', 'Auth\Password\ForgotPasswordController@getResetToken');
        Route::post('authenticate/reset-password', 'Auth\Password\ResetPasswordController@reset');
        Route::post('authenticate/valid-code', 'Auth\Password\ResetPasswordController@codeValid');

        Route::get('files/quota', 'FileController@getOriganizationStorage');
        Route::post('files/search', 'FileController@searchFiles');
        Route::post('files/search-all', 'FileController@searchGlobal');
        //Image upload
        Route::post('upload', 'UploadController@uploadImage');
        Route::post('upload-presentation-notes', 'UploadController@uploadPresentationNotes');

        Route::post('upload-attachments', 'UploadController@uploadFiles')->middleware('storageLimit');
        Route::post('upload-file', 'UploadController@uploadFile');
        Route::post('upload-system-pdf', 'UploadController@uploadSystemPdf')->middleware('storageLimit');
        Route::post('upload-document', 'UploadController@uploadDocument')->middleware('storageLimit');
        Route::post('upload-disclosure', 'UploadController@uploadDisclosure')->middleware('storageLimit');
        Route::post('upload-mom-template-logo', 'UploadController@uploadMomTemplateLogo')->middleware('storageLimit');
        Route::post('upload-organization-logo', 'UploadController@uploadOrganizationLogo')->middleware('storageLimit');
        Route::post('upload-chat-logo', 'UploadController@uploadChatLogo')->middleware('storageLimit');
        Route::post('upload-profile-image', 'UploadController@uploadProfileImage')->middleware('storageLimit');
        Route::post('upload-circular-decisions-attachment', 'UploadController@uploadCircularDecisionsAttachment')->middleware('storageLimit');
        Route::post('upload-mom-pdf', 'UploadController@uploadMomPdf')->middleware('storageLimit');
        Route::post('upload-approval-document', 'UploadController@uploadApproval')->middleware('storageLimit');
        Route::post('upload-evidence-document', 'UploadController@uploadEvidenceDocument')->middleware('storageLimit');
        Route::post('upload-block-document', 'UploadController@uploadBlockDocument')->middleware('storageLimit');
        Route::post('upload-committee-document', 'UploadController@uploadCommitteeDocument')->middleware('storageLimit');
        //Conver pdf to images
        Route::post('convert-pdf-to-images-urls', 'UploadController@convertPdfToImagesURL');

        //Time zones
        Route::get('time-zones/system-time-zones', 'TimeZoneController@getSystemTimeZones');

        //Signature callback
        Route::post('meetings/sign-mom-callback', 'MeetingController@signMOM');

        // create chat users
        Route::get('chat-rooms/users', 'ChatRoomController@createUsersAtChatApp');

        // get notification options
        Route::get('notification-options', 'NotificationOptionController@index');
    }
);

Route::group(
    ['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
        Route::get('authenticate/user', 'Auth\AuthenticateController@getAuthenticatedUser');
        Route::post('authenticate/send-code', 'Auth\AuthenticateController@sendCode');
        Route::post('authenticate/validate-verification-code', 'Auth\AuthenticateController@verifyLoginCode');
    }
);
Route::group(
    ['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {

        Route::group(
            ['prefix' => 'admin'],
            function () {
                Route::get('access-roles', 'RoleController@getRolesWithoutAdmin');
                Route::get('organizations/data-completed', 'OrganizationController@checkOrganizationDataCompleted');
                Route::post('add-multiple-users', 'UserController@addMultiple');
                Route::get('settings/introduction-video', 'SettingController@getIntroductionVideoUrl');
                Route::get('settings/support-email', 'SettingController@getSupportEmail');
                Route::get('configrations/columns/{column}', 'ConfigrationController@getConfigColumn');
                Route::post("history/filtered-list", 'AuditController@getPagedList');
            }
        );
    }
);

Route::group(
    ['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {

        // Signature email sned to next participant
        Route::get('signature-next-participan-job', 'MeetingController@sendSignatureToNextParticipantCronJob');

        // Reminder job
        Route::get('reminder-job', 'MeetingController@getMeetingRemindersForEmail');

        //send task expired notification job
        Route::get('task-expired', 'TaskManagementController@sendTasksExpiredNotifications');

        //send task expired notification job
        Route::get('organization-expired-date', 'OrganizationController@sendOrganizationExpiredNotifications');

        //check delay status of document review 
        Route::get('documents-reviews/end-date', 'DocumentController@updateStatusOfDocumentToDelay');

        // check if review document time started
        Route::get('documents/start-date', 'DocumentController@sendNotificationWhenReviewDocumentTimeStart');

        // check if circular decision time started
        Route::get('circular-decisions/start-date', 'VoteController@sendNotificationWhenCircularDecisionStart');
        Route::get('circular-decisions/completed', 'VoteController@createDirectoryForCircularDecisionAfterCompleted');

        Route::get('roles', 'RoleController@index');

        // get ldap user
        Route::post('users/ldap-user', 'LdapUserController@getLdapUser');

        Route::get('/get-ldap-integration-feature-variable', 'LdapUserController@getLdapIntegrationFeatureVariable');

        Route::group(['prefix' => 'admin'], function () {
            Route::get('current-url', 'UserController@getCurrentURL');

            Route::get('committees', 'CommitteeController@index');
            Route::get('committees/organization-committee/list', 'CommitteeController@getCurrentOrganizationCommittees');
            Route::get('users/committees', 'CommitteeController@getCommitteesThatUserMemberOnIt');
            Route::post('organizations/time-zones', 'OrganizationController@getOrganizationTimeZones');
            Route::post('organizations/meeting-roles', 'OrganizationController@getMeetingRoles');
            Route::get('reminders', 'ReminderController@index');
            Route::get('organization-types', 'OrganizationTypeController@index');
            Route::get('agenda-purposes', 'AgendaPurposeController@index');
            Route::get('settings', 'SettingController@index');
            Route::put('settings', 'SettingController@updateSettings');
            Route::post('files', 'UploadController@getFile');
            Route::get('meetings-statuses', 'MeetingStatusController@index');
            Route::get('vote-types', 'VoteTypesController@index');
            Route::get('languages', 'LanguageController@index');
            Route::get('meeting-attendance-statuses', 'MeetingAttendanceStatusController@index');
            Route::get('meetings-statuses', 'MeetingStatusController@index');
            Route::get('decision-types', 'DecisionTypeController@index');
            Route::get('agenda-templates', 'AgendaTemplateController@index');
            Route::get('agenda-templates/list', 'AgendaTemplateController@getOrganizationAgendaTemplates');
            Route::get('html-mom-templates/list', 'HtmlMomTemplateController@getOrganizationHtmlMomTemplates');
            Route::get('html-mom-templates', 'HtmlMomTemplateController@index');
            Route::get('job-titles', 'JobTitleController@index');
            Route::get('meeting-types', 'MeetingTypeController@index');
            Route::get('mom-templates', 'MomTemplateController@index');
            Route::get('nicknames', 'NicknameController@index');
            Route::get('user-titles', 'UserTitleController@index');
            Route::get('time-zones', 'TimeZoneController@index');
            Route::get('user-online-configurations', 'UserOnlineConfigurationController@index');
            Route::get('mom-templates/list', 'MomTemplateController@getOrganizationMomTemplates');
            Route::get('decision-result-statuses', 'VoteResultStatusController@index');
            Route::get('document-statuses', 'DocumentStatusController@index');
            Route::get('vote-statuses', 'VoteStatusController@index');
            Route::get('proposals', 'ProposalController@index');
            Route::post('organizations/organization-proposals', 'OrganizationController@getOrganizationProposals');
            Route::get('task-statuses', 'TaskStatusController@index');
            Route::get('videos-guide', 'GuideVideoController@index');
            Route::get('tutorial-steps', 'GuideVideoController@getTutorialStepsList');

            Route::post('committees/{id}/unfreeze', 'CommitteeController@unfreezeCommittee');
            // online-meeting-apps
            Route::get('online-meeting-apps', 'OnlineMeetingAppController@index');

            // notification
            Route::post('notifications/{notification_id}/read', 'NotificationController@changeNotificationIsReadFlag');
            Route::post('notifications/filtered-list', 'NotificationController@getPagedList');
            Route::get('notifications/list', 'NotificationController@getListOfNotification');
            Route::get('new-notifications/count', 'NotificationController@getCountOfNewNotification');
            Route::post('notifications/read-all', 'NotificationController@readAllNotifications');

            Route::get('system-disclosure/download', 'OrganizationController@downloadSystemDisclosure');
            Route::get('organizations/disclosure/download', 'OrganizationController@downloadOrganizationDisclosure');
            Route::get('organizations/default-disclosure/download', 'UserController@downloadOrganizationOrDefaultDisclosure');

            Route::post('users/my-profile', 'UserController@updateMyProfile');
            Route::get('users/disclosure/download', 'UserController@downloadUserDisclosure');
            Route::get('approval-statuses', 'ApprovalStatusController@index');

            Route::get('committee-statuses', 'CommitteeStatusController@index');
            Route::get('committee-types', 'CommitteeTypeController@index');
            Route::get('recommendation-status', 'RecommendationStatusController@index');
            Route::get('committee-natures', 'CommitteeNatureController@index');
            //export excel
            Route::get('committees/{id}/export-excel','CommitteeController@exportSingleCommittee');
            Route::get('committees/export-excel','CommitteeController@exportAllCommittees');
            Route::get('committees/export-my-committees-excel', 'CommitteeController@exportMyCommittees');

            // committee custom feature
            Route::get('/get-committee-nature-feature-variable', 'CommitteeController@getCommitteeHasNatureFeatureVariable');

        });

        Route::group(
            ['prefix' => 'userAccess'],
            function () {
                Route::get('roles/CanAccess/{rightId}', 'RoleController@CanAccess');
                Route::get('users/rights', 'RoleController@getRoleRights');
                Route::get('users/conversation-right', 'RoleController@getConversationRight');
                Route::get('roles/modules-rights', 'RoleController@getModulesRights');
                Route::get('users/access-rights', 'RoleController@getRoleAccessRights');
                Route::get('users/all-access-rights', 'RoleController@getAllRoleAccessRights');
            }
        );

    }
);