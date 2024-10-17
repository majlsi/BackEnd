<?php

use Illuminate\Database\Seeder;

class ChatGroupTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $records=[
            ['id' => 1, 'chat_group_type_en' => 'Individual', 'chat_group_type_ar' => 'فردى'],
            ['id' => 2, 'chat_group_type_en' => 'Group', 'chat_group_type_ar' => 'مجموعة'],
            ['id' => 3, 'chat_group_type_en' => 'Meeting', 'chat_group_type_ar' => 'إجتماع'],
            ['id' => 4, 'chat_group_type_en' => 'Committee', 'chat_group_type_ar' => 'لجنة'],
        ];

        foreach ($records as $key => $record) {
            $exists = DB::table('chat_group_types')->where('id', $record['id'])->first();
            if(!$exists){
                DB::table('chat_group_types')->insert([$record]);    
            } else {
                DB::table('chat_group_types')
                    ->where('id', $record['id'])
                    ->update([
                        'chat_group_type_en' => $record['chat_group_type_en'],
                        'chat_group_type_ar' => $record['chat_group_type_ar'],
                    ]);
            }
        }
    }
}
