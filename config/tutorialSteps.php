<?php

return [
    [
        'tutorial_step_name_ar' => 'إضافه اجتماع',
        'tutorial_step_name_en' => 'Add meeting',
        'tutorial_step_tag' => 'addMeeting',
        'tutorial_start_route' => config('frontEndRoutes.addMeetingButton'),
        'tutorial_steps_list' => ['addMeeting','addMeetingInfo@'. config('frontEndRoutes.addMeetingpage'),'saveMeetingInfo@'. config('frontEndRoutes.addMeetingpage')],
    ],
    [
        'tutorial_step_name_ar' => 'إضافه قرار بالتمرير',
        'tutorial_step_name_en' => 'Add circular decision',
        'tutorial_step_tag' => 'addCircularDecision',
        'tutorial_start_route' => config('frontEndRoutes.addCircularDecisionButton'),
        'tutorial_steps_list' => ['addCircularDecision','addCircularDecisionData@'.config('frontEndRoutes.saveCircularDecisionButton'),'saveCircularDecision@'.config('frontEndRoutes.saveCircularDecisionButton')],
    ],
    // [
    //     'tutorial_step_name_ar' => 'إضافه مهمة لقرار بالتمرير',
    //     'tutorial_step_name_en' => 'Add task for circular decision',
    //     'tutorial_step_tag' => 'circularDecisionTasks',
    //     'tutorial_steps_list' => ['circularDecisionTasks','addCircularDecisionTask','saveCircularDecisionTask'],
    // ],
    [
        'tutorial_step_name_ar' => 'إضافه عضو',
        'tutorial_step_name_en' => 'Add user',
        'tutorial_step_tag' => 'addUser',
        'tutorial_start_route' => config('frontEndRoutes.addUSerButton'),
        'tutorial_steps_list' => ['addUser','addUserData@'.config('frontEndRoutes.addUserPage'),'saveUser@'.config('frontEndRoutes.addUserPage')],
    ],
    [
        'tutorial_step_name_ar' => 'إضافه مستند',
        'tutorial_step_name_en' => 'Add document',
        'tutorial_step_tag' => 'addDocument',
        'tutorial_start_route' => config('frontEndRoutes.addDocumentButton'),
        'tutorial_steps_list' => ['addDocument','addDocumentData@'.config('frontEndRoutes.addDocumentPage'),'saveDocument@'.config('frontEndRoutes.addDocumentPage')],
    ],
    [
        'tutorial_step_name_ar' => 'إضافه محادثة ',
        'tutorial_step_name_en' => 'Add conversation',
        'tutorial_step_tag' => 'addConversation',
        'tutorial_start_route' => config('frontEndRoutes.addConversationButton'),
        'tutorial_steps_list' => ['addConversation'],
    ],
    [
        'tutorial_step_name_ar' => 'إضافه اعدادات الاونلاين ',
        'tutorial_step_name_en' => 'Online configuration',
        'tutorial_step_tag' => 'addOnlineConfiguration',
        'tutorial_start_route' => config('frontEndRoutes.addOnlineConfigurationButton'),
        'tutorial_steps_list' => ['addOnlineConfiguration',
            'addOnlineConfigurationData@'.config('frontEndRoutes.addOnlineConfigurationpage'),'saveOnlineConfiguration@'.config('frontEndRoutes.addOnlineConfigurationpage')],
    ],
    [
        'tutorial_step_name_ar' => 'إضافه نموذج محضر الاجتماع ',
        'tutorial_step_name_en' => 'Mom templates',
        'tutorial_step_tag' => 'addMomTemplate',
        'tutorial_start_route' => config('frontEndRoutes.addMomTemplateButton'),
        'tutorial_steps_list' => ['addMomTemplate',
            'addMomTemplateData@'.config('frontEndRoutes.addMomTemplatePage'),'saveMomTemplate@'.config('frontEndRoutes.addMomTemplatePage')],
    ],
    [
        'tutorial_step_name_ar' => 'إضافه نموذج جدول الاعمال ',
        'tutorial_step_name_en' => 'Agenda template',
        'tutorial_step_tag' => 'addAgendaTemplate',
        'tutorial_start_route' => config('frontEndRoutes.addAgendaTemplateButton'),
        'tutorial_steps_list' => ['addAgendaTemplate',
            'addAgendaTemplateData@'.config('frontEndRoutes.addAgendaTemplatepage'),'saveAgendaTemplate@'.config('frontEndRoutes.addAgendaTemplatepage')],
    ],
    [
        'tutorial_step_name_ar' => 'إضافه ملخص محضر الاجتماع ',
        'tutorial_step_name_en' => 'Mom summary',
        'tutorial_step_tag' => 'addMomSummary',
        'tutorial_start_route' => config('frontEndRoutes.addMomSummaryTemplateButton'),
        'tutorial_steps_list' => ['addMomSummary',
            'addMomSummaryData@'.config('frontEndRoutes.addMomSummaryTemplatePage'),'saveMomSummary@'.config('frontEndRoutes.addMomSummaryTemplatePage')],
    ],
    [
        'tutorial_step_name_ar' => 'إضافه ملف ',
        'tutorial_step_name_en' => 'Add file',
        'tutorial_step_tag' => 'addFile',
        'tutorial_start_route' => config('frontEndRoutes.addFileButton'),
        'tutorial_steps_list' => ['addFile','uploadFile@'.config('frontEndRoutes.addFileButton')]
    ],
];