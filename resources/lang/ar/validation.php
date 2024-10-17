<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute يجب قبول',
    'active_url'           => ':attribute ليست عنوان نطاق صالحًا.',
    'after'                => 'يجب أن تكون :attribute تاريخ بعد :date.',
    'alpha'                => 'قد تحتوي :attribute  على أحرف فقط.',
    'alpha_dash'           => 'قد تحتوي :attribute على أحرف وأرقام وشرطات فقط.',
    'alpha_num'            => 'قد تحتوي :attribute على أحرف وأرقام فقط.',
    'array'                => 'يجب أن تكون :attribute صفيف.',
    'before'               => 'يجب أن تكون :attribute تاريخ قبل :date.',
    'between'              => [
        'numeric' => 'يجب أن تكون :attribute بين :min و :max.',
        'file'    => 'يجب أن تكون :attribute بين :min و :max كيلو بايت.',
        'string'  => 'يجب أن تكون :attribute بين :min و :max.',
        'array'   => 'يجب أن تحتوي :attribute على العناصر :min و :max.',
    ],
    'boolean'              => 'يجب أن يكون حقل :attribute صواب أو خطأ.',
    'confirmed'            => 'تأكيد :attribute غير متطابق.',
    'date'                 => ':attribute ليست تاريخًا صالحًا.',
    'date_format'          => ':attribute لا تتطابق مع التنسيق :format.',
    'different'            => ':attribute و :other يجب أن تكون مختلفة.',
    'digits'               => 'يجب أن تكون :attribute :digits digits.',
    'digits_between'       => 'يجب أن تكون :attribute بين :min و :max.',
    'dimensions'           => 'تحتوي :attribute على أبعاد صور غير صالحة.',
    'distinct'             => 'يحتوي حقل :attribute على قيمة مكررة.',
    'email'                => 'يجب أن تكون :attribute عنوان بريد إلكتروني صالحًا.',
    'exists'               => ':attribute المحددة غير صالحة.',
    'file'                 => 'يجب أن تكون :attribute ملف.',
    'filled'               => ' حقل :attribute مطلوب.',
    'image'                => 'يجب أن تكون :attribute صورة.',
    'in'                   => ':attribute المحددة غير صالحة.',
    'in_array'             => ' حقل :attribute غير موجود في :other.',
    'integer'              => 'يجب أن تكون :attribute عددًا صحيحًا.',
    'ip'                   => 'يجب أن تكون :attribute عنوان IP صالحًا.',
    'json'                 => 'يجب أن تكون :attribute سلسلة JSON صالحة.',
    'max'                  => [
        'numeric' => 'قد لا تكون :attribute أكبر من :max',
        'file'    => 'قد لا تكون :attribute أكبر من :max كيلو بايت كحد أقصى.',
        'string'  => 'قد لا تكون :attribute أكبر من :max الحد الأقصى لعدد الأحرف.',
        'array'   => 'قد لا تحتوي :attribute أكثر من :max العناصر القصوى.',
    ],
    'mimes'                => 'يجب أن تكون :attribute ملف نوع: :values.',
    'mimetypes'            => 'يجب أن تكون :attribute ملف نوع: :values.',
    'min'                  => [
        'numeric' => 'يجب أن تكون :attribute :min على الأقل.',
        'file'    => 'يجب أن تكون :attribute على الأقل :min كيلوبايت.',
        'string'  => 'يجب أن تكون :attribute على الأقل :min أحرف.',
        'array'   => 'يجب أن تحتوي :attribute على الأقل :min عناصر .',
    ],
    'not_in'               => ':attribute المحددة غير صالحة.',
    'numeric'              => 'يجب أن تكون :attribute رقمًا.',
    'present'              => 'يجب أن يكون حقل :attribut موجودًا.',
    'regex'                => 'تنسيق :attribute غير صالح.',
    'required'             => 'حقل :attribute مطلوب.',
    'required_if'          => 'حقل :attribute مطلوب عندما قيمة :other تساوي :value.',
    'required_unless'      => 'حقل :attribute مطلوب ما لم :other موجود في :values.',
    'required_with'        => 'حقل :attribute مطلوب عند :values موجودة.',
    'required_with_all'    => 'حقل :attribute مطلوب عند :values موجودة.',
    'required_without'     => 'حقل :attribute مطلوب عندما تكون :values غير موجودة.',
    'required_without_all' => 'حقل :attribute مطلوب عند عدم وجود من :values.',
    'same'                 => ':attribute و :other يجب أن تتطابق.',
    'size'                 => [
        'numeric' => 'يجب أن تكون :attribute :size.',
        'file'    => 'يجب أن تكون :attribute  :size كيلوبايت.',
        'string'  => 'يجب أن تكون :attribute :size  أحرف.',
        'array'   => 'يجب أن تحتوي :attribute :size عناصر .',
    ],
    'string'               => 'يجب أن تكون :attribute سلسلة.',
    'timezone'             => 'يجب أن تكون :attribute منطقة صالحة.',
    'unique'               => ':attribute تم اتخاذها بالفعل.',
    'uploaded'             => 'فشلت :attribute في التحميل.',
    'url'                  => 'تنسيق :attribute غير صالح.',
    'phone'                => ':attribute غير صالح لهذه المدينة.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'email' => [
            'required' => 'حقل البريد الإلكتروني مطلوب',
            'unique' => 'تم استخدام البريد الإلكتروني مسبقاً',
            'email' => 'البريد الإلكتروني غير صالح',
        ],
        'name_ar' => [
            'required' => 'حقل الاسم مطلوب',
        ],
        'name' => [
            'required' => 'حقل الاسم مطلوب',
        ],
        'password' => [
            'required' => 'حقل كلمة المرور مطلوب',
        ],
        'organization_name_ar' => [
            'required' => 'حقل اسم المنشأة مطلوب',
        ],
        'organization_name_en' => [
            'required' => 'حقل اسم المنشأة مطلوب',
        ],
        'organization_phone' => [
            'required' => 'حقل هاتف المنشأة مطلوب',
        ],
        'organization_number_of_users' => [
            'required' => 'حقل رقم مستخدمي المنشأة مطلوب',
        ],
        'organization_directory_quota' => [
            'required' => 'مساحة التخزين المتاحة للمنشأة مطلوبة',
        ],
        'image_url' => [
            'required' => 'حقل عنوان url للصور مطلوب',
        ],
        'original_image_url' => [
            'required' => 'حقل عنوان الصورة الأصلي مطلوب',
        ],
        'username' => [
            'required' => 'حقل البريد الإلكتروني مطلوب',
            'unique_with' => 'البريد الالكترونى استخدم من قبل',
            'unique' => ' البريد الالكترونى استخدم من قبل',
        ],
        'organization_code' => [
            'required' => 'حقل رمز المنشأة مطلوب',
            'unique' => 'لقد تم أخذ كود المنشأة بالفعل',
        ],
        'committee_name_ar' => [
            'required' => 'حقل اسم اللجنة باللغة العربية مطلوب',
        ],
        'committee_code' => [
            'required' => 'حقل رمز اللجنة مطلوب',
            'unique_with' => 'لقد تم بالفعل أخذ كود اللجنة',
        ],
        'file' => [
            'required' => 'حقل الملفات مطلوب',
            'mimes' => 'rtf,wmv,mp4,mov,avi,pptx,ppt,xlsx,xls,doc,odt,txt,,pdf,png,jpg,jpeg :' . ' يجب ان تكون المرفقات ملفًا من نوع',
            'max' => 'يجب أن تكون المرفقات أقل من:  ' . (config('attachment.file_size') / 1000000) . 'جيجابايت'
        ],
        'user_title_ar' => [
            'required' => 'حقل اللقب مطلوب',
        ],
        'committee_organiser_id' => [
            'required' => 'حقل أمين مجلس اللجنة مطلوب',
        ],
        'job_title_ar' => [
            'required' => 'حقل الوظيفة مطلوب',
        ],
        'nickname_ar' => [
            'required' => 'حقل الصفه مطلوب',
        ],
        'language' => [
            'required' => 'حقل اللغة مطلوب',
        ],
        'zoom_api_key' => [
            'required' => 'حقل ال api key مطلوب',
        ],
        'zoom_api_secret' => [
            'required' => 'حقل ال api secret مطلوب',
        ],
        'zoom_audio' => [
            'required' => 'حقل التسجيل مطلوب',
        ],
        'zoom_approval_type' => [
            'required' => 'Zoom approval type field is required',
        ],
        'zoom_auto_recording' => [
            'required' => 'حقل طريقه التسجيل مطلوب',
        ],
        'chat_group_users_ids' => [
            'required' => 'حقل اعضاء المحادثة مطلوب',
            'min' => 'حقل اعضاء المحادثة يجب ان لا يقل عن 2 أعضاء'
        ],
        'chat_group_name_ar' => [
            'unique' => 'لقد تم بالفعل أخذ اسم المحادثة',
        ],
        'chat_group_name_en' => [
            'unique' => 'لقد تم بالفعل أخذ اسم المحادثة',
        ],
        'member_user_id' => [
            'required' => 'حقل عضو المحادثة مطلوب',
        ],
        'microsoft_azure_app_id' => [
            'required' => 'حقل رقم التطبيق مطلوب'
        ],
        'microsoft_azure_tenant_id' => [
            'required' => 'حقل رقم tenant مطلوب'
        ],
        'microsoft_azure_client_secret' => [
            'required' => 'حقل الرقم السري مطلوب'
        ],
        'microsoft_azure_user_id' => [
            'required' => 'حقل رقم المستخدم مطلوب'
        ],
        'template_name_ar' => [
            'required' => 'حقل اسم النموذج باللغة العربية مطلوب'
        ],
        'template_name_en' => [
            'required' => 'حقل اسم النموذج باللغة الإنجليزية مطلوب'
        ],
        'introduction_template_ar' => [
            'required' => 'حقل المقدمة باللغة العربية مطلوب'
        ],
        'introduction_template_en' => [
            'required' => 'حقل المقدمة باللغة الإنجليزية مطلوب'
        ],
        'member_list_introduction_template_ar' => [
            'required' => 'حقل مقدمة اﻷعضاء باللغة العربية مطلوب'
        ],
        'member_list_introduction_template_en' => [
            'required' => 'حقل مقدمة اﻷعضاء باللغة الإنجليزية مطلوب'
        ],
        'agenda_template_name_ar' => [
            'required' => 'حقل اسم النموذج باللغة العربية مطلوب'
        ],
        'agenda_template_name_en' => [
            'required' => 'حقل اسم النموذج باللغة الإنجليزية مطلوب'
        ],
        'agenda_description_template_ar' => [
            'required' => 'حقل الوصف باللغة العربية مطلوب'
        ],
        'agenda_description_template_en' => [
            'required' => 'حقل الوصف باللغة الإنجليزية مطلوب'
        ],
        'html_mom_template_name_ar' => [
            'required' => 'حقل اسم النموذج باللغة العربية مطلوب'
        ],
        'html_mom_template_name_en' => [
            'required' => 'حقل اسم النموذج باللغة الإنجليزية مطلوب'
        ],
        'html_mom_description_template_ar' => [
            'required' => 'حقل الوصف باللغة العربية مطلوب'
        ],
        'html_mom_description_template_en' => [
            'required' => 'حقل الوصف باللغة الإنجليزية مطلوب'
        ],
        'document' => [
            'document_description_ar' => [
                'required' => 'وصف المستند باللغة العربية مطلوب'
            ],
            'document_url' => [
                'required' => 'عنوان المستند مطلوب '
            ],
            'document_subject_ar' => [
                'required' => 'اسم المستند باللغة العربية مطلوب'
            ],
            'document_name' => [
                'required' => 'اسم المستند مطلوب'
            ],
            'committee_id' => [
                'required' => 'رقم اللجنة مطلوب'
            ],
            'review_start_date' => [
                'required' => 'تاريخ بداية المراجعة مطلوب'
            ],
            'review_end_date' => [
                'required' => 'تاريخ نهاية المراجعة مطلوب'
            ],
            'file' => [
                'required' => 'الملف مطلوب',
                'mimes' => 'rtf,wmv,mp4,mov,avi,pptx,ppt,xlsx,xls,doc,odt,txt,,pdf :' . ' يجب ان يكون الملف من نوع',
                'max' => 'يجب أن يكون الملف أقل من:  ' . (config('attachment.file_size') / 1000000) . 'جيجابايت',
            ],
            'document_users' => [
                'required' => 'المراجعين مطلوب',
            ]
        ],
        'document_annotation' => [
            'page_number' => [
                'required' => 'رقم الصفحة مطلوب'
            ],
            'annotation_text' => [
                'required' => 'الملاحظة مطلوبه'
            ],
            'x_upper_left' => [
                'required' => 'وضع الملاحظة الافقى مطلوب'
            ],
            'y_upper_left' => [
                'required' => 'وضع الملاحظة الرأسى مطلوب'
            ]
        ],
        'faq_section_name_ar' => [
            'required' => 'حقل الإسم باللغة العربية مطلوب',
        ],
        'date_of_birth' => [
            'required' => 'تاريخ الميلاد مطلوب',
            'date' => 'تاريخ الميلاد غير صالح',
        ],
        'identity_number' => [
            'required' => 'رقم الهوية مطلوب',
            'unique' => 'رقم الهوية موجود بالفعل',
        ],
        'share' => [
            'required' => 'نسبة التمليك مطلوبة',
            'numeric' => 'نسبة التمليك يجب ان تكون رقم',
            'min' => 'نسبة التمليك يجب ان تكون أكبر من 0',
            'max' => 'نسبة التمليك يجب ان تكون أقل من 100',
            'total_not_valid' => 'مجموع نسبة التملك يجب ان تكون بين 0 و 100',
        ],
        'is_active' => [
            'required' => 'الحالة مطلوبة',
        ],
        'stakesholder_id' => [
            'required' => 'المساهم مطلوب',
            'exists' => 'المساهم غير موجود',
        ],
        'stakeholders' => [
            'count' => 'لا يمكن إضافة مساهم جديد',
            'role' => 'لا يمكنك إضافة مساهمين',
            'exception' => 'لا يمكن إضافة مساهم',
            'limit' => 'تم تخطي الحد الأقصى للمساهمين',
        ],
        "meeting_id" => [
            "required" => "الإجتماع مطلوب",
            "numeric" => "رقم الإجتماع غير صحيح",
            "exists" => "الإجتماع غير موجود"
        ],
        'order' => [
            "required" => "الترتيب مطلوب",
            "numeric" => "الترتيب غير صحيح",
            "min" => "الترتيب غير صحيح"
        ],
        'committee_id' => [
            "required" => "اللجنة مطلوبة",
            "numeric" => "رقم اللجنة غير صحيح",
            "exists" => "اللجنة غير موجودة"
        ],
        'members' => [
            'required' => "الأعضاء مطلوبين",
            'exists' => "العضو غير موجود"
        ],
        'attachments' => [
            'required' => "المرفقات مطلوبة",
            'exists' => "المرفقات غير موجودة"
        ],
        'approval_attachment_id' => [
            'required' => "المرفقات مطلوبة",
            'exists' => "المرفقات غير موجودة"
        ],
        'approval_title' => [
            'required' => "عنوان الموافقة مطلوب",
        ],
        'request_type_id'=>[
            "required" => "نوع الطلب مطلوب",
            "numeric" => "رقم نوع الطلب غير صحيح",
            "exists" => "نوع الطلب غير موجود",
        ],
        'request_body'=>[
            "required" => "بيانات الطلب مطلوبة",
        ],
        'created_by'=>[
            "required" => "طالب الطلب مطلوب",
            "numeric" => "رقم طالب الطلب غير صحيح",
            "exists" => "طالب الطلب غير موجود",
        ],
        'decision_number' => [
            'required' => 'رقم القرار مطلوب',
        ],
        'decision_date' => [
            'required' => 'تاريخ القرار مطلوب',
        ],
        'decision_responsible_user_id' => [
            'required' => 'المعتمد لتشكيل اللجنة مطلوب',
        ],
        'committee_status_id' => [
            'required' => 'حالة اللجنة مطلوبة',
        ],
        'decision_document_url' => [
            'required' => 'قرار تشكيل اللجنة مطلوب',
        ],
        'committee_type_id' => [
            'required' => 'نوع اللجنة مطلوب',
        ],
        'committee_start_date' => [
            'required' => 'تاريخ بداية اللجنة مطلوب في حالة انها مؤقتة',
        ],
        'committee_expired_date' => [
            'required' => 'تاريخ نهاية اللجنة مطلوب في حالة انها مؤقتة',
        ],
        'request_body_reason' => [
            'required' => 'سبب الطلب مطلوب',
        ],
        'evidence_document_url' => [
            'required' => 'مستند الاثبات مطلوب',
        ],
        'recommendations' => [
            'recommendation_body' => [
                'required' => 'نص التوصيات مطلوب',
            ]
        ],
        'reject_reason'=> [
            'required' => 'سبب الرفض مطلوب',
        ],
        'file_id' => [
            'required' => 'الملف مطلوب',
        ],
        'recommendation_text'=>[
            'required' => 'نص التوصية مطلوب',
        ],
        'recommendation_date'=>[
            'required' => 'تاريخ التوصية مطلوب',
        ],
        'responsible_user'=>[
            'required' => 'المسؤول مطلوب',
        ],
        'responsible_party'=>[
            'required' => 'الجهة الملزمة مطلوبة',
        ],
        'final_output_url' => [
            'required' => 'الملف المخرج النهائي مطلوب',
        ],
        'recommendation_status_id' => [
            'required' => 'حالة التوصية مطلوبة',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
