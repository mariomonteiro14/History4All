<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(EscolasSeeder::class);
        $this->call(TurmasSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(PatrimoniosSeeder::class);
        $this->call(PatrimonioImagesSeeder::class);

    }
}
