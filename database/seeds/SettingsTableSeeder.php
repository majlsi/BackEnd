<?php

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settingsList = [
            ['id' => 1, 'setting_key' => 'Introduction Video URL ', 'setting_key_ar' => 'رابط الفيديو التعريفي', 'setting_value' => 'https://www.youtube.com/embed/9fb1iVMfkRc'],
            ['id' => 2, 'setting_key' => 'Support Email', 'setting_key_ar' => 'البريد الإلكترونى للدعم الفني', 'setting_value' => 'eman.mohamed@enozom.com'],

        ];

        foreach ($settingsList as $key => $setting) {
            $exists = DB::table('settings')->where('id', $setting['id'])->first();
            if (!$exists) {
                DB::table('settings')->insert([$setting]);
            } else {
                DB::table('settings')
                    ->where('id', $setting['id'])
                    ->update(['setting_key' => $setting['setting_key'], 
                            'setting_key_ar' => $setting['setting_key_ar']
                        , 'setting_value' => $setting['setting_value']]);
            }
        }
    }
}
