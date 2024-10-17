<?php

return [
    'deleteFile' => env('DELETE_FILE', false),
    'addCommitteeNewFields' =>env('ADD_COMMITTEE_NEW_FIELDS_FEATURE',false),
    'addUserFeature' =>env('ADD_USER_FEATURE',false),
    'deleteUserFeature' =>env('DELETE_USER_FEATURE',false),
    'removeDefaultCommittees' => env('REMOVE_DEFAULT_COMMITTEE', false),
    'addFileToCommitteeFeature' =>env('ADD_FILE_TO_COMMITTEE_FEATURE',false),
    'adminCommitteeMenu' => env('ADMIN_COMMITTEE_MENU', false),
    'removeCommitteeCode' => env('REMOVE_COMMITTEE_CODE', false),
    'workDoneByCommittee' => env('WORK_DONE_BY_COMMITTEE', false),
    'blockUserFeature' => env('BLOCK_USER_FEATURE', false),
    'meetingRecommendationsFeature' => env('MEETING_RECOMMENDATIONS_FEATURE', false),
    'updateCommitteeRequestFeature' => env('UPDATE_COMMITTEE_REQUEST_FEATURE', false),
    'additionalUserFields' => env('ADDITIONAL_USER_FIELDS', false),
    'ldapIntegration' => env('LDAP_INTEGRATION', false),
    'committeeHasNatureFeature' => env('COMMITTEE_NATURE_FEATURE', true),
];