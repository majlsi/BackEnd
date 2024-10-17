<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Services\AttachmentService;
use Illuminate\Support\Str;

class AttachmentHelper
{

    private $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function prepareAttachmentData($urls){
        $data = [];

        foreach ($urls as $key => $url) {
            $data[$key] = ['attachment_url' => $url];
        }
        return $data;
    }
}
