<?php

use Illuminate\Database\Seeder;

class EscolasSeeder extends Seeder
{
     private $contadorGlobal = 0;
    private $totaItems = 10;
    private $faker = null;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->line("");
        $this->command->line("##################################################################################");
        $this->command->line("Creating Schools");
        $this->command->line("##################################################################################");

        $this->faker = Faker\Factory::create('pt_PT');



        $this->addEscola($this->faker, 'escola basica Maia', 'Porto');
        $this->addEscola($this->faker, 'escola basica de Ilhavo', 'Aveiro');
        $this->addEscola($this->faker, 'escola basica de Setubal', 'Setúbal');
        $this->addEscola($this->faker, 'escola eb23 Porto', 'Porto');
        $this->addEscola($this->faker, 'escola eb23 Tondela', 'Viseu');
        $this->addEscola($this->faker, 'escola eb23 de Mirandela', 'Bragança');
        $this->addEscola($this->faker, 'escola eb23 Vila Nova de Milfontes', 'Beja');
        $this->addEscola($this->faker, 'escola secundaria de Abrantes', 'Santarém');
        $this->addEscola($this->faker, 'escola secundaria de Famalicao', 'Braga');
        $this->addEscola($this->faker, 'escola secundaria da Amadora', 'Lisboa');
    }


    private function addEscola(Faker\Generator $faker, $nome, $distrito)
    {
        $item = [
            'nome' => $nome,
            'distrito' => $distrito,
            ];
        $this->contadorGlobal ++;
        DB::table('escolas')->insert($item);
        $this->command->info("Created school {$this->contadorGlobal}/{$this->totaItems}: " . $item['nome']);
    }
}
