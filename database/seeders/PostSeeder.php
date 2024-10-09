<?php

namespace Database\Seeders;

use App\Models\Post;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $index) {
            $title = $faker->sentence;
            $slug = Str::slug($title);

            $originalSlug = $slug;
            $counter = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $imagePath = null;

            if ($faker->boolean(50)) {
                $imagePath = 'images/' . Str::random(10) . '.jpg';
                Storage::put($imagePath, 'fake image content');
            }

            DB::table('posts')->insert([
                'title' => $title,
                'slug' => $slug,
                'content' => $faker->paragraph,
                'image_path' => $imagePath,
                'created_at' => now(),
                'last_update' => now(),
            ]);
        }

        $this->command->info('10 posts ont été insérés avec succès !');
    }
}
