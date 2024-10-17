<?php

namespace Helpers;

class MomTemplateHelper
{

    private $roleService;

    public function __construct()
    {

    }

    public function parseToSimpleView($data)
    {
        if (isset($data['introduction_template_ar'])) {
            $data['introduction_template_ar'] = $this->parseInputWhenView($data['introduction_template_ar']);
        }

        if (isset($data['introduction_template_en'])) {
            $data['introduction_template_en'] = $this->parseInputWhenView($data['introduction_template_en']);
        }

        if (isset($data['member_list_introduction_template_ar'])) {
            $data['member_list_introduction_template_ar'] = $this->parseInputWhenView($data['member_list_introduction_template_ar']);
        }

        if (isset($data['member_list_introduction_template_en'])) {
            $data['member_list_introduction_template_en'] = $this->parseInputWhenView($data['member_list_introduction_template_en']);
        }

        if (isset($data['conclusion_template_en'])) {
            $data['conclusion_template_en'] = $this->parseInputWhenView($data['conclusion_template_en']);
        }

        if (isset($data['conclusion_template_ar'])) {
            $data['conclusion_template_ar'] = $this->parseInputWhenView($data['conclusion_template_ar']);
        }

        return $data;
    }

    private function parseInputWhenView($text)
    {
        $text = str_replace("{{\$data['", "{", $text);
        $text = str_replace("']}}", "}", $text);

        return $text;
    }
}
