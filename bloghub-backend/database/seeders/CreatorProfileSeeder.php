<?php

namespace Database\Seeders;

use App\Models\CreatorProfile;
use App\Models\Tag;
use App\Models\User;
use App\Support\CreatorProfileResourceSupport;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class CreatorProfileSeeder extends Seeder
{
    private const FIXTURES_BASE = 'database/seeders/fixtures/creator-profiles';

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private const PROFILES = [
        'Fox Mulder' => [
            'about' => 'Criminal psychology specialist focused on behavioral profiling and unexplained cases. I explore patterns behind extreme crimes, conspiracy narratives, and the psychology of belief. Deep dives, case breakdowns, and analytical commentary',
            'tag_slugs' => ['psychology', 'conspiracy-theory', 'true-crime'],
        ],
        'Dana Scully' => [
            'about' => 'Medical Doctor. FBI Special Agent. I\'m here to provide a scientific pivot for the unexplainable. The truth is out there, but it usually has a biological explanation',
            'tag_slugs' => ['medicine', 'science', 'skepticism'],
        ],
        'Gordon Freeman' => [
            'about' => 'Theoretical physicist discussing quantum theory, anomalous phenomena, and high-risk experimental science. Content covers physics concepts, research ethics, and speculative science scenarios',
            'tag_slugs' => ['physics', 'science', 'research'],
        ],
        'Gregory House' => [
            'about' => 'Diagnostic medicine specialist breaking down rare diseases, complex symptoms, and medical reasoning. Analytical case studies with a focus on logic, misdiagnosis, and unconventional thinking',
            'tag_slugs' => ['healthcare', 'medicine'],
        ],
        'Caroline' => [
            'about' => 'Aperture Science Computer-Aided Enrichment Center provides world-class testing environments for the advancement of Science. Please ignore any sudden sensations of mortality. We do what we must because we can. For Science',
            'tag_slugs' => ['AI', 'automation'],
        ],
        'Ellen Ripley' => [
            'about' => 'Warrant Officer on USCSS Nostromo. Sole survivor of the Nostromo incident. Dedicated to the documentation of "Special Order 937" and the atmospheric analysis of LV-426',
            'tag_slugs' => ['space', 'leadership', 'survival'],
        ],
        'Maggie Rhee' => [
            'about' => 'Community builder and survival strategist focused on resilience, leadership under pressure, and rebuilding systems after crisis. Lessons on cooperation, agriculture basics, and sustainable communities',
            'tag_slugs' => ['community', 'sustainability', 'leadership'],
        ],
        'Negan' => [
            'about' => 'Former physical education teacher exploring discipline, group dynamics, and authority structures. Content mixes motivational leadership, behavioral control theory, and physical training insights',
            'tag_slugs' => ['physical-education', 'motivation'],
        ],
    ];

    public function run(): void
    {
        foreach (self::PROFILES as $userName => $data) {
            $user = User::where('name', $userName)->first();
            if (! $user) {
                $this->command->warn("User \"{$userName}\" not found, skipping creator profile.");

                continue;
            }

            $maxAbout = CreatorProfileResourceSupport::ABOUT_MAX_LENGTH;
            $rawAbout = rtrim($data['about'], '.');
            $about = mb_strlen($rawAbout) > $maxAbout
                ? mb_substr($rawAbout, 0, $maxAbout - 3).'...'
                : $rawAbout;

            $displayName = $userName;
            $slug = CreatorProfile::uniqueSlugForDisplayName($displayName);

            $profile = CreatorProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'slug' => $slug,
                    'display_name' => $displayName,
                    'about' => $about,
                ]
            );

            $basePath = base_path(self::FIXTURES_BASE);
            $avatarPath = $this->findFixtureFile($basePath.'/avatars', $user->username.'_avatar');
            if ($avatarPath !== null) {
                $stored = Storage::disk('public')->putFile(
                    CreatorProfileResourceSupport::AVATAR_DIRECTORY,
                    new File($avatarPath)
                );
                $profile->profile_avatar_path = $stored;
            }

            $coverPath = $this->findFixtureFile($basePath.'/covers', $user->username.'_cover');
            if ($coverPath !== null) {
                $stored = Storage::disk('public')->putFile(
                    CreatorProfileResourceSupport::COVER_DIRECTORY,
                    new File($coverPath)
                );
                $profile->profile_cover_path = $stored;
            }

            $profile->save();

            $tagIds = Tag::whereIn('slug', $data['tag_slugs'])->pluck('id');
            $profile->tags()->sync($tagIds);
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
