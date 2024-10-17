<?php

use Illuminate\Database\Seeder;

class RecommendationStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $statuses = [
            [
                'id' => 1,
                'recommendation_status_name_ar' => 'جارى العمل',
                'recommendation_status_name_en' => 'In progress',
            ],
            [
                'id' => 2,
                'recommendation_status_name_ar' => 'منجزة‌‌',
                'recommendation_status_name_en' => 'Completed',
            ],
            [
                'id' => 3,
                'recommendation_status_name_ar' => 'معلقة',
                'recommendation_status_name_en' => 'Suspended',
            ]
        ];

        foreach ($statuses as $key => $status) {
            $exists = DB::table('recommendation_status')->where('id', $status['id'])->first();
            if (!$exists) {
                DB::table('recommendation_status')->insert([$status]);
            }
        }
    }
}
