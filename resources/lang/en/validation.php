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

    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field is required.',
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => 'The :attribute may not be greater than :max characters.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'not_in' => 'The selected :attribute is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values is present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute format is invalid.',
    'unique_with' => 'The :attribute has already been used before.',

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
            'required' => 'The email field is required',
            'unique' => 'The email has already been taken',
            'email' => 'The email must be a valid email address',
        ],
        'name_ar' => [
            'required' => 'The name field is required',
        ],
        'name' => [
            'required' => 'The name field is required',
        ],
        'password' => [
            'required' => 'The password field is required',
        ],
        'role_id' => [
            'required' => 'The role id field is required',
        ],
        'organization_name_ar' => [
            'required' => 'The organization name field is required',
        ],
        'organization_name_en' => [
            'required' => 'The organization name field is required',
        ],
        'organization_phone' => [
            'required' => 'The organization phone field is required',
        ],
        'organization_number_of_users' => [
            'required' => 'The organization users number field is required',
        ],
        'organization_directory_quota' => [
            'required' => 'Available organization storage space is required',
        ],
        'image_url' => [
            'required' => 'The image url field is required',
        ],
        'original_image_url' => [
            'required' => 'The original image url field is required',
        ],
        'username' => [
            'required' => 'The email field is required',
            'unique_with' => 'The email has already been taken',
            'unique' => 'The email has already been taken',
        ],
        'organization_code' => [
            'required' => 'The organization code field is required',
            'unique' => 'The organization code has already been taken',
        ],
        'committee_name_ar' => [
            'required' => 'The committee arabic name field is required',
        ],
        'committee_code' => [
            'required' => 'The committee code field is required',
            'unique_with' => 'The committee code has already been taken',
        ],
        'file' => [
            'required' => 'The files field is required',
            'mimes' => 'The attachements must be a file of type: jpeg,jpg,png,pdf,txt,doc,odt,xls,xlsx,ppt,pptx,avi,mov,mp4,wmv,rtf',
            'max' => 'The attachements must less than :' . (config('attachment.file_size') / 1000000) . 'GB',
        ],
        'user_title_ar' => [
            'required' => 'User Title field is required',
        ],
        'committee_organiser_id' => [
            'required' => 'Committee organiser field is required',
        ],
        'job_title_ar' => [
            'required' => 'Job Title field is required',
        ],
        'nickname_ar' => [
            'required' => 'Nickname field is required',
        ],
        'language' => [
            'required' => 'Language field is required',
        ],
        'zoom_api_key' => [
            'required' => 'Zoom api key field is required',
        ],
        'zoom_api_secret' => [
            'required' => 'Zoom api secret field is required',
        ],
        'zoom_audio' => [
            'required' => 'Zoom audio field is required',
        ],
        'zoom_approval_type' => [
            'required' => 'Zoom approval type field is required',
        ],
        'zoom_auto_recording' => [
            'required' => 'Zoom auto type recording is required',
        ],
        'chat_group_users_ids' => [
            'required' => 'Member Users is required',
            'min' => 'Member Users must be greater than 2 users ',
        ],
        'chat_group_name_ar' => [
            'unique' => 'Chat group arabic name has already been taken',
        ],
        'chat_group_name_en' => [
            'unique' => 'Chat group english name has already been taken',
        ],
        'member_user_id' => [
            'required' => 'Chat member user id is required',
        ],
        'microsoft_azure_app_id' => [
            'required' => 'Microsoft azure Application (client) ID is required',
        ],
        'microsoft_azure_tenant_id' => [
            'required' => 'Microsoft azure Directory (tenant) ID is required',
        ],
        'microsoft_azure_client_secret' => [
            'required' => 'Microsoft azure client secret is required',
        ],
        'microsoft_azure_user_id' => [
            'required' => 'Microsoft azure user Object ID is required',
        ],
        'template_name_ar' => [
            'required' => 'Mom arabic template name is required',
        ],
        'template_name_en' => [
            'required' => 'Mom english template name is required',
        ],
        'introduction_template_ar' => [
            'required' => 'Arabic introduction is required',
        ],
        'introduction_template_en' => [
            'required' => 'English introduction is required',
        ],
        'member_list_introduction_template_ar' => [
            'required' => 'Arabic members introduction is required',
        ],
        'member_list_introduction_template_en' => [
            'required' => 'English members introduction is required',
        ],
        'agenda_template_name_ar' => [
            'required' => 'Agenda arabic template name is required',
        ],
        'agenda_template_name_en' => [
            'required' => 'Agenda english template name is required',
        ],
        'agenda_description_template_ar' => [
            'required' => 'Arabic description is required',
        ],
        'agenda_description_template_en' => [
            'required' => 'English description is required',
        ],
        'html_mom_template_name_ar' => [
            'required' => 'Agenda arabic template name is required',
        ],
        'html_mom_template_name_en' => [
            'required' => 'Agenda english template name is required',
        ],
        'html_mom_description_template_ar' => [
            'required' => 'Arabic description is required',
        ],
        'html_mom_description_template_en' => [
            'required' => 'English description is required',
        ],
        'document' => [
            'document_description_ar' => [
                'required' => 'Document arabic description field is required'
            ],
            'document_url' => [
                'required' => 'Document url field is required'
            ],
            'document_subject_ar' => [
                'required' => 'Document arabic subject field is required'
            ],
            'document_name' => [
                'required' => 'Document name field is required'
            ],
            'committee_id' => [
                'required' => 'Committee id field is required'
            ],
            'review_start_date' => [
                'required' => 'Review start date field is required'
            ],
            'review_end_date' => [
                'required' => 'Review end date field is required'
            ],
            'file' => [
                'required' => 'The file field is required',
                'mimes' => 'The attachements must be a file of type: pdf,txt,doc,docx,xls,xlsx,ppt,pptx',
                'max' => 'The attachements must less than :' . (config('attachment.file_size') / 1000000) . 'GB',
            ],
            'document_users' => [
                'required' => 'document users field is required',
            ]
        ],
        'document_annotation' => [
            'page_number' => [
                'required' => 'Page number field is required'
            ],
            'annotation_text' => [
                'required' => 'Annotation text field is required'
            ],
            'x_upper_left' => [
                'required' => 'Postion of x  field is required'
            ],
            'y_upper_left' => [
                'required' => 'Postion of y  field is required'
            ]
        ],
        'faq_section_name_ar' => [
            'required' => 'Arabic name is required',
        ],
        'date_of_birth' => [
            'required' => 'Date of birth is required',
            'date' => 'Date of birth must be a date',
        ],
        'identity_number' => [
            'required' => 'Identity number is required',
            'unique' => 'Identity number has already been taken',
        ],
        'share' => [
            'required' => 'Share is required',
            'numeric' => 'Share must be numeric',
            'min' => 'Share must be greater than 0',
            'max' => 'Share must be less than 100',
            'total_not_valid' => 'Total share must be between 0 and 100',
        ],
        'is_active' => [
            'required' => 'status is required',
        ],
        'stakesholder_id' => [
            'required' => 'Stakeholder is required',
            'exists' => 'Stakeholder not found',
        ],
        'stakeholders' => [
            'count' => 'Can\'t add new stakeholder',
            'role' => 'You can\'t add new stakeholder',
            'exception' =>  'Couldn\'t add new stakeholder',
            'limit' => 'You have reached the maximum limit of stakeholder',
        ],
        'committee_id' => [
            "required" => 'Committee is required',
            "numeric" => "Committee is invalid",
            "exists" => "Comittee not exists"
        ],
        'members' => [
            'required' => "Members are required",
            'exists' => "Member not found"
        ],
        'attachments' => [
            'required' => "Attachments required",
            'exists' => "Attachment not found"
        ],
        'approval_attachment_id' => [
            'required' => "Attachments required",
            'exists' => "Attachment not found"
        ],
        'approval_title' => [
            'required' => "Title is required",
        ],
        'request_type_id'=>[
            "required" => "Request Type required",
            "numeric" => "Request Type is invalid",
            "exists" => "Request Type not found",
        ],
        'request_body'=>[
            "required" => "Request Data required",
        ],
        'created_by'=>[
            "required" => "Request Creator required",
            "numeric" => "Request Creator is invalid",
            "exists" =>"Request Creator not found",
        ],
        'decision_number' => [
            'required' => 'Decision number is required',
        ],
        'decision_date' => [
            'required' => 'Decision date is required',
        ],
        'decision_responsible_user_id' => [
            'required' => 'Decision responsible user id is required',
        ],
        'committee_status_id' => [
            'required' => 'Committee status id is required',
        ],
        'decision_document_url' => [
            'required' => 'decision document url is required',
        ],
        'committee_type_id' => [
            'required' => 'Committee type id is required',
        ],
        'committee_start_date' => [
            'required' => 'Committee start date is required',
        ],
        'committee_expired_date' => [
            'required' => 'committee expired date is required',
        ],
        'request_body_reason' => [
            'required' => 'reason is required',
        ],
        'evidence_document_url' => [
            'required' => 'evidence document url is required',
        ],
        'reject_reason'=> [
            'required' => 'reject reason url is required',
        ],
        'recommendations' => [
            'recommendation_body' => [
                'required' => 'recommendation body is required',
            ]
        ],
        'file_id' => [
            'required' => 'file id is required',
        ],
        'meeting_id' => [
            "required" => 'meeting is required',
            "numeric" => "meeting is invalid",
            "exists" => "meeting not exists"
        ],
        'recommendation_text'=>[
            'required' => 'recommendation text is required',
        ],
        'recommendation_date'=>[
            'required' => 'recommendation date is required',
        ],
        'responsible_user'=>[
            'required' => 'responsible user text is required',
        ],
        'responsible_party'=>[
            'required' => 'responsible party text is required',
        ],
        'final_output_url' => [
            'required' => 'Final output file is required',
        ],
        'recommendation_status_id' => [
            'required' => 'Recommendation status id is required',
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
