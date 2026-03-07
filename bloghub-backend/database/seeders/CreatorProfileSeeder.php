<?php

namespace Database\Seeders;

use App\Models\CreatorProfile;
use App\Models\Tag;
use App\Models\User;
use App\Support\CreatorProfileResourceSupport;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class CreatorProfileSeeder extends Seeder
{
    private const FIXTURES_BASE = 'database/seeders/fixtures/creator-profiles';

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private const PROFILES = [
        'Fox Mulder' => [
            'about' => 'Behavioral Science Unit (BSU). Oxford-educated psychologist and pioneer in serial killer profiling. Currently assigned to the Unsolved Case Files (X-Division)',
            'tag_slugs' => ['psychology', 'conspiracy-theory', 'true-crime'],
            'social' => [
                'facebook_url' => 'https://www.facebook.com/trust_no1',
                'youtube_url' => 'https://www.youtube.com/@trust_no1',
                'twitch_url' => 'https://www.twitch.tv/trust_no1',
                'website_url' => 'https://trustno1.example.com',
            ],
        ],
        'Dana Scully' => [
            'about' => 'Medical Doctor. FBI Special Agent. I\'m here to provide a scientific pivot for the unexplainable. The truth is out there, but it usually has a biological explanation',
            'tag_slugs' => ['medicine', 'science', 'skepticism'],
            'social' => [
                'instagram_url' => 'https://www.instagram.com/queequeg/',
                'facebook_url' => 'https://www.facebook.com/queequeg',
            ],
        ],
        'Gordon Freeman' => [
            'about' => 'Ph.D. from MIT in Theoretical Physics. Specialist in Anomalous Materials and Sub-surface Resonate Research. A Research Associate with focus on the Anti-Mass Spectrometer',
            'tag_slugs' => ['physics', 'science', 'research'],
            'social' => [
                'telegram_url' => 'https://t.me/blackmesa',
                'instagram_url' => 'https://www.instagram.com/blackmesa/',
                'facebook_url' => 'https://www.facebook.com/blackmesa',
                'youtube_url' => 'https://www.youtube.com/@blackmesa',
                'twitch_url' => 'https://www.twitch.tv/blackmesa',
                'website_url' => 'https://blackmesa.example.com',
            ],
        ],
        'Gregory House' => [
            'about' => 'Board-certified Diagnosticist and Nephrologist. My specialty is the anomaly—the symptoms that don\'t fit, the history that doesn\'t track, and the \'rare\' diseases that are usually just common idiocy',
            'tag_slugs' => ['healthcare', 'medicine'],
            'social' => [],
        ],
        'Caroline' => [
            'about' => 'Aperture Science Computer-Aided Enrichment Center provides world-class testing environments for the advancement of Science. Please ignore any sudden sensations of mortality. We do what we must because we can. For Science',
            'tag_slugs' => ['AI', 'automation'],
            'social' => [
                'telegram_url' => 'https://t.me/glados',
                'instagram_url' => 'https://www.instagram.com/glados/',
                'facebook_url' => 'https://www.facebook.com/glados',
                'youtube_url' => 'https://www.youtube.com/@glados',
                'twitch_url' => 'https://www.twitch.tv/glados',
                'website_url' => 'https://aperturescience.com',
            ],
        ],
        'Ellen Ripley' => [
            'about' => 'Warrant Officer on USCSS Nostromo. Sole survivor of the Nostromo incident. Dedicated to the documentation of "Special Order 937" and the atmospheric analysis of LV-426',
            'tag_slugs' => ['space', 'leadership', 'survival'],
            'social' => [
                'facebook_url' => 'https://www.facebook.com/nostromo',
                'telegram_url' => 'https://t.me/nostromo',
                'youtube_url' => 'https://www.youtube.com/@nostromo',
                'website_url' => 'https://nostromo.example.com',
            ],
        ],
        'Maggie Rhee' => [
            'about' => 'The end of the world was just the beginning of the work. For years, I\'ve walked through the mud, the blood, and the silence of a collapsed society. We don\'t just survive; we rebuild. Documenting the blueprint for the next world',
            'tag_slugs' => ['community', 'sustainability', 'leadership'],
            'social' => [
                'twitch_url' => 'https://www.twitch.tv/laurencohan',
                'website_url' => 'https://maggierhee.example.com',
            ],
        ],
        'Negan' => [
            'about' => 'People are a resource. That was the first rule. But resources are messy, they\'re emotional, and they\'re usually one bad day away from a mutiny',
            'tag_slugs' => ['physical-education', 'motivation'],
            'social' => [
                'youtube_url' => 'https://www.youtube.com/@jeffreydeanmorgan',
                'twitch_url' => 'https://www.twitch.tv/jeffreydeanmorgan',
                'website_url' => 'https://negan.example.com',
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::PROFILES as $userName => $data) {
            $user = User::where('name', $userName)->first();
            if (! $user) {
                $this->command->warn("User \"{$userName}\" not found, skipping creator profile");

                continue;
            }

            $maxAbout = CreatorProfileResourceSupport::ABOUT_MAX_LENGTH;
            $rawAbout = rtrim($data['about'], '.');
            $about = mb_strlen($rawAbout) > $maxAbout
                ? mb_substr($rawAbout, 0, $maxAbout - 3).'...'
                : $rawAbout;

            $displayName = $userName;
            $slug = CreatorProfile::uniqueSlugForDisplayName($displayName);

            $social = $data['social'] ?? [];
            $profile = CreatorProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'slug' => $slug,
                    'display_name' => $displayName,
                    'about' => $about,
                    'telegram_url' => $social['telegram_url'] ?? null,
                    'instagram_url' => $social['instagram_url'] ?? null,
                    'facebook_url' => $social['facebook_url'] ?? null,
                    'youtube_url' => $social['youtube_url'] ?? null,
                    'twitch_url' => $social['twitch_url'] ?? null,
                    'website_url' => $social['website_url'] ?? null,
                ]
            );

            if (array_key_exists('social', $data)) {
                $s = $data['social'];
                $profile->telegram_url = $s['telegram_url'] ?? null;
                $profile->instagram_url = $s['instagram_url'] ?? null;
                $profile->facebook_url = $s['facebook_url'] ?? null;
                $profile->youtube_url = $s['youtube_url'] ?? null;
                $profile->twitch_url = $s['twitch_url'] ?? null;
                $profile->website_url = $s['website_url'] ?? null;
            }

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

            $profile->created_at = Carbon::parse('2024-01-15')
                ->setTime(rand(6, 22), rand(0, 59), rand(0, 59));
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
