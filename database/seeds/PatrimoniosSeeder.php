<?php

use Illuminate\Database\Seeder;

class PatrimoniosSeeder extends Seeder
{
     private $contadorGlobal = 0;
    private $totaItems = 18;
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
        $this->command->line("Creating Patrimonios");
        $this->command->line("##################################################################################");

        $this->faker = Faker\Factory::create('pt_PT');

        $this->addPatrimonio($this->faker, 'Torre de belem', 'Lisboa', 'idade média', '1º ciclo');
        $this->addPatrimonio($this->faker, 'Universidade de Coimbra', 'Coimbra', 'idade contemporanea', 'secundário');
        $this->addPatrimonio($this->faker, 'Castelo de Guimarães', 'Braga', 'idade antiga', '1º ciclo');
        $this->addPatrimonio($this->faker, 'Alto Douro Vinhateiro', 'Porto', 'pre-historia', '1º ciclo');
        $this->addPatrimonio($this->faker, 'Convento de Cristo', 'Leiria', 'idade media', '2º ciclo');
        $this->addPatrimonio($this->faker, 'Mosteiro de Alcobaça', 'Leiria', 'idade media', '2º ciclo');
        $this->addPatrimonio($this->faker, 'Mosteiro da Batalha', 'Leiria', 'idade media', 'secundário');
        $this->addPatrimonio($this->faker, 'Mosteiro dos Jeronimos', 'Lisboa', 'idade media', '3º ciclo');
        $this->addPatrimonio($this->faker, 'Convento de Mafra', 'Lisboa', 'idade media', '2º ciclo');
        $this->addPatrimonio($this->faker, 'Templo de Evora', 'Evora', 'idade antiga', '1º ciclo');
        $this->addPatrimonio($this->faker, 'Serra da Arrabida', 'Setubal', 'pre-historia', '1º ciclo');
        $this->addPatrimonio($this->faker, 'Paisagem Cultural de Sintra', 'Lisboa', 'idade média', '2º ciclo');
        $this->addPatrimonio($this->faker, 'Paisagem da Cultura da Vinha da Ilha do Pico', 'Açores', 'pre-historia', '3º ciclo');
        $this->addPatrimonio($this->faker, 'Laurissilva da Madeira', 'Madeira', 'pre-historia', 'secundário');
        $this->addPatrimonio($this->faker, 'Centro Histórico de Santarém', 'Santarem', 'idade média', '1º ciclo');
        $this->addPatrimonio($this->faker, 'Algar do Carvão', 'Açores', 'pre-historia', '1º ciclo');
        $this->addPatrimonio($this->faker, 'Vila de Marvão ', 'Portalegre', 'idade antiga', 'secundário');
        $this->addPatrimonio($this->faker, 'Icnitos de Dinossauros', 'Setubal', 'pre-historia', '3º ciclo');
        $this->addPatrimonio($this->faker, 'Santuário do Bom Jesus do Monte', 'Braga', 'idade média', '2º ciclo');
         }


    private function addPatrimonio(Faker\Generator $faker, $nome, $distrito, $epoca, $ciclo)
    {
        $item = [
            'nome' => $nome,
            'descricao' => $faker->realText(200),
            'distrito' => $distrito,
            'epoca' => $epoca,
            'ciclo' => $ciclo,
            ];
        $this->contadorGlobal ++;
        DB::table('patrimonios')->insert($item);
        $this->command->info("Created Patrimonio {$this->contadorGlobal}/{$this->totaItems}: " . $item['nome']);
    }
}
