<?php

use Illuminate\Database\Seeder;

class FileTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fileTypesData = [ 
        [
            'id' => '1',
            'file_type_ext' => 'jpeg',
            'file_type_icon' => './assets/app/media/img/files/jpg.svg' 
        ],
        [
            'id' => '2',
            'file_type_ext' => 'jpg',
            'file_type_icon' => './assets/app/media/img/files/jpg.svg' 
        ],
        [
            'id' => '3',
            'file_type_ext' => 'png',
            'file_type_icon' => './assets/app/media/img/files/jpg.svg' 
        ],
        [
            'id' => '4',
            'file_type_ext' => 'pdf',
            'file_type_icon' => './assets/app/media/img/files/pdf.svg' 
        ],
        [
            'id' => '5',
            'file_type_ext' => 'txt',
            'file_type_icon' => './assets/app/media/img/files/doc.svg' 
        ],
        [
            'id' => '6',
            'file_type_ext' => 'doc',
            'file_type_icon' => './assets/app/media/img/files/doc.svg' 
        ],
        [
            'id' => '7',
            'file_type_ext' => 'docx',
            'file_type_icon' => './assets/app/media/img/files/doc.svg' 
        ],
        [
            'id' => '8',
            'file_type_ext' => 'odt',
            'file_type_icon' => './assets/app/media/img/files/doc.svg' 
        ],
        [
            'id' => '9',
            'file_type_ext' => 'rtf',
            'file_type_icon' => './assets/app/media/img/files/doc.svg' 
        ],
        [
            'id' => '10',
            'file_type_ext' => 'xls',
            'file_type_icon' => './assets/app/media/img/files/xls.svg' 
        ],
        [
            'id' => '11',
            'file_type_ext' => 'xlsx',
            'file_type_icon' => './assets/app/media/img/files/xls.svg' 
        ],
        [
            'id' => '12',
            'file_type_ext' => 'ppt',
            'file_type_icon' => './assets/app/media/img/files/ppt.svg' 
        ],
        [
            'id' => '13',
            'file_type_ext' => 'pptx',
            'file_type_icon' => './assets/app/media/img/files/ppt.svg'
        ],
        [
            'id' => '14',
            'file_type_ext' => 'avi',
            'file_type_icon' => './assets/app/media/img/files/mp4.svg'
        ],
        [
            'id' => '15',
            'file_type_ext' => 'mov',
            'file_type_icon' => './assets/app/media/img/files/mp4.svg'
        ],
        [
            'id' => '16',
            'file_type_ext' => 'mp4',
            'file_type_icon' => './assets/app/media/img/files/mp4.svg'
        ],
        [
            'id' => '17',
            'file_type_ext' => 'wmv',
            'file_type_icon' => './assets/app/media/img/files/mp4.svg'
        ],
    ];

        foreach ($fileTypesData as $key => $fileType) {
            $exists = DB::table('file_types')->where('id', $fileType['id'])->first();
            if(!$exists){
                DB::table('file_types')->insert([$fileType]);    
            }
        }
    }
}        