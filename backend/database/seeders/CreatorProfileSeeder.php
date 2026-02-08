<?php

namespace Database\Seeders;

use App\Models\CreatorProfile;
use App\Models\User;
use App\Support\CreatorProfileResourceSupport;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class CreatorProfileSeeder extends Seeder
{
    private const FIXTURES_BASE = 'database/seeders/fixtures/creator-profiles';

    private const AVATAR_NAMES = [
        'creator-profile_avatar-1',
        'creator-profile_avatar-2',
        'creator-profile_avatar-3',
    ];

    private const COVER_NAMES = [
        'creator-profile_cover-1',
        'creator-profile_cover-2',
        'creator-profile_cover-3',
    ];

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private const PROFILES = [
        [
            'display_name' => 'Repus Nimda',
            'about' => 'Repus Nimda is an independent digital creator focused on technical tutorials, backend development, and system design. He shares practical examples from real projects, explains application architecture, and helps developers write clean, sustainable code — without unnecessary theory.',
            'user_name' => 'Super Admin',
        ],
        [
            'display_name' => 'Resu',
            'about' => 'Resu is a creative author focused on personal development, productivity, and working with ideas. He shares thoughts on creation, habits, and long-term motivation, blending structure with creativity and space for reflection in the digital world.',
            'user_name' => 'User',
        ],
        [
            'display_name' => 'Urban Faune',
            'about' => 'Urban Fauna documents animals in human-made environments. From zoos to conservation parks, the creator shares knowledge, impressions, and curiosity-driven content about wildlife and coexistence.',
            'user_name' => 'Admin',
        ],
    ];

    public function run(): void
    {
        $basePath = base_path(self::FIXTURES_BASE);

        foreach (self::PROFILES as $index => $data) {
            $user = User::where('name', $data['user_name'])->first();
            if (! $user) {
                $this->command->warn("User \"{$data['user_name']}\" not found, skipping creator profile \"{$data['display_name']}\".");

                continue;
            }

            $maxAbout = CreatorProfileResourceSupport::ABOUT_MAX_LENGTH;
            $about = mb_strlen($data['about']) > $maxAbout
                ? mb_substr($data['about'], 0, $maxAbout - 3).'...'
                : $data['about'];

            $slug = CreatorProfile::uniqueSlugForDisplayName($data['display_name'], null);

            $profile = CreatorProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'slug' => $slug,
                    'display_name' => $data['display_name'],
                    'about' => $about,
                ]
            );

            $avatarPath = $this->findFixtureFile($basePath.'/avatars', self::AVATAR_NAMES[$index] ?? null);
            if ($avatarPath !== null) {
                $stored = Storage::disk('public')->putFile(
                    CreatorProfileResourceSupport::AVATAR_DIRECTORY,
                    new File($avatarPath)
                );
                $profile->profile_avatar_path = $stored;
            }

            $coverPath = $this->findFixtureFile($basePath.'/covers', self::COVER_NAMES[$index] ?? null);
            if ($coverPath !== null) {
                $stored = Storage::disk('public')->putFile(
                    CreatorProfileResourceSupport::COVER_DIRECTORY,
                    new File($coverPath)
                );
                $profile->profile_cover_path = $stored;
            }

            $profile->save();
        }
    }

    private function findFixtureFile(string $directory, ?string $baseName): ?string
    {
        if ($baseName === null || $baseName === '') {
            return null;
        }

        foreach (self::EXTENSIONS as $ext) {
            $path = $directory.DIRECTORY_SEPARATOR.$baseName.'.'.$ext;
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
