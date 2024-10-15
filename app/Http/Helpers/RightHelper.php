<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class RightHelper
{

    public function __construct()
    {
    }

    public function prepareRightDataForOrganizationAdmin(){
        return $rightsData = [
            ['right_id' => '1'],
            ['right_id' => '2'],
            ['right_id' => '3'],
            ['right_id' => '4'],
            ['right_id' => '5'],
            ['right_id' => '6'],
            ['right_id' => '13'],
            ['right_id' => '14'],
            ['right_id' => '15'],
            // ['right_id' => '16'],
            // ['right_id' => '17'],
            // ['right_id' => '18'],
            ['right_id' => '19'],
            ['right_id' => '20'],
            ['right_id' => '21'],
            ['right_id' => '22'],
            ['right_id' => '23'],
            ['right_id' => '24'],
            ['right_id' => '25'],
            ['right_id' => '26'],
            ['right_id' => '27'],
            ['right_id' => '28'],
            ['right_id' => '29'],
            ['right_id' => '30'],
            ['right_id' => '31'],

            ['right_id' => '32'],
            // ['right_id' => '33'],
            // ['right_id' => '34'],
            // ['right_id' => '35'],
            ['right_id' => '36'],
            ['right_id' => '37'],
            ['right_id' => '38'],
            // ['right_id' => '39'],
            ['right_id' => '64'],
            ['right_id' => '68'],
            ['right_id' => '69'],
            ['right_id' => '70'],
            ['right_id' => '65'],
            ['right_id' => '66'],
            ['right_id' => '67'],
            ['right_id' => '101'],
            ['right_id' => '76'],
            ['right_id' => '106'],
            ['right_id' => '107'],
            ['right_id' => '108'],
            ['right_id' => '109'],
            ['right_id' => '110'],
            /* Faq Rights */
            ['right_id' => '127'],
            // Guest Rights
            ['right_id' => '137'],
            ['right_id' => '138'],
            ['right_id' => '139'],
            ['right_id' => '140'],
            ['right_id' => '141'],
            /* Meeting Page Rights */
            ['right_id' => '142'],
            ['right_id' => '143'],
            ['right_id' => '144'],
            ['right_id' => '145'],
            ['right_id' => '146'],
            // My Committee Page
            ['right_id' => '158'],


        ];
    }


    public function prepareRightDataForMembers(){
        return $rightsData = [
            ['right_id' => '30'],
            ['right_id' => '31'],
            ['right_id' => '36'],
            ['right_id' => '37'],
            ['right_id' => '38'],
            ['right_id' => '42'],
            ['right_id' => '46'],
            ['right_id' => '69'],
            ['right_id' => '70'],
            ['right_id' => '96'],
            ['right_id' => '97'],
            ['right_id' => '98'],
            ['right_id' => '99'],
            ['right_id' => '100'],
            ['right_id' => '101'],
            ['right_id' => '76'],
            ['right_id' => '106'],
            ['right_id' => '107'],
            ['right_id' => '108'],
            ['right_id' => '109'],
            ['right_id' => '110'],
            /* Faq Rights */
            ['right_id' => '127'],
            // Guest Rights
            ['right_id' => '137'],
            ['right_id' => '138'],
            ['right_id' => '139'],
            ['right_id' => '140'],
            ['right_id' => '141'],
            /* Meeting Page Rights */
            ['right_id' => '142'],
            ['right_id' => '143'],
            ['right_id' => '144'],
            ['right_id' => '145'],
            ['right_id' => '146'],
            // My Committee Page
            ['right_id' => '158'],

        ];
    }

	public function prepareRightDataForSecretary(){
        return $rightsData = [
            ['right_id' => '22'],
            ['right_id' => '23'],
            ['right_id' => '24'],
            // ['right_id' => '33'],
            // ['right_id' => '34'],
            // ['right_id' => '35'],
            ['right_id' => '30'],
            ['right_id' => '31'],
            ['right_id' => '36'],
            ['right_id' => '37'],
            ['right_id' => '38'],
            ['right_id' => '41'],
            // ['right_id' => '44'],
            // ['right_id' => '45'],
            ['right_id' => '46'],
            ['right_id' => '48'],
            ['right_id' => '64'],
            ['right_id' => '69'],
            ['right_id' => '70'],
            ['right_id' => '65'],
            ['right_id' => '66'],
            ['right_id' => '67'],  
            ['right_id' => '80'],
            ['right_id' => '81'],
            ['right_id' => '82'],
            ['right_id' => '83'],
            ['right_id' => '84'],
            ['right_id' => '85'],
            ['right_id' => '86'],
            ['right_id' => '87'],
            ['right_id' => '88'],
            ['right_id' => '89'],
            ['right_id' => '90'],
            ['right_id' => '91'],
            ['right_id' => '92'],
            ['right_id' => '93'],
            ['right_id' => '94'],
            ['right_id' => '95'],
            ['right_id' => '96'],
            ['right_id' => '97'],
            ['right_id' => '98'],
            ['right_id' => '99'],
            ['right_id' => '100'],
            ['right_id' => '101'],
            ['right_id' => '102'],
            ['right_id' => '103'],
            ['right_id' => '104'],
            ['right_id' => '105'],
            ['right_id' => '76'],
            ['right_id' => '106'],
            ['right_id' => '107'],
            ['right_id' => '108'],
            ['right_id' => '109'],
            ['right_id' => '110'],
            /* Faq Rights */
            ['right_id' => '127'],
            // Guest Rights
            ['right_id' => '137'],
            ['right_id' => '138'],
            ['right_id' => '139'],
            ['right_id' => '140'],
            ['right_id' => '141'],
            /* Meeting Page Rights */
            ['right_id' => '142'],
            ['right_id' => '143'],
            ['right_id' => '144'],
            ['right_id' => '145'],
            ['right_id' => '146'],
            // My Committee Page
            ['right_id' => '158'],
        ];
    }

    public function prepareRightDataForBoardMembers(){
        return $rightsData = [
            ['right_id' => '22'],
            ['right_id' => '23'],
            ['right_id' => '24'],
            // ['right_id' => '33'],
            // ['right_id' => '34'],
            // ['right_id' => '35'],
            ['right_id' => '30'],
            ['right_id' => '31'],
            ['right_id' => '36'],
            ['right_id' => '37'],
            ['right_id' => '38'],
            ['right_id' => '42'],
            ['right_id' => '46'],
            ['right_id' => '48'],
            ['right_id' => '64'],
            ['right_id' => '69'],
            ['right_id' => '70'],
            ['right_id' => '65'],
            ['right_id' => '66'],
            ['right_id' => '67'],
            ['right_id' => '96'],
            ['right_id' => '97'],
            ['right_id' => '98'],
            ['right_id' => '99'],
            ['right_id' => '100'],
            ['right_id' => '101'],
            ['right_id' => '76'],
            ['right_id' => '106'],
            ['right_id' => '107'],
            ['right_id' => '108'],
            ['right_id' => '109'],
            ['right_id' => '110'],
            /* Faq Rights */
            ['right_id' => '127'],
            // Guest Rights
            ['right_id' => '137'],
            ['right_id' => '138'],
            ['right_id' => '139'],
            ['right_id' => '140'],
            ['right_id' => '141'],
            /* Meeting Page Rights */
            ['right_id' => '142'],
            ['right_id' => '143'],
            ['right_id' => '144'],
            ['right_id' => '145'],
            ['right_id' => '146'],
            // My Committee Page
            ['right_id' => '158'],
        ];
    }

    /* Guest Rights */
    public function prepareRightDataForGuest()
    {
        return $rightsData = [
            // View Meeting Right
            ['right_id' => '46'],
            ['right_id' => '48'],
            /* Faq Rights */
            ['right_id' => '127'],
            // Guest Rights
            ['right_id' => '137'],
            ['right_id' => '138'],
            ['right_id' => '139'],
            ['right_id' => '140'],
            ['right_id' => '141'],
            /* Meeting Page Rights */
            ['right_id' => '142'],
            ['right_id' => '143'],
            ['right_id' => '144'],
            ['right_id' => '145'],
            ['right_id' => '146'],
        ];
    }
}
