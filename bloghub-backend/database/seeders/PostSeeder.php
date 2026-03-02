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
                'notes-from-the-test-chamber' => [
                    MediaType::Image->value,
                    "The chamber was silent before activation. It rarely is after. Documentation is the only defense against repeating the same experiment twice. When we step into the environment of the Anti-Mass Spectrometer, we aren't just observers; we are variables within a highly volatile equation.\n\nThe primary objective of today's sequence was to calibrate the emitters to a baseline of 93%. Even at this \"safe\" level, the atmospheric ionization is palpable. You can feel the static crawling across your skin, a reminder that the vacuum seals are the only thing separating us from a localized collapse of standard physics. I've noted a slight variance in the superconducting magnets—nothing outside of the 0.03% margin of error, but enough to warrant a manual override if the dampeners don't compensate by the next cycle.\n\nIn theoretical physics, we often treat the \"chamber\" as a vacuum of variables, but the reality is much messier. Dust, humidity, and even the hum of the facility's power grid play their parts. We record everything because the moment the beam is live, the luxury of second-guessing vanishes.",
                    'The chamber was silent before activation. It rarely is after. Documentation is the only defense against repeating the same experiment twice. These logs represent the raw, unfiltered data from the pre-resonance phase, where every decibel and degree matters',
                    '3years',
                    3,
                ],
                'cascade-risk-assessment-model' => [
                    MediaType::Image->value,
                    "Risk is rarely about probability alone. It's about consequence multiplied by amplification potential. A small trigger can destabilize a closed system faster than expected.\n\nIn our current modeling for high-energy experiments, we often focus on the \"Mean Time Between Failures\" (MTBF), but this is a trap. In a complex, tightly coupled system like the one we operate here at Black Mesa, failure isn't a linear progression. It is a phase transition. One moment the system is nominal; the next, it has reached a point of no return.\n\nConsider the \"Resonance\" scenario. If the harmonic oscillations of the crystal sample exceed the containment field's dampening capacity, the resulting energy spike doesn't just blow a fuse—it creates a feedback loop that draws power directly from the grid. We are essentially building a lightning rod and hoping the lightning knows when to stop. My assessment suggests that our current safety protocols rely too heavily on manual intervention. In a true cascade, the human nervous system is the slowest component in the room.",
                    "Risk is rarely about probability alone. It's consequence multiplied by amplification potential. A small trigger can destabilize a closed system fast. This model examines the thresholds where a minor anomaly becomes an unstoppable chain reaction",
                    '2years',
                    2,
                ],
                'lab-whiteboard-session' => [
                    MediaType::Image->value,
                    "Today's derivation focuses on nonlinear response curves in confined plasma systems. The chalk dust is optional. The math is not.\n\nWhen we look at the interaction between high-intensity beams and exotic matter, we have to discard the comfort of linear equations. We are working with the Schrödinger equation in a high-pressure environment where \$V(x)\$ is no longer a static potential.\n\n\$i\\hbar \\frac{\\partial}{\\partial t} \\Psi(\\mathbf{r},t) = \\hat{H} \\Psi(\\mathbf{r},t)\$\n\nThe whiteboard is currently covered in tensors trying to map the stress on the containment housing. If the plasma density fluctuates past the 10.4 GeV threshold, the magnetic bottle will begin to \"breathe.\" This rhythmic expansion and contraction is what leads to catastrophic shearing. I spent four hours today just recalculating the dampening constants for the secondary emitters. It's tedious, unglamorous work, but it's the difference between a successful test and a very expensive hole in the floor.\n\nPeople ask why I still use a physical whiteboard instead of a digital simulation. The answer is simple: friction. The act of writing the variables out helps me internalize the scale of the energy we're trying to tether.",
                    "Today's derivation focuses on nonlinear response curves in confined plasma systems. The chalk dust is optional; the math is not. We look at the moment Newtonian physics hands the keys to quantum uncertainty—the results are demanding",
                    '1year',
                    2,
                ],
                'resonance-cascade-theory-basics' => [
                    MediaType::Image->value,
                    "When energy systems are pushed beyond safe theoretical thresholds, the unknown is no longer abstract – it becomes measurable. Today I break down how resonance amplification works in closed environments and why containment models often fail under nonlinear load.\n\nAt its core, a Resonance Cascade occurs when the frequency of the input energy matches the \"natural\" frequency of the space-time fabric within a localized area. Normally, the universe is quite resilient. It has a way of absorbing excess energy and radiating it away as heat or light. However, when we use a focal point—like a pure crystal sample—we are essentially creating a needle that can pierce that resilience.\n\nThe danger is the \"Feedback Singularity.\" Once the rift begins to open, it creates its own gravitational and electromagnetic pull, drawing more energy into the system than we are providing. It becomes a self-sustaining event. Our current containment shields are designed for 110% of predicted load, but a cascade is, by definition, an infinite load event. We aren't just testing physics; we are poking at the boundaries of the dimension itself. We need to be prepared for the possibility that the door we are opening might not have a handle on our side.",
                    'When energy systems are pushed beyond safe theoretical thresholds, the unknown becomes measurable. I break down how resonance amplification works in closed environments and why containment models often fail under nonlinear load. Proceed with caution',
                    '6months',
                    3,
                ],
                'advanced-particle-dynamics' => [
                    MediaType::Video->value,
                    "At extreme densities, classical intuition collapses. Predictive modeling must rely on probabilistic interpretation rather than deterministic expectation. When you are standing in the observation deck, watching the beam ignite, it's easy to think of particles as tiny billiard balls. They are not. They are smears of probability across a field of infinite potential.\n\nIn the most recent run, we observed \"ghosting\" in the detection arrays—particles appearing to exist in two positions simultaneously before the collision even occurred. While the junior techs were checking for sensor malfunctions, the reality is much more interesting. We are seeing the temporal displacement predicted by the Thorne-Hawking models.\n\nThe math suggests that at these speeds, the particles are \"feeling\" the collision before it happens. If we can stabilize this effect, we aren't just looking at better power generation; we are looking at the fundamental restructuring of how we transmit information. But again, the power requirements are staggering. We are currently pulling 40% of the facility's total output just to maintain the vacuum seal on the primary ring. It is a delicate dance on the edge of a very sharp knife.",
                    'At extreme densities, classical intuition collapses. Predictive modeling must rely on probabilistic interpretation. This entry explores subatomic particle behavior under the concentrated force of a multi-stage accelerator',
                    '2months',
                    1,
                ],
                'microfractures-under-pressure' => [
                    MediaType::Image->value,
                    "Slow structural stress simulation. Watch where the fractures begin – not at the center, but along overlooked constraints. When we talk about \"structural integrity,\" we often visualize a solid block of titanium or lead-lined concrete. In reality, under the influence of high-energy particle bombardment, these materials behave more like viscous fluids.\n\nThe simulation today focused on the primary dampening struts. Most engineers look for the point of impact, but the microfractures actually manifest at the mounting brackets—the \"overlooked constraints.\" It's the rigid points that break first. If a system cannot flex, it shatters.\n\nI've seen this happen in the field. A containment seal looks pristine to the naked eye, but under a spectrographic sweep, it's a web of microscopic failures waiting for a single resonant vibration to trigger a breach. We need to move toward adaptive materials that can redistribute stress in real-time. Until then, we are just betting on the thickness of the steel.",
                    'Slow structural stress simulation. Watch where the fractures begin—not at the center, but along overlooked constraints. This entry explores the transition from nominal operation to catastrophic failure in reinforced containment materials',
                    '3weeks',
                    2,
                ],
                'containment-door-mechanics' => [
                    MediaType::Image->value,
                    "Engineering is philosophy applied to steel. Every door, seal and barrier exists because someone predicted failure. The heavy blast doors in Sector C aren't just there for privacy; they are the physical manifestation of our lack of trust in the laws of physics.\n\nEach door operates on a redundant hydraulic system with a manual override that requires more physical force than a single human should be able to exert. Why? Because if the power fails and the magnets lose their grip, that door becomes our only line of defense against atmospheric venting or worse—exotic radiation.\n\nI spent the morning reviewing the seal integrity on the Level 3 security bulkheads. The seals are rated for 5,000 PSI, which sounds impressive until you realize the pressure spike during a resonance event can exceed that by a factor of ten. We aren't building cages; we are building delay mechanisms. In the event of a total system failure, these doors don't save the experiment. They save the people in the next hallway. Hopefully.",
                    "Engineering is philosophy applied to steel. Every door, seal and barrier exists because someone predicted failure. We build them to buy us time when the inevitable breach occurs. Here is the breakdown",
                    '1week',
                    null,
                ],
                'particle-accelerator-perspective' => [
                    MediaType::Video->value,
                    "A slow walkthrough of a circular acceleration tunnel. Scale matters. Human intuition struggles to grasp infrastructure built for subatomic phenomena. To move a single electron at near-light speeds, we require miles of superconducting magnets, gigawatts of power, and a vacuum more perfect than the space between stars.\n\nWalking the maintenance corridor of the primary ring is a humbling experience. You are surrounded by the hum of the cooling pumps, a sound that vibrates in your marrow. It is a reminder that we are small. We are biological entities of carbon and water trying to dictate terms to the fundamental forces of the universe.\n\nThe alignment must be perfect. A deviation of a few microns at the injection point translates to a catastrophic collision with the tunnel wall three miles down the line. We spend months calibrating the magnets just to witness a collision that lasts less than a nanosecond. It is the ultimate exercise in patience and precision. Sometimes I wonder if the machine is studying us as much as we are studying it.",
                    'A slow walkthrough of a circular acceleration tunnel. Scale matters; human intuition struggles to grasp infrastructure built for subatomic phenomena. This log details the physical magnitude required to influence the smallest building blocks',
                    '3days',
                    1,
                ],
                'theoretical-bridge-between-dimensions' => [
                    MediaType::Image->value,
                    "Speculative but mathematically grounded: what would a stable energy bridge require? Short answer – symmetry, balance, and far more power than we comfortably control.\n\nTo fold space-time, you don't just \"tear\" a hole. You have to convince two points in different manifolds that they are actually the same point. This requires a massive injection of negative energy density. On paper, the equations balance beautifully. The tensors align, the variables cancel out, and you are left with a gateway.\n\nIn practice, the \"bridge\" is a violent, churning vortex of instability. To keep it open, you need a constant stream of exotic matter to act as a tether. Without that tether, the bridge snaps back like a rubber band, releasing all that stored potential energy in a localized explosion that would make a nuclear warhead look like a firecracker.\n\nWe are currently looking at \"Xen\" as a potential anchor point—a borderworld that seems to sit between higher and lower dimensional states. If we can find a stable frequency there, we might not just see through the bridge; we might be able to walk across it. But as I told the board: just because you can build a bridge doesn't mean you want to see what's crossing from the other side.",
                    'Speculative but mathematically grounded: what would a stable energy bridge require? Short answer—symmetry, balance, and far more power than we comfortably control. A look into the dark math of multi-dimensional folding and transit',
                    'yesterday',
                    3,
                ],
                'acoustic-profile-of-reactor-room' => [
                    MediaType::Audio->value,
                    "Ambient recording from a high-energy reactor chamber. Even sound reveals system stress if you know what to listen for – subtle harmonic shifts precede mechanical failure.\n\nTo the untrained ear, the reactor room is just a wall of white noise. But to an experimental physicist, that noise is a symphony of data. The high-pitched whine of the turbines tells you about the bearing wear. The low-frequency thrum of the capacitors tells you about the charge density.\n\nLast night, during a routine power-up, I noticed a \"shiver\" in the acoustic profile—a 12Hz oscillation that wasn't there during the last cycle. It's a ghost in the machine. It suggests that the coolant pipes are vibrating in sympathy with the magnetic field. If those frequencies align, we get a resonance event that could shake the entire Sector C wing apart.\n\nI've requested a full dampening audit. Most people wait for the warning lights to turn red. I prefer to listen for the moment the machine starts to scream.",
                    'Ambient recording from a high-energy reactor chamber. Sound reveals system stress if you know what to listen for—subtle harmonic shifts precede mechanical failure. This entry analyzes the "song" of the machine under heavy load',
                    'today',
                    2,
                ],
                'containment-failure-simulation' => [
                    MediaType::Image->value,
                    "A short simulation showing how cascading overload propagates through a segmented energy chamber. Notice how the weakest structural boundary defines the outcome.\n\nIn this model, we've simulated a 15% over-injection of plasma into the primary holding tank. The initial failure isn't the tank itself, but the magnetic seals on the intake valves. Once those blow, the plasma is no longer contained—it's looking for a path of least resistance.\n\nThe \"cascade\" happens in milliseconds. The heat from the first breach melts the control sensors, which tells the computer the system is \"nominal\" even as the room is melting. By the time the secondary failsafes kick in, the kinetic energy is too great to be stopped.\n\nThe takeaway from this simulation is clear: our safety systems are too localized. We need a \"scorched earth\" protocol—if one sector fails, the adjacent sectors must be physically isolated immediately. We cannot afford to be polite when the laws of thermodynamics are being violated.",
                    'A short simulation showing how cascading overload propagates through a segmented energy chamber. The weakest structural boundary defines the outcome. A visual and data-driven study of how we lose control',
                    '5days',
                    null,
                ],
                'lab-notes-on-energy-instability' => [
                    MediaType::Image->value,
                    "Instability rarely announces itself dramatically. It begins as deviation – minor fluctuations that appear statistically irrelevant. Monitoring variance is more important than chasing anomalies.\n\nIn the test we ran at 0800, the energy output spiked by a mere 0.004%. In any other facility, that would be dismissed as a sensor glitch or a power surge from the main grid. Here, that 0.004% represents an extra three terajoules of energy that shouldn't exist.\n\nWhere did it come from? That is the question that keeps me up. If the system is closed, the energy should be constant. If it's increasing, we are either drawing from an external source we haven't identified, or the material we're testing is beginning to decay in a way that our models didn't predict.\n\nI've ordered a recalibration of the spectrometer. If the variance continues to climb, we have to abort. The administration is pushing for a full-scale test by the end of the week, but the numbers are telling a different story. The numbers don't have a schedule to keep; they only have the truth to tell.",
                    'Instability rarely announces itself dramatically. It begins as deviation—minor fluctuations that appear statistically irrelevant. Monitoring variance is more important than chasing anomalies. These notes cover the early warning signs',
                    '2weeks',
                    1,
                ],
                'quantum-field-visualization' => [
                    MediaType::Image->value,
                    "A simplified visualization of quantum field interactions. Think less of particles as objects and more as disturbances propagating through structured fields. The math is elegant. The consequences are not always.\n\nWhen we look at the visualization on the main monitor, it looks like ripples on a pond. But those ripples are the very fabric of our reality. When we fire the Anti-Mass Spectrometer, we are throwing a massive boulder into that pond.\n\nWhat we're looking for is the \"interference pattern.\" If the waves overlap correctly, we can enhance the signal and peer into the underlying structure of the universe. If they overlap incorrectly, we get \"destructive interference\"—not just in the data, but in physical space.\n\nI've been staring at these field maps for years, and I'm still struck by how fragile it all seems. We live our lives on the surface of these fields, never realizing that just a few microns beneath the \"water,\" there is a depth of energy that could unmake us in an instant. It's a beautiful way to die, I suppose. But I'd rather just collect the data.",
                    'A simplified visualization of quantum field interactions. Think less of particles as objects and more as disturbances in structured fields. The math is elegant; the consequences are not always. A look at the unseen',
                    '4months',
                    null,
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

            $tiersByLevel = null;
            if ($userName === 'Gordon Freeman') {
                $tiersByLevel = $profile->tiers()->whereIn('level', [1, 2, 3])->get()->keyBy('level');
            }

            foreach ($posts as $slug => $postData) {
                $isExtended = count($postData) >= 5;
                $mediaTypeValue = $postData[0];
                $contentText = $postData[1];
                $mediaType = MediaType::from($mediaTypeValue);
                $title = $this->slugToTitle($slug);
                $mediaPath = $this->copyFixtureMedia($creatorSlug, $slug, $mediaType);

                $attributes = [
                    'title' => $title,
                    'content_text' => $contentText,
                    'media_url' => $mediaPath,
                    'media_type' => $mediaType,
                    'required_tier_id' => null,
                ];

                $createdAtKey = null;
                if ($isExtended) {
                    $excerpt = $postData[2];
                    $createdAtKey = $postData[3];
                    $tierLevel = $postData[4];
                    $attributes['excerpt'] = $excerpt;
                    if ($tierLevel !== null && $tiersByLevel !== null) {
                        $tier = $tiersByLevel->get($tierLevel);
                        $attributes['required_tier_id'] = $tier?->id;
                    }
                }

                $post = Post::firstOrCreate(
                    [
                        'creator_profile_id' => $profile->id,
                        'slug' => $slug,
                    ],
                    $attributes
                );
                if ($isExtended) {
                    if (! $post->wasRecentlyCreated) {
                        $post->update($attributes);
                    }
                    $post->created_at = $this->resolveCreatedAt($createdAtKey);
                    $post->save();
                }
            }
        }
    }

    private function resolveCreatedAt(string $key): \DateTimeInterface
    {
        $now = now();

        return match ($key) {
            'today' => $now,
            'yesterday', '1day' => $now->copy()->subDay(),
            '3days' => $now->copy()->subDays(3),
            '5days' => $now->copy()->subDays(5),
            '1week' => $now->copy()->subWeek(),
            '2weeks' => $now->copy()->subWeeks(2),
            '3weeks' => $now->copy()->subWeeks(3),
            '2months' => $now->copy()->subMonths(2),
            '4months' => $now->copy()->subMonths(4),
            '6months' => $now->copy()->subMonths(6),
            '1year' => $now->copy()->subYear(),
            '2years' => $now->copy()->subYears(2),
            '3years' => $now->copy()->subYears(3),
            default => $now,
        };
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
