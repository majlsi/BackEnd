<?php

namespace Helpers;

use Carbon\Carbon;

class ModuleHelper
{

    public function __construct()
    {
    }

    public function getconversationRight(){
        return [
            'icon' => 'fas fa-comments font-awesome-icon',
            'title' => 'Conversations',
            'title_ar' => 'المحادثات',
            'submenu' => [
                [
                    'icon' => '',
                    'in_menu' => 1,
                    'module_id' => 9,
                    'page' => '/conversations/chats',
                    'right_name' => 'All conversations',
                    'right_name_ar' => 'كل المحادثات',
                    'right_order_number' => 1,
                    'right_type_id' => null,
                    'right_url' => '/conversations/chats',
                    'title' => 'All conversations',
                    'title_ar' => 'كل المحادثات',
                ]
            ]
        ];
    }
}