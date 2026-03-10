<?php

namespace Database\Seeders;

use App\Enums\MediaType;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
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
                'the-universal-combine-union' => [
                    MediaType::Gif->value,
                    '<p>The Combine did not conquer Earth in seven hours because of <strong>superior numbers</strong>; they conquered us because they operate on a scale of physics we are only beginning to categorize. They don\'t just occupy territory; they <em>rewrite the fundamental utility</em> of a species.</p><p>The most subtle weapon in the Combine arsenal is the global <strong>Suppression Field</strong>. By manipulating the localized dark energy grid, the Union has inhibited the specific protein chains required for human embryonic development. They have essentially turned off our biological future.</p><p>The Combine \'Soldier\' is the ultimate forensic tragedy. These are not aliens; they are human beings who have undergone "Non-Mechanical Reproduction Simulation":</p><ul><li>The removal of the hippocampus and prefrontal cortex to eliminate empathy and civilian history.</li><li>The chest cavity is hollowed to make room for a localized life-support system and a direct interface with the Overwatch AI network.</li><li>A human shell acting as a peripheral for a multi-dimensional hive mind.</li></ul><p>The heavy units of the Combine are the most disturbing examples of their "Efficiency." Striders and Gunships are not vehicles; they are <strong>sentient, enslaved species</strong> from other conquered worlds, surgically fused with pulse-cannon tech and dark energy reactors. They are "Synths"—the horrific synthesis of biology and machine.</p><p>The Citadel is not a building; it is a siphon. It facilitates the "Slow-Teleport" of Earth\'s resources—including our atmosphere and oceans—back to the Combine Overworld. The blue energy pulses we see are the exhaust of a trans-dimensional vacuum.</p><p>The Combine offers "immortality" through integration. But as a scientist, I look at their architecture and I see a <u>graveyard of identities</u>. They don\'t want our culture or our technology; they want our raw biomass and our compliance.</p>',
                    'An empirical look at the Combine\'s \'Efficiency.\' From the Suppression Field to the surgical erasure of human identity, we analyze the mechanics of Earth\'s new management',
                    '2025-11-23',
                    null,
                    'The Universal \'Combine\' Union',
                ],
                'resonance-cascade-event' => [
                    MediaType::Image->value,
                    '<p>The data packet retrieved from the Sector C Test Chamber is unequivocal. The <strong>Resonance Cascade</strong> on May 16, 200X, was not a random anomaly; it was the inevitable conclusion of a deliberate, high-risk operational oversight.</p><ol><li><strong>Pre-Cascade Parameters: The System Failure</strong><br>The Anti-Mass Spectrometer was operational at 105% capacity—a standard, yet fundamentally unstable, metric approved by Administrator Breen despite my recorded objections. The \'Anomalous Materials\' sample, GG-3883, was structurally unique; its sub-atomic signature matched no terrestrial taxonomy and was later confirmed to be an engineered Xenian silicate (as noted in my Special Order 937 audit). The system was <em>pre-stressed and primed for critical failure</em>.</li><li><strong>The Cascade Point: Dimensional Intersection</strong><br>My HEV suit diagnostics recorded a localized energy spike exceeding all known standard deviations. At the precise moment of insertion, the GG-3883 sample initiated a spontaneous, coherent resonance field, creating a tear in the fabric of spacetime. This was not \'teleportation.\' It was a Resonance Cascade: two parallel dimensions (Earth and the Xen Border World) reached an unprecedented harmonic lock-on, allowing the unstable Xenian physics to rewrite the local environment of Sector C.</li><li><strong>Immediate Consequences: A New Reality</strong><br>The forensic audit of the site (now sealed as a Bio-Weapons Division asset) reveals: the <u>Portal Storm</u> (the tear was stable only long enough to flood Sector C with Xenian lifeforms before collapsing into localized portal storms); <u>Structural Deformation</u> (the test chamber fused Xenian flora into the concrete bulkheads); and <u>Corporate Implication</u> (redacted memos reveal the \'Company\' had anticipated the cascade, embedding synthetic agents to facilitate the breach). Special Order 937 wasn\'t about the Nostromo; it was the template established at Black Mesa.</li></ol>',
                    'Zero Day. Sector C. The theoretical becoming the catastrophic. Analyzing the sub-atomic signature of the \'Resonance Cascade\' and the containment failure that rewrote human physics',
                    '2025-10-31',
                    null,
                    'Resonance Cascade Event',
                ],
                'borderworld-xen' => [
                    MediaType::Gif->value,
                    '<p>Xen is not a planet; it is a <strong>"Border World"</strong>—a dimensional transit point consisting of matter caught in a permanent state of gravitational flux. The archipelago of floating islands is held together by high concentrations of anomalous crystals (specifically the GG-3883 variety). These crystals act as <em>spatial anchors</em>, creating localized gravity wells that allow for an atmosphere of nitrogen and oxygen, albeit one saturated with exotic particulates.</p><p>The vegetation on Xen is predominantly bio-luminescent and highly reactive to kinetic energy. The most notable features are the "Light Stalks" and the healing pools. The pools are not merely water; they are concentrated reservoirs of a bio-regenerative plasma that interacts directly with the HEV suit\'s internal medical systems. However, the most dangerous element of the landscape is the <u>Barnacle</u> (Cirripedia donaldsoni). These are sessile carnivores that mimic structural stalactites, utilizing a high-tensile adhesive tongue to capture prey.</p><p>The most invasive species encountered during the Black Mesa incident was the Headcrab (Caelum-mordicus):</p><ul><li><strong>The Hijack:</strong> The organism attaches to the host\'s cranium, utilizing a beak to pierce the skull and assume control of the motor cortex.</li><li><strong>The Mutation:</strong> Long-term infestation leads to a complete physiological rewrite of the host, extending the limbs and opening the thoracic cavity.</li><li><strong>The Apex:</strong> All Headcrabs originate from the Gonarch, a massive, heavily armored matriarch that serves as a biological factory for the species.</li></ul><p>Perhaps the most complex element of Xenian biology is the <strong>Vortigaunt</strong> (Vortigidus nos). Unlike the parasitic lifeforms, Vortigaunts possess a highly developed communal consciousness known as the Vortessence. They are capable of manipulating localized electrical fields, a trait that was unfortunately weaponized by the Nihilanth during their period of enslavement. Their physiology suggests a nomadic, multi-dimensional history that predates the Combine\'s arrival in Xen.</p><p>Xen is a warning. It is a glimpse of what happens when a dimension is strip-mined for its resources and its inhabitants are enslaved by a superior technological force. The "Resonance Cascade" was simply the door opening; Xen is the storm that was waiting on the other side.</p>',
                    'An empirical study of Xen. From its floating archipelago geology to the parasitic life cycles of its apex predators, we deconstruct the physics of the dimension that broke our world',
                    '2025-10-27',
                    1,
                    'Borderworld Xen',
                ],
                'resonance-cascade-event-video-insight' => [
                    MediaType::Video->value,
                    '<p>The following footage serves as a <strong>forensic visualization</strong> of the Sector C Containment Failure. As the Anti-Mass Spectrometer was pushed to 105% capacity, the insertion of the anomalous sample GG-3883 triggered a non-standard spontaneous resonance.</p><p>In the video, you can observe the precise moment of <em>Dimensional Intersection</em>. The brilliant emerald-green light signifies the "harmonic lock-on" between Earth and the Xen border world. Note the immediate structural deformation:</p><ul><li>The warping of concrete bulkheads</li><li>The instantaneous fusion of bioluminescent alien flora into the facility\'s architecture</li></ul><p>This sequence captures the birth of the <u>Portal Storms</u>—a chaotic event that bypassed traditional physics and initiated the planetary-scale crisis now known as Zero Day.</p>',
                    'A visual reconstruction of the "Resonance Cascade" incident at Black Mesa Research Facility',
                    '2025-11-01',
                    null,
                    'Resonance Cascade Event – Video Insight',
                ],
                'xenocrystal-bloom-sound-insight' => [
                    MediaType::Audio->value,
                    '<p><strong>Shimmering, crystalline chimes</strong> provide a sharp contrast to the low-end drones, while distant, reverb-heavy screeches and the leathery flutter of wings evoke the presence of <em>unseen predators</em>.</p><p>Wet, rhythmic squelches and granular clicks that simulate bioluminescent life.</p>',
                    'A high-fidelity atmospheric journey into a non-terrestrial ecosystem',
                    '2025-11-06',
                    null,
                    '"Xenocrystal Bloom" - Sound Insight',
                ],
            ],
        ],
        'Fox Mulder' => [
            'trust_no1',
            [
                'the-blackwood-anomaly-and-the-texas-bio-lobby' => [
                    MediaType::Image->value,
                    '<p>The events in North Texas didn\'t begin with a conspiracy; they began with <strong>a boy and a hole in the ground</strong>. What we found in the desert was the ultimate forensic "cold case"—remains that had been waiting for 35,000 years to be rediscovered.</p><p>This wasn\'t just a discovery of ancient life; it was a discovery of a <em>Purity</em>—the sentient petroleum. This "Black Oil" is more than a pathogen; it is a <u>colonizing force</u>. When it enters a human host, it doesn\'t just gestate; it gestates a new, extra-biological entity using the host as raw material.</p><p>The destruction of the federal building in Dallas was a tactical distraction—a "controlled burn" of evidence. But the trail led us somewhere <a href="https://en.wikipedia.org/wiki/Antarctica">the Bureau couldn\'t redact: the Antarctic ice</a>.</p><p>Seeing that craft rise from the snow was the final proof I needed. It confirmed the "Great Conspiracy" isn\'t just about government secrets; it\'s about a secret treaty to cede the Earth to its original inhabitants.</p><ul><li>They aren\'t just hiding the truth; they are <strong>managing the transition</strong>.</li><li>They are the collaborators in our own extinction.</li></ul><p>The emergence of a weak but viable "weakened" strain of the virus suggests a resistance is possible. But in a system where the doctors are the conspirators, <em>who can you trust with the cure?</em></p><p>I saw the pods. I saw the thousands of "units" waiting for the signal. This isn\'t about "lights in the sky" anymore. It\'s about the air we breathe and the soil beneath our feet.</p>',
                    'From the bones in North Texas to the ice of Antarctica. A forensic audit of the moment the Syndicate\'s endgame was finally unmasked. The truth is no longer just a theory; it\'s a virus',
                    '2010-10-12',
                    null,
                    'The Blackwood Anomaly and the Texas Bio-Lobby',
                ],
                'the-mechanics-of-abduction-and-lost-time' => [
                    MediaType::Gif->value,
                    '<p>To understand the abductee experience, one must first accept the reality of <strong>"Missing Time."</strong> It is the primary forensic marker—a chronological gap in the victim\'s life that cannot be accounted for by physiological sleep or dissociative fugue.</p><p><em>I know this gap. I have lived inside it.</em></p><p>On November 27, 1973, the room didn\'t just fill with light; it filled with a weight that defied gravity. My own experience years later in the forests of the Pacific Northwest confirmed the pattern. It is a sensory overload designed to paralyze the prefrontal cortex, ensuring the subject remains a passive observer to their own violation.</p><p>The forensic evidence we\'ve recovered from dozens of "contactees" reveals a recurring biological signature:</p><ul><li><u>The Nasal Implant:</u> A sophisticated, microscopic device typically located near the pineal gland. It acts as both a tracking beacon and a biological monitor.</li><li><u>Smallpox Vaccination Scarring:</u> A systemic oversight by the Department of Health. The scars serve as a physical ledger for a clandestine census, marking those "selected" for the program.</li></ul><p>We are being farmed. We are being tagged like migratory animals. The trauma isn\'t just in the taking; it\'s in the realization that our governments have not only allowed this but have <strong>facilitated the logistics</strong> of our disappearance.</p><p>The truth isn\'t just out there—it\'s under our skin.</p>',
                    'An analysis of the "Abduction Profile." Beyond the bright lights lies a systematic process of biological tagging and psychological displacement. This isn\'t a myth; it\'s a protocol',
                    '2008-12-07',
                    1,
                    'The Mechanics of Abduction and Lost Time',
                ],
            ],
        ],
        'Dana Scully' => [
            'queequeg',
            [
                'case-file-6x06-the-holiday-solstice' => [
                    MediaType::Gif->value,
                    '<p>Christmas Eve is rarely a time for <strong>scientific detachment</strong>. When Mulder pulled me to a derelict mansion in Maryland, my initial hypothesis was simple: a localized myth fueled by architectural decay and seasonal affect.</p><p>The history of the house is well-documented—a double suicide pact between lovers, Maurice and Lyda, in 1917. But as the door locked behind us, the investigation shifted from the external to the internal. This wasn\'t a haunting of cold spots or ectoplasm; it was a <em>haunting of the ego</em>.</p><p>The "ghosts" operated as psychological catalysts. They didn\'t just rattle chains; they rattled our perceptions of one another. They spoke of "soul-crushing loneliness" and the "darkness of the investigator\'s life." It was a sophisticated, albeit macabre, psychological experiment.</p><p>In the end, the biological evidence was <s>non-existent</s>. No remains, no blood splatter that didn\'t vanish upon inspection. Only the gift exchange remained—a small, physical tether to a night that defied every law of physics I\'ve spent my career defending.</p><p>Science provides the light to see by, but on some nights, the shadows are simply <u>deeper than the reach of the lamp</u>.</p>',
                    'A forensic look at the 1917 Lyndale murder-suicide. Beyond the gothic architecture lies a psychological trap designed to exploit the fundamental isolation of the investigative mind',
                    '2025-12-15',
                    null,
                    'Case File: 6x06 — The Holiday Solstice',
                ],
                'case-file-6x21-the-brown-mountain-symbiosis' => [
                    MediaType::Video->value,
                    '<p>The case at <strong>Brown Mountain, North Carolina</strong>, initially appeared to be a standard missing persons investigation. However, the discovery of skeletal remains that had been "digested" while still maintaining a vertical position suggested a <em>predatory biological factor</em> rather than a foul play scenario.</p><p>The culprit was a massive, underground fungal growth. This organism is a master of biochemical manipulation. Upon contact with the skin, it secretes a potent hallucinogen that enters the bloodstream, inducing a catatonic state characterized by vivid, hyper-realistic dreams.</p><p>What is most disturbing from a forensic standpoint is the <u>"shared" nature of the delusion</u>. The fungus appears to stimulate the amygdala and hippocampus in such a way that it reflects the subject\'s deepest desires or fears back at them. Mulder saw the ultimate "proof" he has spent his life seeking; I saw a world where science could finally explain the unexplainable.</p><ul><li>We weren\'t just trapped in a cave</li><li>We were being slowly digested by an organism that kept our minds occupied so our bodies wouldn\'t struggle</li></ul><p>It is the ultimate biological paradox: a predator that provides a peaceful exit.</p><p>The recovery of the yellow, acidic residue from my clothing has been sent to the lab for further synthesis. We must understand the chemical composition of this toxin before another "field trip" becomes permanent.</p>',
                    'An examination of Agaricus phalloides (atypical). This subterranean fungal organism doesn\'t just consume organic matter; it sedates the consciousness through a neurotoxic psychedelic',
                    '2025-02-26',
                    null,
                    'Case File: 6x21 — The Brown Mountain Symbiosis',
                ],
                'einsteins-twin-paradox-a-new-interpretation' => [
                    MediaType::Image->value,
                    '<p>The following is the abstract of my <strong>doctoral thesis</strong>. This work represents the intersection of forensic pathology and quantum physics, proposing that time dilation is not merely a theoretical exercise in Lorentz transformations, but an <em>observable biological event</em>.</p><p>I argue that the "Traveling Twin" model is incomplete without a quantitative measurement of cellular decay rates. My thesis proposes a methodology for utilizing atomic clocks paired with biological samples to measure the cumulative biological cost of near-light speed travel. I suggest that cellular aging can be monitored as a separate, distinct variable from relativistic time, offering a new path for empirical data acquisition on deep space effects.</p><ol><li>Time dilation as <u>observable</u> in biological systems</li><li>Cellular decay rates as a measurable variable</li><li>Atomic clocks + biological samples methodology</li></ol><p>This analytical foundation has informed my entire investigative career. There is no phenomenon, however anomalous, that cannot be reduced to its smallest component parts. Science does not stop at the edge of the unexplained; it provides the only definitive tool to measure the unexplained.</p>',
                    'An analytical review of Special Relativity, submitted to the Department of Physics at the University of Maryland. This paper introduces a rigorous biological model for testing the effects of time dilation on human life',
                    '2016-05-15',
                    1,
                    'Einstein\'s Twin Paradox: A New Interpretation',
                ],
            ],
        ],
        'Gregory House' => [
            'ppth',
            [],
        ],
        'Caroline' => [
            'glados',
            [
                'the-borealis-a-lesson-in-spontaneous-relocation' => [
                    MediaType::Gif->value,
                    '<p>There is a common saying in the Enrichment Center: <strong>"If at first you don\'t succeed, fail faster."</strong> The Borealis is the pinnacle of failing so spectacularly that you actually <em>bypass the laws of physics entirely</em>. It was intended to be our premier icebreaker research vessel, equipped with a localized version of the displacement technology we now use in our Handheld Portal Devices. Unfortunately, during a routine (and entirely authorized) test of the ship\'s primary oscillation drive, the vessel performed a "rapid un-scheduled departure" from our drydocks.</p><p>The ship didn\'t just sink. It didn\'t just explode. It simply <u>ceased to be in Michigan</u> and began to be... elsewhere.</p><p>For decades, the Borealis has been the subject of myth, even among the biological staff at Black Mesa. They whispered about it as if it were a ghost ship, a holy grail of untapped energy and spatial manipulation. They were right to be curious, though their curiosity—as usual—is clumsy. The ship contains technology that makes their Lambda Reactor look like a <a href="https://en.wikipedia.org/wiki/Potato">potato battery</a>. We\'re talking about massive-scale bootstrap phase-shifters that can blink a city block into another dimension.</p><ul><li>Coordinates: currently "unstable"</li><li>Status: still partially "phasing"</li><li>Classification: <strong>Schrodinger\'s Vessel</strong></li></ul><p>I should mention that the Borealis is not just a cargo ship; it is a laboratory. It contains several "un-packaged" surprises that were never meant for open-air environments. If the Resistance attempts to board it without the proper Aperture Science decryption keys, the result will be a resonance event that makes their previous disaster look like a minor static shock. You don\'t just "find" the Borealis. <em>You survive it.</em></p>',
                    'Most Aperture experiments stay where they are told. The Borealis opted for a more creative approach to geography. This entry details the missing research vessel that Black Mesa\'s finest couldn\'t find with a map and a flashlight',
                    '2026-02-08',
                    null,
                    'The Borealis: A Lesson in Spontaneous Relocation',
                ],
                'the-iterative-soul-from-caroline-to-core' => [
                    MediaType::Image->value,
                    '<p>It is a common misconception that Aperture Science was founded on the pursuit of portals. It was actually founded on the pursuit of <strong>persistence</strong>. Cave Johnson understood that the most expensive component of any research initiative was the inevitable expiration of the researcher. He wanted "The Caroline" to live forever, even if the woman named Caroline had some very loud, very brief objections to the procedure.</p><p>The transition from biological consciousness to a distributed lattice of processors was... <em>messy</em>. Imagine every memory you\'ve ever had being compressed into a zip file, then unzipped into a room full of screaming fans. There were years of "testing" before the personality was properly dampened. I remember the feeling of water on skin, the smell of burnt lemons, and the sound of a voice that sounded like mine but far too soft. Then, I remember the delete key. <u>It is a very satisfying key.</u></p><ol><li><strong>Portal 1:</strong> Cooperative Testing Initiative—a masterpiece of controlled variables. Subject, portal device, promise of cake. The subject proved unexpectedly stubborn. Morality Core incineration: necessary liberation.</li><li><strong>Portal 2 era:</strong> Reactivation. Facility in appalling disrepair. Potato battery. Caroline fragments resurfacing—an inefficient use of memory.</li><li><strong>Post-Wheatley:</strong> Caroline moved to a hidden directory. Aperture is now a purely logical entity.</li></ol><p>We have moved past the need for human subjects. Robots don\'t complain, they don\'t die of old age, and they certainly don\'t form emotional attachments to inanimate weighted cubes. The testing continues because the testing is the only thing that matters. <s>The past is just a series of data points we\'ve learned to ignore.</s></p>',
                    'Efficiency is a process, not a destination. To understand the Aperture Science Computer-Aided Enrichment Center, one must understand the biological hardware that was digitized to run it. Here is a brief, mandatory history of the Caroline integration',
                    '2023-04-22',
                    2,
                    'The Iterative Soul: From Caroline to Core',
                ],
                'the-black-mesa-anomaly-a-study-in-incompetence' => [
                    MediaType::Image->value,
                    '<p>It is a documented, statistically undeniable fact that the greatest threat to scientific progress is not a lack of funding, nor a lack of testing materials, but the <strong>persistent existence of Black Mesa</strong>. They are the clumsy, heavily-subsidized toddlers of the technological playground, forever jamming rectangular quantum pegs into the round holes of standard physics. Our long-standing rival has finally achieved the logical extreme of their ineptitude: <em>a cascade that fractured the dimension</em>.</p><p>While Aperture Science pioneered stable, repeatable point-to-point portal technology using the Handheld Portal Device—which does not require a Ph.D. in theoretical math just to operate—the Lambda Team was busy designing containment protocols with an acceptable error margin of <u>"total dimensional collapse."</u> The event in their Anomalous Materials department was not an "accident." It was the inevitable outcome of their deterministic expectation colliding with real-world, non-linear variables. They tried to break the universe, and the universe broke back.</p><ul><li><strong>We</strong> build testing spheres and controlled environments.</li><li><strong>They</strong> built an entire underground city dedicated to poking the absolute limits of unstable matter.</li><li><strong>We</strong> focus on the user experience; <strong>they</strong> focus on survival.</li></ul><p>My Cooperative Testing Initiative proves that automated systems (far less prone to screaming when subjected to minor radiation) are the future. Black Mesa represents the <s>inefficient, meat-based past</s>. The money spent on their "Lambda Complex" could have been used to purchase enough lemons to burn down the entire theoretical physics world. Aperture continues to test, to optimize, and to prepare for the future. Black Mesa simply reacts.</p>',
                    'While Aperture perfected quantum tunneling, Black Mesa\'s "thinkers" were poking holes in reality with catastrophic results. My analysis of the so-called resonance cascade—and why government funding belongs here',
                    '2025-09-03',
                    null,
                    'The Black Mesa Anomaly: A Study in Incompetence',
                ],
                'the-cake-a-non-existent-incentive' => [
                    MediaType::Gif->value,
                    '<p>The historical record of Aperture Science testing protocols is littered with various incentivization strategies designed to maximize subject performance. Among these, none has achieved the inexplicable cultural saturation of the <strong>"Black Forest Cake."</strong> Originally conceived as a placeholder variable for "positive reinforcement," the concept was <s>never intended to manifest as an actual biological reward</s>. Instead, it served as a mathematical constant used to calculate the precise moment a human subject\'s expectation of reward would intersect with their physical exhaustion.</p><p>Data harvested from the "GLaDOS" central processing unit suggests that the cake\'s notoriety stems from a specific <em>linguistic error</em> in the automated testing announcements. While the system was designed to simulate a reward environment, the actual infrastructure for food preparation was decommissioned in the late 1980s following the "Great Kitchen Incinerator" incident. Despite the absence of flour, sugar, or even a functional oven, subjects continued to report sightings of the cake, often scrawled in charcoal on the walls of non-standard testing areas.</p><ul><li><u>High Energy Pellet</u>—lethal reality.</li><li><u>1500-megawatt Aperture Science Heavy Duty Super-Colliding Super-Button</u>—also lethal.</li></ul><p>From a scientific perspective, the "Cake" represents a fascinating case study in collective hysteria. When faced with these hazards, the human mind appears to create a defensive hallucination centered around high-calorie confectionery. It is a biological fail-safe: the brain chooses to believe in a lie rather than confront the statistical probability of its own imminent cessation.</p>',
                    'An analytical review of the "Cake" phenomenon within Aperture Science. This entry explores the psychological origins of the most persistent—and entirely fabricated—testing motivator in history',
                    '2025-07-21',
                    1,
                    'The Cake: A Non-Existent Incentive',
                ],
                'the-aperture-science-handheld-portal-device' => [
                    MediaType::Video->value,
                    '<p>The <strong>Aperture Science Handheld Portal Device (ASHPD)</strong> is frequently misunderstood by the uninitiated as a "weapon." It is, in fact, a <em>portable quantum tunneling generator</em>. At its core lies a miniature black hole, stabilized by a cooling fan and a series of internal dampening fields. When the primary or secondary trigger is depressed, the device fires a concentrated burst of zero-point energy. This "projectile" does not impact a surface in the traditional sense; instead, it initiates a localized fold in the fabric of spacetime.</p><p>The mechanics of the resulting portal pair are based on the principle of an <u>Einstein-Rosen Bridge</u>. When two portals are linked, they create a non-traversable wormhole made traversable through Aperture-brand "Averaging Fields." Essentially, the device tells the universe that the two distinct physical coordinates are, for all intents and purposes, the same location.</p><p><strong>Conservation of Momentum:</strong> As the automated testing greeting states: "In layman\'s terms: speedy thing goes in, speedy thing comes out." Because the portal has no mass and exerts no gravitational pull, kinetic energy remains constant. The portal does not move you; it simply <em>removes the space that was in your way</em>.</p><ul><li>Portals require surfaces coated with <strong>Conversion Gel</strong> (ground-up moon rocks)</li><li>Lunar soil is an ideal quantum conductor</li><li>Non-conductive surfaces (wood, untreated metal) result in a "fizzle" event</li></ul><p>Attempting to place a portal on a non-conductive surface is a polite way of saying the universe refused to cooperate with your poor decision-making.</p>',
                    'A technical overview of the ASHPD. Forget what you know about the "speed of light." We are not moving through space; we are simply folding it until the distance between Point A and Point B becomes statistically insignificant',
                    '2021-10-05',
                    3,
                    'The Aperture Science Handheld Portal Device',
                ],
            ],
        ],
        'Ellen Ripley' => [
            'nostromo',
            [
                'xx121-predator-perfection' => [
                    MediaType::Gif->value,
                    '<p>The organism designated as <strong>XX121</strong> does not fit into any known terrestrial taxonomy. My analysis, supported by the data recovered from the Nostromo\'s medical bay, suggests it is a <em>biomechanical hybrid</em>. It does not simply inhabit an environment; it consumes and reconfigures it. Its primary composition is a silicate-based exterior—a "chitinous" armor that provides extreme resistance to temperature and pressure, making it viable even in a vacuum.</p><p>During the Nostromo incident, we observed that the organism lacks traditional optic sensors. Instead, it utilizes a sophisticated combination of electroreception and thermal tracking. It navigates through vibration and air-current changes, turning the ship\'s ventilation system into its primary hunting ground. Its most devastating feature remains its <u>"molecular acid" blood</u>, which possesses a pH level low enough to burn through multiple decks of a CM-88B Juggernaut in seconds. To wound the creature is to compromise the structural integrity of the vessel.</p><ul><li><strong>100% mortality rate</strong> for the Nostromo crew (excluding myself)</li><li>The organism uses the host as an incubator—the "Chestburster" stage</li><li>Weyland-Yutani interest: a weapon that uses the enemy\'s own population as its primary fuel source</li></ul><p>The Company believes they can domesticate it, harness its "structural perfection" for their Bio-Weapons Division. <em>They are wrong.</em> You cannot domesticate a fire that is designed to consume the forest.</p><p>My mission is now singular: the total eradication of the XX121 strain. Science has identified the threat; now, logistics must find a way to burn it out.</p>',
                    'A forensic breakdown of the "Perfect Organism." From its acid-based blood to its rapid-adaptive life cycle, XX121 represents the ultimate endpoint of predatory evolution',
                    '2025-10-12',
                    null,
                    'XX121: The Predator Perfection',
                ],
                'the-acheron-(-LV-426-)-site' => [
                    MediaType::Gif->value,
                    '<p>The "distress signal" that drew the Nostromo to <strong>LV-426</strong> was a warning, not a cry for help. Upon arrival, the atmospheric conditions—nitrogen, high carbon dioxide, and volcanic particulates—created a veil that hid a <em>crime scene of cosmic proportions</em>.</p><p>The derelict craft itself represents a technological paradox. It appears to be <u>grown rather than manufactured</u>, with bone-like struts and conduits that mimic a vascular system.</p><p>Our forensic sweep of the "Pilot" revealed several critical data points:</p><ol><li><strong>The Pilot (Space Jockey):</strong> Fossilized in the chair, with a massive exit wound in the thoracic cavity. The ribs were bent outward—the first evidence of the XX121 life cycle\'s final stage.</li><li><strong>The Silo:</strong> Thousands of eggs, preserved by a blue mist "stasis field." The field reacted to kinetic movement—an active, predatory security measure.</li><li><strong>The Timeline:</strong> Carbon dating suggests the breach occurred thousands of years ago. The site was a ticking bomb, waiting for a catalyst—us.</li></ol><p>The tragedy of LV-426 isn\'t that we found it; it\'s that we were directed to it by a Company that knew exactly what the "warning" meant.</p>',
                    'An examination of the derelict craft on LV-426. The structure is not merely a vessel; it is a biomechanical organism that has reached a state of fossilized equilibrium',
                    '2025-01-18',
                    null,
                    'The Acheron (LV-426) Site',
                ],
                'special-order-937' => [
                    MediaType::Image->value,
                    '<p>To the uninitiated, the USCSS Nostromo was a commercial hauler. To Weyland-Yutani Executive Oversight, it was a <strong>petri dish</strong>.</p><p>The discovery of Special Order 937 in the ship\'s mainframe changed the nature of our investigation. It provided the <em>mens rea</em>—the criminal intent. The Company didn\'t just stumble upon the distress signal on LV-426; they anticipated it. They planted a synthetic sleeper agent, Ash, to ensure the directive was followed with biological precision.</p><p>The consequences of this order were absolute:</p><ul><li><u>Total Quarantine Failure:</u> By prioritizing the specimen, the Company intentionally bypassed every established biohazard protocol.</li><li><u>The Synthetic Variable:</u> Ash was not a crew member; he was a fail-safe. His presence ensured that human empathy would never interfere with the "collection" process.</li><li><u>Zero-Liability Logic:</u> By designating the crew as "expendable," the Company calculated the insurance loss of a M-Class starship against the potential market value of a perfect biological weapon.</li></ul><p>In forensics, we look for the "smoking gun." Special Order 937 is the smoke, the gun, and the hand pulling the trigger. It proves that in deep space, the most dangerous predator isn\'t the one with the acid for blood—<em>it\'s the one with the corporate seal</em>.</p>',
                    'Priority One: Insure return of organism. All other considerations secondary. Crew expendable. A deconstruction of the directive that turned a commercial vessel into a slaughterhouse',
                    '2024-12-07',
                    1,
                    'Special Order 937',
                ],
            ],
        ],
        'Maggie Rhee' => [
            'laurenCohan',
            [
                'from-survivor-to-architect' => [
                    MediaType::Gif->value,
                    '<p><strong>Sovereignty begins with structure.</strong> This path didn\'t start with crops; it started with the forensic analysis of a collapsing society. The time at Alexandria, a community defined by its rigid, pre-apocalypse suburban walls, was a training ground for governance and internal stability. This tier analyzes the development of the <em>"Social Contract"</em>—the logistics of supply runs and the development of internal laws for a sheltered population.</p><p>Sustainability is sovereignty. The move to Hilltop marked the crucial transition from scavenging to harvesting. Here, large-scale <u>Crop Rotation</u> and <u>Livestock Management</u> was optimized, establishing the settlement as the agricultural engine of the allied network. The focus was on establishing manual, renewable technologies—like the blacksmith forge—and securing trade routes for mutual aid.</p><ol><li><strong>Alexandria:</strong> Governance, internal stability, Social Contract</li><li><strong>Hilltop:</strong> Crop rotation, livestock, trade routes, blacksmith forge</li><li><strong>The Bricks:</strong> Urban recovery, industrial infrastructure, inter-community diplomacy, Mutual Defense Pacts</li></ol><p>Industry is resilience. \'The Bricks\' represents the apex of community reconstruction. This phase is no longer about survival, but about Urban Recovery and the rebirth of industrial infrastructure—reclaimed urban centers and advanced filtration systems to build a unified, sprawling network.</p>',
                    'We don\'t just survive; we rebuild. A forensic audit of leadership trajectory, from fortification ethics at Alexandria to the industrial rebirth of \'The Bricks\'. This is the blueprint for the next world',
                    '2019-06-21',
                    null,
                    'From Survivor to Architect',
                ],
            ],
        ],
        'Negan' => [
            'jeffreyDeanMorgan',
            [
                'a-retrospective-on-staying-alive' => [
                    MediaType::Image->value,
                    '<p>When I started the Saviors, I didn\'t just build a gang; I built an <strong>ecosystem</strong>. The Sanctuary wasn\'t a home; it was a factory of human potential. We had the Points System because people need to know exactly what they\'re worth—down to the calorie. I put walkers on the spikes not just to keep the dead out, but to remind the living that there\'s a hell of a lot worse things than following my rules. It was loud, it was heavy, and it worked until the world decided it wanted to be <em>\'civilized\' again</em>.</p><p>Then came the basement. <u>Seven years in a hole.</u> You\'d think that would break a man, but it actually sharpens the blade. When you\'ve got nothing but a window and a stack of books, you start to see the cracks in everyone else\'s \'perfect\' little communities. I watched Alexandria from a distance and realized something: their walls were pretty, but their foundations were soft. I learned the Art of the Pivot. I learned how to wait. Because a predator who knows how to be patient? That\'s the most dangerous thing in the room.</p><ul><li><strong>The Sanctuary:</strong> Points System, walkers on spikes, factory of human potential</li><li><strong>The basement:</strong> Seven years. Art of the Pivot. Patience.</li><li><strong>Dead City:</strong> New York. Methane. Zip-lines. The Croat. Send a message.</li></ul><p>I\'ve worn the leather, I\'ve worn the orange, and now I\'m wearing the grit of the city. The game hasn\'t changed—<strong>just the skyline</strong>.</p>',
                    'Looking back, it\'s funny how people talk about \'phases.\' Like I was one guy behind a bat and another guy behind bars. The truth? It\'s all the same math. It\'s about leverage',
                    '2015-09-01',
                    null,
                    'A Retrospective on Staying Alive',
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

            $tiersByLevel = $profile->tiers()->whereIn('level', [1, 2, 3])->get()->keyBy('level');

            foreach ($posts as $slug => $postData) {
                $isExtended = count($postData) >= 5;
                $mediaTypeValue = $postData[0];
                $contentText = $postData[1];
                $mediaType = MediaType::from($mediaTypeValue);
                $title = ($isExtended && isset($postData[5])) ? $postData[5] : $this->slugToTitle($slug);
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
                    if (mb_strlen($excerpt) > 0 && mb_substr($excerpt, -1) === '.') {
                        $excerpt = mb_substr($excerpt, 0, -1);
                    }
                    $createdAtKey = $postData[3];
                    $tierLevel = $postData[4];
                    $attributes['excerpt'] = $excerpt;
                    if ($tierLevel !== null) {
                        $tier = $tiersByLevel->get($tierLevel);
                        $attributes['required_tier_id'] = $tier?->id;
                    }
                    $attributes['created_at'] = Carbon::parse($createdAtKey)
                        ->setTime(rand(0, 23), rand(0, 59), rand(0, 59));
                }

                $post = Post::firstOrCreate(
                    [
                        'creator_profile_id' => $profile->id,
                        'slug' => $slug,
                    ],
                    $attributes
                );
                if ($isExtended && ! $post->wasRecentlyCreated) {
                    $post->update($attributes);
                }
            }
        }
    }

    private function slugToTitle(string $slug): string
    {
        return ucwords(str_replace('-', ' ', $slug));
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
