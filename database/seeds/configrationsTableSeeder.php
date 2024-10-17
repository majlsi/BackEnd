<?php

use Illuminate\Database\Seeder;

class configrationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = [
            [
                'id' => 1, 
                'introduction_video_url' => 'https://www.youtube.com/embed/9fb1iVMfkRc',
                'support_email' => 'eman.mohamed@enozom.com',
                'mjlsi_system_before_meeting_video_url' => 'https://www.youtube.com/embed/gazXoa_lTYA',
                'explain_create_meeting_video_url' => 'https://www.youtube.com/embed/RZKpcFZKFvc',
                'manage_board_meeting_video_url' => 'https://www.youtube.com/embed/HWPE2a7FMaM',
                'manage_board_meeting_extra_video_url' => 'https://www.youtube.com/embed/Db4YIOkpN_s',
                'introduction_arabic_pdf_url' => 'pdf/Mjlsi-System-Arabic.pdf',
                'introduction_english_pdf_url' => 'pdf/Mjlsi-System-English.pdf',
                'third_pdf_url' => 'pdf/Mjlsi-System-English.pdf'
            ]
        ];

        foreach ($records as $key => $record) {
            $exists = DB::table('configrations')->where('id', $record['id'])->first();
            if (!$exists) {
                DB::table('configrations')->insert([$record]);
            }
        }
    }
}
