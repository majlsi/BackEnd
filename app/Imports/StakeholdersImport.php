<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Models\Stakeholder as ModelsStakeholder;

class StakeholdersImport implements ToModel
{
    use Importable;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|Stakeholder
     */

    public function model(array $row)
    {
        return new ModelsStakeholder([
            'name' => $row[0],
            'email' => $row[1],
            'date_of_birth' => $row[2],
            'identity_number' => $row[3],
            'share' => $row[4],
        ]);
    }
}
