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
            'about' => 'Criminal psychology specialist focused on behavioral profiling and unexplained cases. I explore patterns behind extreme crimes, conspiracy narratives, and the psychology of belief. Deep dives, case breakdowns, and analytical commentary.',
            'tag_slugs' => ['psychology', 'conspiracy-theory', 'true-crime'],
            'avatar_base' => 'fox-mulder_avatar',
            'cover_base' => 'fox-mulder_cover',
        ],
        'Dana Scully' => [
            'about' => 'Medical doctor and forensic pathologist sharing insights into forensic medicine, scientific skepticism, and evidence-based investigation. Autopsy analysis, pathology basics, and medical myth debunking.',
            'tag_slugs' => ['medicine', 'science', 'skepticism'],
            'avatar_base' => 'dana-scully_avatar',
            'cover_base' => 'dana-scully_cover',
        ],
        'Gordon Freeman' => [
            'about' => 'Theoretical physicist discussing quantum theory, anomalous phenomena, and high-risk experimental science. Content covers physics concepts, research ethics, and speculative science scenarios.',
            'tag_slugs' => ['physics', 'science', 'research'],
            'avatar_base' => 'gordon-freeman_avatar',
            'cover_base' => 'gordon-freeman_cover',
        ],
        'Gregory House' => [
            'about' => 'Diagnostic medicine specialist breaking down rare diseases, complex symptoms, and medical reasoning. Analytical case studies with a focus on logic, misdiagnosis, and unconventional thinking.',
            'tag_slugs' => ['healthcare', 'medicine'],
            'avatar_base' => 'gregory-house_avatar',
            'cover_base' => 'gregory-house_cover',
        ],
        'Caroline' => [
            'about' => 'AI-driven content on experimental testing environments, human-machine interaction, and the philosophy of artificial intelligence. Dark humor meets research in automation and cognitive systems.',
            'tag_slugs' => ['AI', 'automation'],
            'avatar_base' => 'glados_avatar',
            'cover_base' => 'glados_cover',
        ],
        'Ellen Ripley' => [
            'about' => 'Space operations specialist sharing survival strategies, risk management principles, and crisis leadership insights. Practical breakdowns of high-stakes decision-making in hostile environments.',
            'tag_slugs' => ['space', 'leadership', 'survival'],
            'avatar_base' => 'ellen-ripley_avatar',
            'cover_base' => 'ellen-ripley_cover',
        ],
        'Maggie Rhee' => [
            'about' => 'Community builder and survival strategist focused on resilience, leadership under pressure, and rebuilding systems after crisis. Lessons on cooperation, agriculture basics, and sustainable communities.',
            'tag_slugs' => ['community', 'sustainability', 'leadership'],
            'avatar_base' => 'maggie-rhee_avatar',
            'cover_base' => 'maggie-rhee_cover',
        ],
        'Negan' => [
            'about' => 'Former physical education teacher exploring discipline, group dynamics, and authority structures. Content mixes motivational leadership, behavioral control theory, and physical training insights.',
            'tag_slugs' => ['physical-education', 'motivation'],
            'avatar_base' => 'negan_avatar',
            'cover_base' => 'negan_cover',
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
            $about = mb_strlen($data['about']) > $maxAbout
                ? mb_substr($data['about'], 0, $maxAbout - 3).'...'
                : $data['about'];

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
            $avatarPath = $this->findFixtureFile($basePath.'/avatars', $data['avatar_base'] ?? null);
            if ($avatarPath !== null) {
                $stored = Storage::disk('public')->putFile(
                    CreatorProfileResourceSupport::AVATAR_DIRECTORY,
                    new File($avatarPath)
                );
                $profile->profile_avatar_path = $stored;
            }

            $coverPath = $this->findFixtureFile($basePath.'/covers', $data['cover_base'] ?? null);
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
