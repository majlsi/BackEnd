<?php


use Illuminate\Database\Seeder;

class RequestTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $requestTypesData=
        [
            ['id' => 1, 'request_type_name_ar' => 'اضافة لجنة', 'request_type_name_en' => 'add committee'],
            ['id' => 2, 'request_type_name_ar' => 'مسح لجنة', 'request_type_name_en' => 'delete committee'],
            ['id' => 3, 'request_type_name_ar' => 'اضافة عضو', 'request_type_name_en' => 'add user'],
            ['id' => 4, 'request_type_name_ar' => 'تعديل عضو', 'request_type_name_en' => 'edit user'],
            ['id' => 5, 'request_type_name_ar' => 'مسح عضو', 'request_type_name_en' => 'delete user'],
            ['id' => 6, 'request_type_name_ar' => 'فك تجميد اللجنة', 'request_type_name_en' => 'unfreeze committee'],
            ['id' => 7, 'request_type_name_ar' => 'حذف ملف', 'request_type_name_en' => 'delete file'],
            ['id' => 8, 'request_type_name_ar' => 'تعديل لجنة', 'request_type_name_en' => 'update committee'],
        ];

        foreach ($requestTypesData as $key => $requestType) {
            $exists = DB::table('request_types')->where('id', $requestType['id'])->first();
            if(!$exists){
                DB::table('request_types')->insert([$requestType]);    
            } else {
                DB::table('request_types')
                    ->where('id', $requestType['id'])
                    ->update($requestType);
            }
        }
    }
}
