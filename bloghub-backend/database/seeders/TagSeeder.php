<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    private const TAGS = [
        ['slug' => 'tech', 'name' => 'Tech'],
        ['slug' => 'backend', 'name' => 'Backend'],
        ['slug' => 'tutorials', 'name' => 'Tutorials'],
        ['slug' => 'productivity', 'name' => 'Productivity'],
        ['slug' => 'wildlife', 'name' => 'Wildlife'],
        ['slug' => 'personal-development', 'name' => 'Personal development'],
    ];

    public function run(): void
    {
        foreach (self::TAGS as $data) {
            Tag::firstOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name']]
            );
        }
    }
}
