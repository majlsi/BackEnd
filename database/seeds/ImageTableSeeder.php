<?php

use Illuminate\Database\Seeder;

class ImageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('images')->where('id', 1)->first();

        if (!$exists) {
            DB::table('images')->insert([
                ['id' => 1, 'original_image_url' => 'img/logo_large.png', 'image_url' => 'img/logo_large.png'],
            ]);
        }
    }
}
