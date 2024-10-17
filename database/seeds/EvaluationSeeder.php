<?php


use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $evaluationsData = [
            ['evaluation_name_en' => 'low', 'evaluation_name_ar' => 'منخفض'],
            ['evaluation_name_en' => 'medium', 'evaluation_name_ar' => 'متوسط'],
            ['evaluation_name_en' => 'high', 'evaluation_name_ar' => 'مرتفع'],
        ];
        foreach ($evaluationsData as $key => $evaluation) {
            $exists = DB::table('evaluations')->where('evaluation_name_ar', $evaluation['evaluation_name_ar'])
                ->where('evaluation_name_en', $evaluation['evaluation_name_en'])->first();
            if (!$exists) {
                DB::table('evaluations')->insert([$evaluation]);
            }
        }
    }
}
