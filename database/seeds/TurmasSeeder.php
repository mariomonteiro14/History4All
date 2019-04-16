<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TurmasSeeder extends Seeder
{
    private $contadorGlobal = 0;
    private $totalEscolas = 10;
    private $turmasPorEscolas = 3;
    private $faker = null;
    private $turmas = ['A', 'B', 'C'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->line("");
        $this->command->line("##################################################################################");
        $this->command->line("Creating Turmas");
        $this->command->line("##################################################################################");

        $this->faker = Faker\Factory::create('pt_PT');

        for ($i = 1; $i <= $this->totalEscolas; $i++) {

            $turma = array_rand($this->turmas, 1);
            for ($j = 1; $j <= $this->turmasPorEscolas; $j++) {
                $this->addTurma($this->faker, $this->turmas[$turma] . $j, $i);
            }

            if ($this->turmasPorEscolas < 7)
                $this->turmasPorEscolas++;
        }
    }


    private function addTurma(Faker\Generator $faker, $nome, $escola_id)
    {
        $item = [
            'nome' => $nome,
            'escola_id' => $escola_id,
        ];
        $this->contadorGlobal++;
        DB::table('turmas')->insert($item);
        $this->command->info("Created Turma {$this->contadorGlobal}/{$this->totalEscolas } *{ $this->turmasPorEscolas}: " . $item['nome']);
    }
}
