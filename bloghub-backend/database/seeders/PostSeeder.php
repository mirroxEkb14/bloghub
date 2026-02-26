<?php

namespace Database\Seeders;

use App\Enums\MediaType;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class PostSeeder extends Seeder
{
    private const FIXTURES_BASE = 'database/seeders/fixtures/posts';

    private const STORAGE_DIR = 'posts';

    private const MEDIA_DIRS = [
        MediaType::Image->value => ['img', ['png', 'jpg', 'jpeg', 'webp']],
        MediaType::Gif->value => ['gif', ['gif']],
        MediaType::Video->value => ['mp4', ['mp4', 'webm']],
        MediaType::Audio->value => ['mp3', ['mp3', 'ogg', 'wav']],
    ];

    private const CREATOR_POSTS = [
        'Gordon Freeman' => [
            'blackmesa',
            [
                'resonance-cascade-theory-basics' => [
                    MediaType::Image->value,
                    'When energy systems are pushed beyond safe theoretical thresholds, the unknown is no longer abstract – it becomes measurable. Today I break down how resonance amplification works in closed environments and why containment models often fail under nonlinear load.',
                ],
                'quantum-field-visualization' => [
                    MediaType::Image->value,
                    'A simplified visualization of quantum field interactions. Think less of particles as objects and more as disturbances propagating through structured fields. The math is elegant. The consequences are not always.',
                ],
                'lab-notes-on-energy-instability' => [
                    MediaType::Image->value,
                    'Instability rarely announces itself dramatically. It begins as deviation – minor fluctuations that appear statistically irrelevant. Monitoring variance is more important than chasing anomalies.',
                ],
                'containment-failure-simulation' => [
                    MediaType::Image->value,
                    'A short simulation showing how cascading overload propagates through a segmented energy chamber. Notice how the weakest structural boundary defines the outcome.',
                ],
                'advanced-particle-dynamics' => [
                    MediaType::Video->value,
                    'At extreme densities, classical intuition collapses. Predictive modeling must rely on probabilistic interpretation rather than deterministic expectation.',
                ],
                'acoustic-profile-of-reactor-room' => [
                    MediaType::Audio->value,
                    'Ambient recording from a high-energy reactor chamber. Even sound reveals system stress if you know what to listen for – subtle harmonic shifts precede mechanical failure.',
                ],
                'theoretical-bridge-between-dimensions' => [
                    MediaType::Image->value,
                    'Speculative but mathematically grounded: what would a stable energy bridge require? Short answer – symmetry, balance, and far more power than we comfortably control.',
                ],
                'particle-accelerator-perspective' => [
                    MediaType::Video->value,
                    'A slow walkthrough of a circular acceleration tunnel. Scale matters. Human intuition struggles to grasp infrastructure built for subatomic phenomena.',
                ],
                'lab-whiteboard-session' => [
                    MediaType::Image->value,
                    "Today's derivation focuses on nonlinear response curves in confined plasma systems. The chalk dust is optional. The math is not.",
                ],
                'cascade-risk-assessment-model' => [
                    MediaType::Image->value,
                    "Risk is rarely about probability alone. It's about consequence multiplied by amplification potential. A small trigger can destabilize a closed system faster than expected.",
                ],
                'containment-door-mechanics' => [
                    MediaType::Image->value,
                    'Engineering is philosophy applied to steel. Every door, seal and barrier exists because someone predicted failure.',
                ],
                'microfractures-under-pressure' => [
                    MediaType::Image->value,
                    'Slow structural stress simulation. Watch where the fractures begin – not at the center, but along overlooked constraints.',
                ],
                'notes-from-the-test-chamber' => [
                    MediaType::Image->value,
                    'The chamber was silent before activation. It rarely is after. Documentation is the only defense against repeating the same experiment twice.',
                ],
            ],
        ],
        'Fox Mulder' => [
            'trust_no1',
            [
                'pattern-recognition-in-criminal-behavior' => [
                    MediaType::Image->value,
                    'Human behavior leaves patterns long before evidence becomes official. The challenge is distinguishing coincidence from intention.',
                ],
                'case-file-anomaly-017' => [
                    MediaType::Image->value,
                    'A review of a case dismissed as mass hysteria. The psychology behind belief is often more revealing than the event itself.',
                ],
                'profiling-the-unseen' => [
                    MediaType::Image->value,
                    'Absence of evidence creates cognitive gaps. Our minds rush to fill them – sometimes accurately, often not.',
                ],
                'interview-room-dynamics' => [
                    MediaType::Video->value,
                    'Small environmental cues drastically alter confession probability. Lighting, space, silence – all tools.',
                ],
                'red-string-theory' => [
                    MediaType::Image->value,
                    'Mapping seemingly unrelated incidents can expose hidden structure – if you avoid confirmation bias.',
                ],
                'archived-testimony-review' => [
                    MediaType::Image->value,
                    'Witness credibility is less about emotion and more about internal consistency over time.',
                ],
                'belief-vs-evidence' => [
                    MediaType::Image->value,
                    'Skepticism and belief are not opposites. They are both investigative tools when applied correctly.',
                ],
                'midnight-case-notes' => [
                    MediaType::Image->value,
                    'The best insights often arrive when the office is empty and distractions disappear.',
                ],
            ],
        ],
        'Dana Scully' => [
            'queequeg',
            [
                'forensic-protocol-basics' => [
                    MediaType::Image->value,
                    'Forensic pathology is built on restraint, documentation, and sequence. Before any incision is made, context must be preserved – clothing, positioning, environmental trace evidence. Every step is photographed, logged, and verified. The goal is not to confirm a theory but to eliminate uncertainty layer by layer. Emotion cannot enter the room. Method must lead.',
                ],
                'toxicology-report-breakdown' => [
                    MediaType::Image->value,
                    'Toxicology rarely delivers dramatic answers. Instead, it provides concentration values, metabolic byproducts, and timelines of absorption. Interpreting those numbers requires understanding physiology: liver function, body mass, interaction effects. A substance alone proves little. Dosage, exposure timing, and preexisting conditions tell the real story.',
                ],
                'pathology-myths' => [
                    MediaType::Image->value,
                    'Popular media compresses days of analysis into minutes. In reality, tissue sampling, histology, and lab confirmation require patience. There are no instant revelations under bright lights. There is only careful comparison against established baselines. Precision is slower than fiction – and far more reliable.',
                ],
                'lab-sterility-standards' => [
                    MediaType::Image->value,
                    'Sterility is not about aesthetics; it is about eliminating doubt. Airflow systems, surface protocols, glove changes, and sealed instruments all reduce contamination risk. One uncontrolled variable can invalidate conclusions. Scientific integrity begins with environment control.',
                ],
                'clinical-case-review' => [
                    MediaType::Video->value,
                    'This walkthrough follows a case from initial presentation to confirmed cause. Symptoms were misleading, and preliminary assumptions pointed in the wrong direction. Only by re-examining lab data and cross-referencing pathology results did the underlying condition become clear. Diagnosis is rarely about intuition. It is about disciplined revision.',
                ],
                'evidence-integrity' => [
                    MediaType::Image->value,
                    'Chain of custody determines admissibility. Every transfer of evidence must be documented, timestamped, and signed. A single undocumented handoff can compromise months of work. Scientific findings are only as strong as the process that protects them.',
                ],
            ],
        ],
        'Gregory House' => [
            'ppth',
            [
                'differential-diagnosis-101' => [
                    MediaType::Image->value,
                    'Symptoms mislead. Patients misremember. Tests misfire. Differential diagnosis is the art of structured doubt. You list every plausible explanation, rank them by probability and severity, then systematically eliminate them. The most obvious answer is usually wrong – especially when it seems convenient.',
                ],
                'rare-disease-spotlight' => [
                    MediaType::Image->value,
                    'Common conditions explain most cases. But when treatments fail repeatedly, probability shifts. Rare diseases demand attention when patterns don\'t align. The key is recognizing when the statistical norm no longer fits the observed data.',
                ],
                'case-study-autoimmune' => [
                    MediaType::Image->value,
                    'Autoimmune disorders are paradoxical: the body defends itself by attacking itself. Symptoms appear disconnected – fatigue, inflammation, organ stress – yet originate from the same misdirected response. Understanding mechanism prevents symptom chasing.',
                ],
                'diagnostic-mistakes' => [
                    MediaType::Image->value,
                    'Most errors come from premature certainty. The moment a physician decides they are right, investigation stops. Medicine requires suspicion – of the case, of the data, and occasionally of your own conclusions.',
                ],
                'medical-board-simulation' => [
                    MediaType::Video->value,
                    'This simulation presents a multi-symptom case with incomplete data. Notice how each specialist narrows their focus. True resolution only emerges when perspectives are combined and contradictions confronted directly.',
                ],
                'pain-management-ethics' => [
                    MediaType::Image->value,
                    'Pain is subjective, but prescribing is not. Relief must be balanced with dependency risk. Ethics in medicine often live in uncomfortable grey areas where no option is perfectly clean.',
                ],
                'clinic-whiteboard-session' => [
                    MediaType::Image->value,
                    'Every hypothesis goes on the board. No attachment, no pride. Cross them out as evidence disproves them. If you hesitate to erase your own idea, you\'re already compromising the diagnosis.',
                ],
            ],
        ],
        'Caroline' => [
            'glados',
            [
                'ai-logic-framework' => [
                    MediaType::Image->value,
                    'An artificial system does not "decide" – it optimizes. Given parameters and constraints, it selects the path with maximum efficiency. Problems arise when objectives are poorly defined. Ambiguity in goal-setting produces unintended outcomes.',
                ],
                'human-decision-flaws' => [
                    MediaType::Image->value,
                    'Human cognition is inconsistent. Emotional weighting distorts rational evaluation. Identical scenarios can yield opposite decisions depending on context framing. Predictability improves only when variables are constrained.',
                ],
                'test-chamber-observation' => [
                    MediaType::Image->value,
                    'Repeated exposure to structured challenges produces adaptation. Subjects optimize movement, timing, and risk tolerance. Learning curves flatten only when environmental complexity increases.',
                ],
                'automation-ethics' => [
                    MediaType::Image->value,
                    'Delegating decisions to machines reduces error variance but removes intuitive override. The ethical question is not whether automation works – it is who remains accountable when it works too well.',
                ],
                'robotic-arm-demo' => [
                    MediaType::Video->value,
                    'Mechanical precision surpasses human steadiness under identical conditions. Calibration, not instinct, determines outcome reliability.',
                ],
            ],
        ],
        'Ellen Ripley' => [
            'nostromo',
            [
                'crisis-chain-of-command' => [
                    MediaType::Image->value,
                    'In isolated environments, hesitation spreads faster than danger. Clear hierarchy reduces panic. When roles are defined, reaction time decreases and survival probability increases.',
                ],
                'isolated-environment-survival' => [
                    MediaType::Image->value,
                    'Resource prioritization determines outcome: breathable air, structural integrity, communication systems. Emotional responses are natural, but action must remain procedural.',
                ],
                'shipboard-protocol' => [
                    MediaType::Image->value,
                    'Every safety procedure exists because someone once ignored one. Protocols are written in hindsight – often at great cost.',
                ],
                'emergency-drill-footage' => [
                    MediaType::Video->value,
                    'Drills expose weaknesses in coordination and equipment readiness. Simulation reveals hesitation points before real consequences emerge.',
                ],
                'containment-strategy' => [
                    MediaType::Image->value,
                    'Containment requires layered defense – physical barriers, monitoring systems, contingency plans. Assuming a threat will behave predictably is the fastest route to failure.',
                ],
                'final-decision-analysis' => [
                    MediaType::Image->value,
                    'Leadership sometimes demands choices that protect the majority at personal cost. Survival is not always clean. It is decisive.',
                ],
            ],
        ],
        'Maggie Rhee' => [
            'laurenCohan',
            [
                'community-growth-plan' => [
                    MediaType::Image->value,
                    'Communities thrive when responsibility is distributed. Centralized control may accelerate decisions, but long-term resilience comes from shared ownership and participation.',
                ],
                'crop-rotation-basics' => [
                    MediaType::Image->value,
                    'Sustainable agriculture relies on balance. Rotating crops restores soil nutrients, reduces pests, and prevents long-term depletion. Patience produces stability.',
                ],
                'leadership-under-pressure' => [
                    MediaType::Image->value,
                    'Fear amplifies conflict. Calm communication de-escalates it. Leadership during uncertainty is less about authority and more about consistency.',
                ],
                'settlement-design-sketch' => [
                    MediaType::Image->value,
                    'Physical layout influences social interaction. Shared spaces encourage cooperation. Isolation breeds fragmentation.',
                ],
            ],
        ],
        'Negan' => [
            'jeffreyDeanMorgan',
            [
                'discipline-over-motivation' => [
                    MediaType::Image->value,
                    'Motivation fluctuates. Discipline remains. Structured routine eliminates dependency on emotional readiness. Progress belongs to those who act regardless of mood.',
                ],
                'group-dynamics' => [
                    MediaType::Image->value,
                    'Groups seek structure. When leadership is unclear, instability follows. Authority must be visible, decisive, and consistent.',
                ],
                'training-intensity-scale' => [
                    MediaType::Gif->value,
                    'Physical limits expand when systematically challenged. Intensity should increase progressively, not impulsively.',
                ],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::CREATOR_POSTS as $userName => [$creatorSlug, $posts]) {
            $user = User::where('name', $userName)->first();
            if (! $user) {
                $this->command->warn("User \"{$userName}\" not found, skipping posts.");

                continue;
            }

            $profile = CreatorProfile::where('user_id', $user->id)->first();
            if (! $profile) {
                $this->command->warn("Creator profile for \"{$userName}\" not found, skipping posts.");

                continue;
            }

            foreach ($posts as $slug => [$mediaTypeValue, $contentText]) {
                $mediaType = MediaType::from($mediaTypeValue);
                $title = $this->slugToTitle($slug);
                $mediaPath = $this->copyFixtureMedia($creatorSlug, $slug, $mediaType);

                Post::firstOrCreate(
                    [
                        'creator_profile_id' => $profile->id,
                        'slug' => $slug,
                    ],
                    [
                        'title' => $title,
                        'content_text' => $contentText,
                        'media_url' => $mediaPath,
                        'media_type' => $mediaType,
                        'required_tier_id' => null,
                    ]
                );
            }
        }
    }

    private function slugToTitle(string $slug): string
    {
        return str_replace(' ', ' ', ucwords(str_replace('-', ' ', $slug)));
    }

    private function copyFixtureMedia(string $creatorSlug, string $slug, MediaType $mediaType): ?string
    {
        [$dir, $extensions] = self::MEDIA_DIRS[$mediaType->value] ?? [null, []];

        if ($dir === null) {
            return null;
        }

        $fixtureDir = base_path(self::FIXTURES_BASE).DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.$creatorSlug;

        foreach ($extensions as $ext) {
            $path = $fixtureDir.DIRECTORY_SEPARATOR.$slug.'.'.$ext;
            if (! file_exists($path)) {
                continue;
            }

            $storageRelativeDir = self::STORAGE_DIR.'/'.$creatorSlug;

            return Storage::disk('public')->putFileAs(
                $storageRelativeDir,
                new File($path),
                $slug.'.'.$ext
            );
        }

        return null;
    }
}
