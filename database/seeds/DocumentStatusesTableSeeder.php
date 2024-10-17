<?php

use Illuminate\Database\Seeder;

class DocumentStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = [
            ['id' => 1, 'document_status_name_ar' => 'جديدة', 'document_status_name_en' => 'New'],
            ['id' => 2, 'document_status_name_ar' => 'قيد المراجعة', 'document_status_name_en' => 'In progress'],
            ['id' => 3, 'document_status_name_ar' => 'تمت', 'document_status_name_en' => 'Complete'],
            ['id' => 4, 'document_status_name_ar' => 'متأخرة', 'document_status_name_en' => 'Delay'],

        ];

        foreach ($records as $key => $record) {
            $exists = DB::table('document_statuses')->where('id', $record['id'])->first();
            if (!$exists) {
                DB::table('document_statuses')->insert([$record]);
            } else {
                DB::table('document_statuses')
                    ->where('id', $record['id'])
                    ->update([
                        'document_status_name_ar' => $record['document_status_name_ar'],
                        'document_status_name_en' => $record['document_status_name_en']
                    ]);
            }
        }
    }
}        