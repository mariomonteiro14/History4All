<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */

    private $photoPath = 'public/profiles';
    private $typesOfUsers = ['admin', 'professor', 'aluno'];
    private $numberOfUsers = [5, 10, 150];
    private $numberOfSoftDeletedUsers = [1, 5, 10];
    private $typesOfUsersPrefix = ['a', 'p', 'e'];
    private $malePhotos = [];
    private $femalePhotos = [];

    public function run()
    {
        /*$this->call(EscolasSeeder::class);
        $this->call(TurmasSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(PatrimoniosSeeder::class);
        $this->call(PatrimonioImagesSeeder::class);*/

        $this->command->line("");
        $this->command->line("##################################################################################");
        $this->command->line("Creating Users");
        $this->command->line("##################################################################################");

        $totalUsers = 0;
        $contadorGlobal = 0;
        for ($typeOfUserIdx = 0; $typeOfUserIdx < 3; $typeOfUserIdx++) {
            $totalUsers += $this->numberOfUsers[$typeOfUserIdx];
            $totalUsers += $this->numberOfSoftDeletedUsers[$typeOfUserIdx];
        }
        $this->command->table(['Users table seeder notice'], [
            ['Profile photos will be stored on path ' . storage_path('app/' . $this->photoPath)],
            ['Edit UsersSeeder.php file to change the storage path or the number of users']
        ]);


        Storage::deleteDirectory($this->photoPath);
        Storage::makeDirectory($this->photoPath);
        $faker = Faker\Factory::create('pt_PT');
        $this->files = collect(File::files(database_path('seeds/profile_photos')));
        $this->malePhotos = [];
        $this->femalePhotos = [];
        foreach ($this->files as $fileInfo) {
            $filename = $fileInfo->getFilename();
            if (strpos($filename, "M_") === 0) {
                $this->malePhotos[] = $filename;
            } else if (strpos($filename, "W_") === 0) {
                $this->femalePhotos[] = $filename;
            }
        }
        $escola_prof_id = 1;
        $count_escola_prof = 0;
        $escola_aluno_id = 1;
        $count_escola_aluno = 0;
        $turma_id = null;
        $count_turma_id = 0;

        for ($typeOfUserIdx = 0; $typeOfUserIdx < 3; $typeOfUserIdx++) {
            for ($i = 0; $i < $this->numberOfUsers[$typeOfUserIdx]; $i++) {
                $contadorGlobal++;

                if ($typeOfUserIdx == 0) {
                    $user = $this->fakeUser($faker, $typeOfUserIdx, null, null, $i);
                } else if ($typeOfUserIdx == 1) {
                    $user = $this->fakeUser($faker, $typeOfUserIdx, $escola_prof_id, null, $i);
                    $count_escola_prof++;
                    if ($count_escola_prof == 5) {
                        $count_escola_prof = 0;
                        $escola_prof_id++;
                    }
                } else {
                    $user = $this->fakeUser($faker, $typeOfUserIdx, $escola_aluno_id, $turma_id, $i);
                    $count_escola_aluno++;
                    $count_turma_id++;

                    if ($count_escola_aluno == 50) {
                        $escola_aluno_id++;
                        $count_escola_aluno = 0;
                        /*  $turma_id=1;
                          $count_turma_id=0;*/
                    }
                    /*
                                        if ($count_turma_id%15 == 0){
                                            $turma_id++;
                                            $count_turma_id =0;
                                        }
                                        if (50 - $count_turma_id == 5){
                                            $turma_id=null;
                                        }*/

                }
                DB::table('users')->insert($user);
                //$this->command->info("Created User $contadorGlobal/$totalUsers: " . $user['nome']);
            }
        }
    }

    private function copyProfilePhoto($filename)
    {
        $targetDir = storage_path('app/' . $this->photoPath);
        $newFileName = str_random(16) . ".jpg";
        File::copy(database_path('seeds/profile_photos') . "/$filename", $targetDir . '/' . $newFileName);
        return $newFileName;
    }

    private function fakeUser(Faker\Generator $faker, $typeOfUserIdx, $escola_id, $turma_id, $idx, $softDelete = false)
    {
        $createdAt = Carbon\Carbon::now()->subDays(600);
        $gender = $faker->randomElements(['male', 'female'])[0];
        if ($gender == 'male') {
            $filename = $faker->randomElements($this->malePhotos)[0];
            DatabaseSeeder::deleteArrayElement($filename, $this->malePhotos);
        } else {
            $filename = $faker->randomElements($this->femalePhotos)[0];
            DatabaseSeeder::deleteArrayElement($filename, $this->femalePhotos);
        }
        $newProfileFileName = $this->copyProfilePhoto($filename);
        return [
            'nome' => $idx == 0 ? 'First ' . $this->typesOfUsers[$typeOfUserIdx] : $faker->name($gender),
            'email' => $this->typesOfUsersPrefix[$typeOfUserIdx] . $idx . '@mail.pt',
            'email_verified_at' => $faker->dateTimeBetween($createdAt),
            'password' => bcrypt('123'),
            'tipo' => $this->typesOfUsers[$typeOfUserIdx],
            'escola_id' => $escola_id,
            'turma_id' => $turma_id,
            'foto' => $newProfileFileName,
            'remember_token' => str_random(10),
            'created_at' => $createdAt,
            'updated_at' => $faker->dateTimeBetween($createdAt),
            'deleted_at' => $softDelete ? $faker->dateTimeBetween($createdAt) : null,
        ];
    }

    private static function deleteArrayElement($element, &$array)
    {
        $index = array_search($element, $array);
        if ($index !== false) {
            unset($array[$index]);
        }
    }
}
