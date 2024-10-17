<?php
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommitteeNaturesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'id'=>1,
                'committee_nature_name_ar' => 'داخلية',
                'committee_nature_name_en' => 'Internal',
            ],
            [
                'id'=>2,
                'committee_nature_name_ar' => ' خارجية',
                'committee_nature_name_en' => 'External',
            ],
        ];

        foreach ($types as $key => $type) {
            $exists = DB::table('committee_natures')->where('id', $type['id'])->first();
            if(!$exists){
                DB::table('committee_natures')->insert([$type]);    
            }
        }
    }
}
