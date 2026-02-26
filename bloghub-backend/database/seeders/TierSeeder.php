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
            ['level' => 1, 'tier_name' => 'The Believer', 'price' => 5, 'currency' => Currency::USD, 'tier_desc' => 'Access to weekly case breakdowns', 'cover_base' => 'The-Believer_cover'],
            ['level' => 2, 'tier_name' => 'Behavioral Insider', 'price' => 15, 'currency' => Currency::USD, 'tier_desc' => 'Deep psychological profiling reports. Live Q&A on unsolved cases', 'cover_base' => 'Behavioral-Insider_cover'],
            ['level' => 3, 'tier_name' => 'X-Archive Elite', 'price' => 120, 'currency' => Currency::USD, 'tier_desc' => 'Private research briefings. Direct topic voting', 'cover_base' => 'X-Archive-Elite_cover'],
        ],
        'Dana Scully' => [
            ['level' => 1, 'tier_name' => 'Clinical Observer', 'price' => 4, 'currency' => Currency::USD, 'tier_desc' => 'Medical myth debunking articles. Access to archive content', 'cover_base' => 'Clinical-Observer_cover'],
            ['level' => 2, 'tier_name' => 'Forensic Analyst', 'price' => 12, 'currency' => Currency::USD, 'tier_desc' => 'Detailed autopsy case simulations', 'cover_base' => 'Forensic-Analyst_cover'],
            ['level' => 3, 'tier_name' => 'Evidence Council', 'price' => 170, 'currency' => Currency::USD, 'tier_desc' => 'Private webinars. Advanced clinical case breakdowns', 'cover_base' => 'Evidence-Council_cover'],
        ],
        'Gordon Freeman' => [
            ['level' => 1, 'tier_name' => 'Lab Access', 'price' => 6, 'currency' => Currency::EUR, 'tier_desc' => 'Physics concept explainers', 'cover_base' => 'Lab-Access_cover'],
            ['level' => 2, 'tier_name' => 'Resonance Member', 'price' => 18, 'currency' => Currency::EUR, 'tier_desc' => 'Advanced quantum theory breakdowns', 'cover_base' => 'Resonance-Member_cover'],
            ['level' => 3, 'tier_name' => 'Black Mesa Patron', 'price' => 200, 'currency' => Currency::EUR, 'tier_desc' => 'Early access to experimental content', 'cover_base' => 'Black-Mesa-Patron_cover'],
        ],
        'Gregory House' => [
            ['level' => 1, 'tier_name' => 'Differential Thinker', 'price' => 7, 'currency' => Currency::USD, 'tier_desc' => 'Weekly diagnostic puzzles. Medical case summaries. Access to discussion threads', 'cover_base' => 'Differential-Thinker_cover'],
            ['level' => 2, 'tier_name' => 'Diagnostic Team', 'price' => 31, 'currency' => Currency::USD, 'tier_desc' => 'Full case walkthrough videos. Rare disease breakdowns. Monthly clinical reasoning workshop', 'cover_base' => 'Diagnostic-Team_cover'],
            ['level' => 3, 'tier_name' => 'Princeton-Plainsboro Inner Circle', 'price' => 1101, 'currency' => Currency::USD, 'tier_desc' => 'Live interactive diagnosis sessions. Direct Q&A with case reviews. Exclusive behind-the-case commentary', 'cover_base' => 'Princeton-Plainsboro _Inner-Circle_cover'],
        ],
        'Caroline' => [
            ['level' => 1, 'tier_name' => 'Test Subject', 'price' => 199, 'currency' => Currency::CZK, 'tier_desc' => 'Experimental AI articles', 'cover_base' => 'Test-Subject_cover'],
            ['level' => 2, 'tier_name' => 'Advanced Prototype', 'price' => 1399, 'currency' => Currency::CZK, 'tier_desc' => 'Access to testing logs. Exclusive AI research posts. Early access to experimental drops', 'cover_base' => 'Advanced-Prototype_cover'],
            ['level' => 3, 'tier_name' => 'Aperture Core Member', 'price' => 5299, 'currency' => Currency::CZK, 'tier_desc' => 'Direct influence on next experiments', 'cover_base' => 'Aperture-Core-Member_cover'],
        ],
        'Ellen Ripley' => [
            ['level' => 1, 'tier_name' => 'Crew Member', 'price' => 99, 'currency' => Currency::CZK, 'tier_desc' => 'Survival strategy guides. Risk management articles. Community discussions', 'cover_base' => 'Crew-Member_cover'],
            ['level' => 2, 'tier_name' => 'Flight Officer', 'price' => 499, 'currency' => Currency::CZK, 'tier_desc' => 'Monthly tactical briefings', 'cover_base' => 'Flight-Officer_cover'],
            ['level' => 3, 'tier_name' => 'Command Authority', 'price' => 1999, 'currency' => Currency::CZK, 'tier_desc' => 'High-risk case analyses', 'cover_base' => 'Command-Authority_cover'],
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
                $coverPath = $this->findCoverFixture($coverBaseName);
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

    private function findCoverFixture(?string $baseName): ?string
    {
        if ($baseName === null || $baseName === '') {
            return null;
        }

        $directory = base_path(self::FIXTURES_COVERS);

        foreach (self::COVER_EXTENSIONS as $ext) {
            $path = $directory.DIRECTORY_SEPARATOR.$baseName.'.'.$ext;
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
