<?php

/**
 * Description of CustomValidator
 *
 * @author Mohamed
 */

namespace App\Validators;

use DB;
use Illuminate\Support\Facades\Schema;

class CustomValidator {


    public function uniqueWith($attribute, $value, $parameters, $validator) {
        if (!array_key_exists($parameters[1],$validator->getData())) {
            return false;
        }
        $q = DB::table($parameters[0])->where($attribute, $value)
                ->where($parameters[1], $validator->getData()[$parameters[1]]);
        if (count($parameters)  >= 4 ) {
            $q->where('id', '!=', $parameters[3]);
        }
        if (Schema::hasColumn($parameters[0], 'deleted_at')) {
            $q->where('deleted_at', null);
        }
        return $q->count() === 0;
    }

}
