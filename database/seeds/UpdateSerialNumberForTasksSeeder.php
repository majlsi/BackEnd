
<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UpdateSerialNumberForTasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $organizations = DB::table('organizations')->whereNull('deleted_at')->get();
        foreach ($organizations as $key => $organization) {
            $date = null;
            $sequence = 0;
            $tasks = DB::table('task_management')->selectRaw('task_management.id,task_management.created_at,task_management.serial_number')->where('task_management.organization_id',$organization->id)->orderBy('task_management.id')->whereNull('task_management.deleted_at')->get();
            foreach ($tasks as $index => $task) {
                if(!$task->serial_number){
                    $committee = DB::table('committees')->join('task_management','task_management.committee_id','committees.id')->where('task_management.id',$task->id)->first();
                    $createdDate = Carbon::parse($task->created_at);
                    $sequence = ($date != $createdDate->format('d-m-y'))? 1 : $sequence + 1;
                    $date = ($date != $createdDate->format('d-m-y'))? $createdDate->format('d-m-y') : $date;
                    $data['task_sequence'] = $sequence;
                    $data['serial_number'] = $organization->organization_code.'-'.$committee->committee_code.'-'.$createdDate->format('d').$createdDate->format('m').$createdDate->format('y').'-'.sprintf('%03d',$data['task_sequence']);
                    DB::table('task_management')->where('id', $task->id)->update($data);
                }
            }
        }
    }

}
