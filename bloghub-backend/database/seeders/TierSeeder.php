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
    private const DEFAULT_PRICES = [99, 199, 299];

    private const TIERS_BY_USER = [
        'Super Admin' => [
            [
                'level' => 1,
                'tier_name' => 'Apprentice',
                'tier_desc' => 'The Entered Apprentice represents the beginning of the journey. It focuses on self-knowledge, moral foundation, discipline, and the willingness to learn through humility and reflection.',
                'cover_file' => 'tier-l1_Apprentice',
            ],
            [
                'level' => 2,
                'tier_name' => 'Fellowcraft',
                'tier_desc' => 'The Fellowcraft degree symbolizes growth through knowledge and work. It emphasizes learning, reason, craftsmanship, and the development of the mind through study and applied understanding.',
                'cover_file' => 'tier-l2_Fellowcraft',
            ],
            [
                'level' => 3,
                'tier_name' => 'Master',
                'tier_desc' => 'The Master Mason degree reflects maturity and mastery of oneself. It explores responsibility, integrity, mortality, and the pursuit of truth, marking readiness for leadership and deeper understanding.',
                'cover_file' => 'tier-l3_Master',
            ],
        ],
        'User' => [
            [
                'level' => 1,
                'tier_name' => 'Engram',
                'tier_desc' => 'An engram is a recorded mental impression formed during moments of pain or unconsciousness. It is believed to store trauma and emotions that later influence behavior, reactions, and decisions without conscious awareness',
                'cover_file' => 'tier-l1_Engram',
            ],
            [
                'level' => 2,
                'tier_name' => 'Operating Thetan',
                'tier_desc' => 'An Operating Thetan is a spiritual being who has regained awareness of their true, immortal nature and is believed to function independently of the physical body, matter, energy, space, and time',
                'cover_file' => 'tier-l2_Operating-Thetan',
            ],
            [
                'level' => 3,
                'tier_name' => 'Xenu',
                'tier_desc' => 'Xenu is a mythological galactic ruler described as having ruled millions of years ago, whose actions allegedly led to the implantation of traumatic memories that still affect spiritual beings today',
                'cover_file' => 'tier-l3_Xenu',
            ],
        ],
        'Admin' => [
            [
                'level' => 1,
                'tier_name' => 'Neophyte',
                'tier_desc' => 'A neophyte is a beginner on the path of Krishna consciousness. This stage focuses on learning basic philosophy, developing daily practice, and cultivating devotion through guidance and community.',
                'cover_file' => 'tier-l1_Neophyte',
            ],
            [
                'level' => 2,
                'tier_name' => 'Prabhupada',
                'tier_desc' => 'Srila Prabhupada was the founder of ISKCON and a key teacher of Gaudiya Vaishnavism. He brought Krishna consciousness to a global audience through teachings, translations, and disciplined practice.',
                'cover_file' => 'tier-l2_Prabhupada',
            ],
            [
                'level' => 3,
                'tier_name' => 'Krishna',
                'tier_desc' => 'Krishna is the central divine figure of Krishnaism, embodying wisdom, compassion, and divine play. He represents ultimate reality, guiding devotees toward devotion, ethical living, and spiritual liberation.',
                'cover_file' => 'tier-l3_Krishna',
            ],
        ],
    ];

    public function run(): void
    {
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

            foreach ($tiers as $index => $data) {
                $tierDesc = $data['tier_desc'];
                $maxDesc = TierResourceSupport::DESC_MAX_LENGTH;
                if (mb_strlen($tierDesc) > $maxDesc) {
                    $tierDesc = mb_substr($tierDesc, 0, $maxDesc - 3).'...';
                }

                $price = self::DEFAULT_PRICES[$index] ?? 99;

                $tier = $profile->tiers()->firstOrCreate(
                    [
                        'creator_profile_id' => $profile->id,
                        'level' => $data['level'],
                    ],
                    [
                        'tier_name' => $data['tier_name'],
                        'tier_desc' => $tierDesc,
                        'price' => $price,
                        'tier_currency' => Currency::CZK,
                    ]
                );

                $coverBaseName = $data['cover_file'] ?? $data['tier_name'];
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

    private function findCoverFixture(string $baseName): ?string
    {
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
