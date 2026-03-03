<?php

namespace Database\Seeders;

use App\Enums\Currency;
use App\Models\CreatorProfile;
use App\Models\User;
use App\Support\TierResourceSupport;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class TierSeeder extends Seeder
{
    private const FIXTURES_COVERS = 'database/seeders/fixtures/tiers/covers';

    private const COVER_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];

    private const TIERS_BY_USER = [
        'Fox Mulder' => [
            ['level' => 1, 'tier_name' => 'The Believer', 'price' => 5, 'currency' => Currency::USD, 'tier_desc' => 'The Believer: Breakdowns of UFO sightings, Declassified reports from Nevada desert', 'cover_base' => 'The-Believer_cover'],
            ['level' => 2, 'tier_name' => 'The Abductee', 'price' => 15, 'currency' => Currency::USD, 'tier_desc' => 'The Abductee: Diagrams of the "nasal implants" (Smallpox)', 'cover_base' => 'Behavioral-Insider_cover'],
            ['level' => 3, 'tier_name' => 'The Conspirator', 'price' => 120, 'currency' => Currency::USD, 'tier_desc' => 'The Conspirator: Black Oil deep-dive, Profiles on "First Elders", Uncensored documents on Colonists', 'cover_base' => 'X-Archive-Elite_cover'],
        ],
        'Dana Scully' => [
            ['level' => 1, 'tier_name' => 'Agent', 'price' => 4, 'currency' => Currency::USD, 'tier_desc' => 'Field Agent: Insights on solved X-Files cases, Mysterious UFOs explanations', 'cover_base' => 'Clinical-Observer_cover'],
            ['level' => 2, 'tier_name' => 'Investigator', 'price' => 12, 'currency' => Currency::USD, 'tier_desc' => 'Senior Investigator: Breakdowns of "paranormal" unsolved cases', 'cover_base' => 'Forensic-Analyst_cover'],
            ['level' => 3, 'tier_name' => 'FBI Liaison', 'price' => 170, 'currency' => Currency::USD, 'tier_desc' => 'FBI Liaison: Concepts for the "The Syndicate" and government cover-ups, Briefings on "The Great Conspiracy"', 'cover_base' => 'Evidence-Council_cover'],
        ],
        'Gordon Freeman' => [
            ['level' => 1, 'tier_name' => 'Research Associate (Clearance 3)', 'price' => 6, 'currency' => Currency::EUR, 'tier_desc' => 'Research Associate (Clearance 3): GG-3883 (Xen) crystal lab notes, Anti-Mass Spectrometer insights, H.E.V. mark IV diagrams', 'cover_base' => 'Lab-Access_cover'],
            ['level' => 2, 'tier_name' => 'The Anti-Citizen', 'price' => 18, 'currency' => Currency::EUR, 'tier_desc' => 'The Anti-Citizen: Gravity Gun usage guide, Combine tech insights, Headcrab & Gonarch lab reports', 'cover_base' => 'Resonance-Member_cover'],
            ['level' => 3, 'tier_name' => 'The One Free Man', 'price' => 200, 'currency' => Currency::EUR, 'tier_desc' => 'The One Free Man: G-Man\'s non-linear "Slow-Teleport" phenomenon observations, Contact reports with Vortigaunt', 'cover_base' => 'Black-Mesa-Patron_cover'],
        ],
        'Gregory House' => [
            ['level' => 1, 'tier_name' => 'The Placebo', 'price' => 7, 'currency' => Currency::USD, 'tier_desc' => 'The Placebo: TBA', 'cover_base' => 'Differential-Thinker_cover'],
            ['level' => 2, 'tier_name' => 'The Pathogen', 'price' => 31, 'currency' => Currency::USD, 'tier_desc' => 'The Pathogen: TBA', 'cover_base' => 'Diagnostic-Team_cover'],
            ['level' => 3, 'tier_name' => 'The Cure', 'price' => 1101, 'currency' => Currency::USD, 'tier_desc' => 'The Cure: TBA', 'cover_base' => 'Princeton-Plainsboro _Inner-Circle_cover'],
        ],
        'Caroline' => [
            ['level' => 1, 'tier_name' => 'Test Subject #1498', 'price' => 199, 'currency' => Currency::CZK, 'tier_desc' => 'Test Subject #1498: The Companion Cube, Aperture Science Handheld Portal Device, The Cake', 'cover_base' => 'Test-Subject_cover'],
            ['level' => 2, 'tier_name' => 'Maintenance Specialist (Level 4)', 'price' => 1399, 'currency' => Currency::CZK, 'tier_desc' => 'Maintenance Specialist (Level 4): Aperture Science Long Fall Boots, The Curiosity Core', 'cover_base' => 'Advanced-Prototype_cover'],
            ['level' => 3, 'tier_name' => 'Central AI Overseer', 'price' => 5299, 'currency' => Currency::CZK, 'tier_desc' => 'Central AI Overseer: Atlas & P-Body Blueprints, Neurotoxin Delivery Controls, The Morality Core', 'cover_base' => 'Aperture-Core-Member_cover'],
        ],
        'Ellen Ripley' => [
            ['level' => 1, 'tier_name' => 'Sole Survivor', 'price' => 99, 'currency' => Currency::CZK, 'tier_desc' => 'Sole Survivor: Insights on the Nostromo\'s flight path, LV-426 landing reports', 'cover_base' => 'Crew-Member_cover'],
            ['level' => 2, 'tier_name' => 'The Captain', 'price' => 499, 'currency' => Currency::CZK, 'tier_desc' => 'The Captain: Breakdowns of the "Facehugger" anatomy, Reconstruction of Nostromo crew\'s fate, Reports on Weyland-Yutani ROs', 'cover_base' => 'Flight-Officer_cover'],
            ['level' => 3, 'tier_name' => 'The Alien', 'price' => 1999, 'currency' => Currency::CZK, 'tier_desc' => 'The Alien: Breakdowns of the XX121 organism', 'cover_base' => 'Command-Authority_cover'],
        ],
        'Maggie Rhee' => [
            ['level' => 1, 'tier_name' => 'Community Supporter', 'price' => 3, 'currency' => Currency::USD, 'tier_desc' => 'Leadership insights. Sustainable community guides', 'cover_base' => 'Community-Supporter_cover'],
            ['level' => 2, 'tier_name' => 'Settlement Builder', 'price' => 11, 'currency' => Currency::USD, 'tier_desc' => 'Advanced agriculture strategies', 'cover_base' => 'Settlement-Builder_cover'],
            ['level' => 3, 'tier_name' => 'Council Member', 'price' => 19, 'currency' => Currency::USD, 'tier_desc' => 'Private community planning sessions', 'cover_base' => 'Council-Member_cover'],
        ],
        'Negan' => [
            ['level' => 1, 'tier_name' => 'Rookie', 'price' => 13, 'currency' => Currency::EUR, 'tier_desc' => 'Training routines. Discipline challenges', 'cover_base' => 'Rookie_cover'],
            ['level' => 2, 'tier_name' => 'Field Leader', 'price' => 47, 'currency' => Currency::EUR, 'tier_desc' => 'Private coaching sessions. Authority-building frameworks. Direct Q&A and feedback', 'cover_base' => 'Field-Leader_cover'],
        ],
    ];

    public function run(): void
    {
        $maxDesc = TierResourceSupport::DESC_MAX_LENGTH;

        foreach (self::TIERS_BY_USER as $userName => $tiers) {
            $user = User::where('name', $userName)->first();
            if (! $user) {
                $this->command->warn("User \"{$userName}\" not found, skipping tiers.");

                continue;
            }

            $profile = CreatorProfile::where('user_id', $user->id)->first();
            if (! $profile) {
                $this->command->warn("Creator profile for \"{$userName}\" not found, skipping tiers.");

                continue;
            }

            foreach ($tiers as $data) {
                $tierDesc = $this->formatTierDescBullets($data['tier_desc']);
                if (mb_strlen($tierDesc) > $maxDesc) {
                    $tierDesc = mb_substr($tierDesc, 0, $maxDesc - 3).'...';
                }

                $tier = $profile->tiers()->firstOrCreate(
                    [
                        'creator_profile_id' => $profile->id,
                        'level' => $data['level'],
                    ],
                    [
                        'tier_name' => $data['tier_name'],
                        'tier_desc' => $tierDesc,
                        'price' => $data['price'],
                        'tier_currency' => $data['currency'],
                    ]
                );

                $coverBaseName = $data['cover_base'] ?? null;
                $coverPath = $this->findCoverFixture($user, $data['tier_name'], $data['tier_desc'], $coverBaseName);
                if ($coverPath !== null) {
                    $stored = Storage::disk('public')->putFile(
                        TierResourceSupport::COVER_DIRECTORY,
                        new File($coverPath)
                    );
                    $tier->tier_cover_path = $stored;
                    $tier->save();
                }
            }
        }
    }

    private function formatTierDescBullets(string $desc): string
    {
        if (str_contains($desc, ':')) {
            $afterColon = trim(substr($desc, strpos($desc, ':') + 1));
            $features = array_filter(array_map('trim', explode(',', $afterColon)));
            $lines = [];
            foreach ($features as $feature) {
                if ($feature !== '') {
                    $lines[] = '• '.$feature;
                }
            }

            return implode("\n", $lines);
        }

        $parts = array_filter(array_map('trim', explode('. ', $desc)));
        $lines = [];
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $lines[] = '• '.rtrim($part, '.');
        }

        return implode("\n", $lines);
    }

    private function tierNameToCoverSlug(string $name): string
    {
        $withParenDashes = str_replace(['(', ')'], ['(-', '-)'], $name);

        return str_replace(' ', '-', trim($withParenDashes));
    }

    private function findCoverFixture(User $user, string $tierName, string $tierDesc, ?string $coverBaseName): ?string
    {
        $tiersDir = base_path('database/seeders/fixtures/tiers');
        $creatorDir = $tiersDir.DIRECTORY_SEPARATOR.$user->username;
        if (is_dir($creatorDir)) {
            foreach ([$this->tierNameToCoverSlug($tierName), str_replace(' ', '-', $tierName)] as $tierSlug) {
                $path = $creatorDir.DIRECTORY_SEPARATOR.$tierSlug.'.png';
                if (file_exists($path)) {
                    return $path;
                }
            }
            if (str_contains($tierDesc, ':')) {
                $namePart = trim(substr($tierDesc, 0, strpos($tierDesc, ':')));
                foreach ([$this->tierNameToCoverSlug($namePart), str_replace(' ', '-', $namePart)] as $nameSlug) {
                    $path = $creatorDir.DIRECTORY_SEPARATOR.$nameSlug.'.png';
                    if (file_exists($path)) {
                        return $path;
                    }
                }
            }
        }

        if ($coverBaseName !== null && $coverBaseName !== '') {
            $directory = base_path(self::FIXTURES_COVERS);
            foreach (self::COVER_EXTENSIONS as $ext) {
                $path = $directory.DIRECTORY_SEPARATOR.$coverBaseName.'.'.$ext;
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        return null;
    }
}
