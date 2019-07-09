<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatProfessoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->line("");
        $this->command->line("##################################################################################");
        $this->command->line("Creating Chat");
        $this->command->line("##################################################################################");

        $this->faker = Faker\Factory::create('pt_PT');

        $item = [
            'id' => 1,
            'privado' => 1,
            'assunto' => 'chat entre todos os professores registados no site'
        ];
        DB::table('chats')->insert($item);
    }
}
