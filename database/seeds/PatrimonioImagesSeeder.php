<?php

use Illuminate\Database\Seeder;

class PatrimonioImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
	 private $contadorGlobal = 0;

    public function run()
    {
        $this->command->line("");
        $this->command->line("##################################################################################");
        $this->command->line("Creating Patrimonios_Imagens");
        $this->command->line("##################################################################################");

		$this->faker = Faker\Factory::create('pt_PT');

		$this->addPatrimonioImage($this->faker, 3, '0004.jpg');
		$this->addPatrimonioImage($this->faker, 3, '0005.jpg');
		$this->addPatrimonioImage($this->faker, 4, '0006.jpg');
		$this->addPatrimonioImage($this->faker, 4, '0007.jpg');
		$this->addPatrimonioImage($this->faker, 4, '0008.jpg');
		$this->addPatrimonioImage($this->faker, 5, '0009.jpg');
		$this->addPatrimonioImage($this->faker, 5, '0010.jpg');
		$this->addPatrimonioImage($this->faker, 5, '0011.jpg');
		$this->addPatrimonioImage($this->faker, 6, '0012.jpg');
		$this->addPatrimonioImage($this->faker, 6, '0013.jpg');
		$this->addPatrimonioImage($this->faker, 7, '0014.jpg');
		$this->addPatrimonioImage($this->faker, 7, '0015.jpg');
		$this->addPatrimonioImage($this->faker, 8, '0016.jpg');
		$this->addPatrimonioImage($this->faker, 8, '0017.jpg');
		$this->addPatrimonioImage($this->faker, 9, '0018.jpg');
		$this->addPatrimonioImage($this->faker, 9, '0019.jpg');
		$this->addPatrimonioImage($this->faker, 10, '0020.jpg');
		$this->addPatrimonioImage($this->faker, 10, '0021.jpg');
		$this->addPatrimonioImage($this->faker, 11, '0022.jpg');
		$this->addPatrimonioImage($this->faker, 11, '0023.jpg');
		$this->addPatrimonioImage($this->faker, 12, '0024.jpg');
		$this->addPatrimonioImage($this->faker, 13, '0026.jpg');
		$this->addPatrimonioImage($this->faker, 14, '0027.jpg');
		$this->addPatrimonioImage($this->faker, 15, '0028.jpg');
		$this->addPatrimonioImage($this->faker, 15, '0029.jpg');
		$this->addPatrimonioImage($this->faker, 16, '0030.jpg');
		$this->addPatrimonioImage($this->faker, 17, '0031.jpg');
		$this->addPatrimonioImage($this->faker, 18, '0032.jpg');
		$this->addPatrimonioImage($this->faker, 19, '0033.jpg');
		$this->addPatrimonioImage($this->faker, 20, '0034.jpg');
		$this->addPatrimonioImage($this->faker, 21, '0035.jpg');
		$this->addPatrimonioImage($this->faker, 21, '0036.jpg');
		$this->addPatrimonioImage($this->faker, 21, '0037.jpg');

    }

	private function addPatrimonioImage(Faker\Generator $faker, $id, $image)
    {
        $item = [
            'patrimonio_id' => $id,
            'foto' => $image,
            ];
        DB::table('patrimonio_imagens')->insert($item);
        //$this->command->info("Created Patrimonio_image {$this->contadorGlobal}: " . $item['id'] ."/". $item['foto]);
    }
}
