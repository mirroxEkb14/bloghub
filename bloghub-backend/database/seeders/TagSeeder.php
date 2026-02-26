<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    private const TAGS = [
        ['slug' => 'psychology', 'name' => 'Psychology'],
        ['slug' => 'conspiracy-theory', 'name' => 'Conspiracy theory'],
        ['slug' => 'true-crime', 'name' => 'True crime'],
        ['slug' => 'medicine', 'name' => 'Medicine'],
        ['slug' => 'science', 'name' => 'Science'],
        ['slug' => 'skepticism', 'name' => 'Skepticism'],
        ['slug' => 'physics', 'name' => 'Physics'],
        ['slug' => 'research', 'name' => 'Research'],
        ['slug' => 'healthcare', 'name' => 'Healthcare'],
        ['slug' => 'AI', 'name' => 'AI'],
        ['slug' => 'automation', 'name' => 'Automation'],
        ['slug' => 'space', 'name' => 'Space'],
        ['slug' => 'leadership', 'name' => 'Leadership'],
        ['slug' => 'survival', 'name' => 'Survival'],
        ['slug' => 'community', 'name' => 'Community'],
        ['slug' => 'sustainability', 'name' => 'Sustainability'],
        ['slug' => 'physical-education', 'name' => 'Physical education'],
        ['slug' => 'motivation', 'name' => 'Motivation'],
        ['slug' => 'music', 'name' => 'Music'],
        ['slug' => 'games', 'name' => 'Games'],
        ['slug' => 'cooking', 'name' => 'Cooking'],
        ['slug' => 'investment', 'name' => 'Investment'],
        ['slug' => 'language-learning', 'name' => 'Language learning'],
        ['slug' => 'photography', 'name' => 'Photography'],
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
