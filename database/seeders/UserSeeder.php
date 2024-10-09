<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();


        foreach (range(1, 10) as $index) {

            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $email = $faker->unique()->safeEmail;
            $age = $faker->numberBetween(18, 80);
            $password = Hash::make('password123');

            $picturePath = $faker->image('storage/app/public/profile_pictures', 400, 300, 'people', false);

            User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'age' => $age,
                'picture_path' => $picturePath,
                'password' => $password,
            ]);
        }

        $this->command->info('10 utilisateurs ont été insérés avec succès !');
    }
}
