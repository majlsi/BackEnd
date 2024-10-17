<?php

return [
    
    'template_name_en'=>'Default',
    'template_name_ar'=>'الأساسية',

    'introduction_template_ar' => "  تم -بعون الله تعالى- انعقاد {{\$data['meeting_title_ar']}} ل {{\$data['committee_name_ar']}}  بتاريخ {{\$data['meeting_schedule_date_from']}} في تمام الساعة {{\$data['meeting_schedule_time_from']}}، ب {{\$data['meeting_venue_ar']}}،",
    'introduction_template_en' => "  Done -with help of Allah- holding of  {{\$data['meeting_title_en']}} of {{\$data['committee_name_en']}} on {{\$data['meeting_schedule_date_from']}} at {{\$data['meeting_schedule_time_from']}} in  {{\$data['meeting_venue_en']}}",

    'member_list_introduction_template_ar' => "وبحضور أصحاب المعالي والسعادة أعضاء مجلس الإدارة  التالية أسمائهم:",
    'member_list_introduction_template_en' => "In the presence of their Excellencies the following members of the Board of Directors:",

    "conclusion_template_en" => "",
    "conclusion_template_ar" => "",

    'swcc_introduction_template_ar' => "اشارة الى {{ \$data['meeting_title_ar'] ? \$data['meeting_title_ar'] : \$data['meeting_title_en'] }} - {{ \$data['meeting_description_ar'] ? \$data['meeting_description_ar'] : \$data['meeting_description_en'] }},هذا و قد تم الاجتماع, والخروج بهذا المحضر وفق الاجندة والتوصيات التالية",
    'swcc_introduction_template_en' => "A reference to {{ \$data['meeting_title_en'] ? \$data['meeting_title_en'] : \$data['meeting_title_ar'] }} - {{ \$data['meeting_description_en'] ? \$data['meeting_description_en'] : \$data['meeting_description_ar'] }},The meeting took place, and these minutes were produced according to the following agenda and recommendations",

];