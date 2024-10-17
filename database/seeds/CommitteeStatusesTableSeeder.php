<?php



use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommitteeStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'id' => 1,
                'committee_status_name_ar' => 'معلق',
                'committee_status_name_en' => 'Pending',
            ],
            [
                'id' => 2,
                'committee_status_name_ar' => 'جاري العمل',
                'committee_status_name_en' => 'In Progress',
            ],
            [
                'id' => 3,
                'committee_status_name_ar' => 'تم الاصدار',
                'committee_status_name_en' => 'Accepted',
            ],
            [
                'id' => 4,
                'committee_status_name_ar' => 'المخرج النهائي متأخر',
                'committee_status_name_en' => 'Final Document Pending',
            ],
            [
                'id' => 5,
                'committee_status_name_ar' => 'مغلقة',
                'committee_status_name_en' => 'Closed',
            ]
        ];

        foreach ($statuses as $key => $status) {
            $exists = DB::table('committee_statuses')->where('id', $status['id'])->first();
            if (!$exists) {
                DB::table('committee_statuses')->insert([$status]);
            }
        }
    }
}
