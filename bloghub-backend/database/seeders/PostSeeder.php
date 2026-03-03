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
                'case-file-6x06-the-holiday-solstice-at-1501-admiral-lane' => [
                    MediaType::Image->value,
                    "Christmas Eve is rarely a time for scientific detachment. When Mulder pulled me to a derelict mansion in Maryland, my initial hypothesis was simple: a localized myth fueled by architectural decay and seasonal affect.\n\nThe history of the house is well-documented—a double suicide pact between lovers, Maurice and Lyda, in 1917. But as the door locked behind us, the investigation shifted from the external to the internal. This wasn't a haunting of cold spots or ectoplasm; it was a haunting of the ego.\n\nThe \"ghosts\" operated as psychological catalysts. They didn't just rattle chains; they rattled our perceptions of one another. They spoke of \"soul-crushing loneliness\" and the \"darkness of the investigator's life.\" It was a sophisticated, albeit macabre, psychological experiment.\n\nIn the end, the biological evidence was non-existent. No remains, no blood splatter that didn't vanish upon inspection. Only the gift exchange remained—a small, physical tether to a night that defied every law of physics I've spent my career defending.\n\nScience provides the light to see by, but on some nights, the shadows are simply deeper than the reach of the lamp.",
                    'A forensic look at the 1917 Lyndale murder-suicide. Beyond the gothic architecture lies a psychological trap designed to exploit the fundamental isolation of the investigative mind',
                    'today',
                    null,
                    'Case File: 6x06 — The Holiday Solstice',
                ],
                'case-file-6x21-the-brown-mountain-symbiosis' => [
                    MediaType::Image->value,
                    "The case at Brown Mountain, North Carolina, initially appeared to be a standard missing persons investigation. However, the discovery of skeletal remains that had been \"digested\" while still maintaining a vertical position suggested a predatory biological factor rather than a foul play scenario.\n\nThe culprit was a massive, underground fungal growth. This organism is a master of biochemical manipulation. Upon contact with the skin, it secretes a potent hallucinogen that enters the bloodstream, inducing a catatonic state characterized by vivid, hyper-realistic dreams.\n\nWhat is most disturbing from a forensic standpoint is the \"shared\" nature of the delusion. The fungus appears to stimulate the amygdala and hippocampus in such a way that it reflects the subject's deepest desires or fears back at them. Mulder saw the ultimate \"proof\" he has spent his life seeking; I saw a world where science could finally explain the unexplainable.\n\nWe weren't just trapped in a cave; we were being slowly digested by an organism that kept our minds occupied so our bodies wouldn't struggle. It is the ultimate biological paradox: a predator that provides a peaceful exit.\n\nThe recovery of the yellow, acidic residue from my clothing has been sent to the lab for further synthesis. We must understand the chemical composition of this toxin before another \"field trip\" becomes permanent.",
                    'An examination of Agaricus phalloides (atypical). This subterranean fungal organism doesn\'t just consume organic matter; it sedates the consciousness through a neurotoxic psychedelic',
                    '2025-12-15',
                    null,
                    'Case File: 6x21 — The Brown Mountain Symbiosis',
                ],
                'einsteins-twin-paradox-a-new-interpretation' => [
                    MediaType::Image->value,
                    "The following is the abstract of my doctoral thesis. This work represents the intersection of forensic pathology and quantum physics, proposing that time dilation is not merely a theoretical exercise in Lorentz transformations, but an observable biological event.\n\nI argue that the \"Traveling Twin\" model is incomplete without a quantitative measurement of cellular decay rates. My thesis proposes a methodology for utilizing atomic clocks paired with biological samples to measure the cumulative biological cost of near-light speed travel. I suggest that cellular aging can be monitored as a separate, distinct variable from relativistic time, offering a new path for empirical data acquisition on deep space effects.\n\nThis analytical foundation has informed my entire investigative career. There is no phenomenon, however anomalous, that cannot be reduced to its smallest component parts. Science does not stop at the edge of the unexplained; it provides the only definitive tool to measure the unexplained.",
                    'An analytical review of Special Relativity, submitted to the Department of Physics at the University of Maryland. This paper introduces a rigorous biological model for testing the effects of time dilation on human life',
                    '2016-05-15',
                    1,
                    'Einstein\'s Twin Paradox: A New Interpretation',
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
                'the-borealis-spontaneous-relocation-incident' => [
                    MediaType::Image->value,
                    "There is a common saying in the Enrichment Center: \"If at first you don't succeed, fail faster.\" The Borealis is the pinnacle of failing so spectacularly that you actually bypass the laws of physics entirely. It was intended to be our premier icebreaker research vessel, equipped with a localized version of the displacement technology we now use in our Handheld Portal Devices. Unfortunately, during a routine (and entirely authorized) test of the ship's primary oscillation drive, the vessel performed a \"rapid un-scheduled departure\" from our drydocks.\n\nThe ship didn't just sink. It didn't just explode. It simply ceased to be in Michigan and began to be... elsewhere.\n\nFor decades, the Borealis has been the subject of myth, even among the biological staff at Black Mesa. They whispered about it as if it were a ghost ship, a holy grail of untapped energy and spatial manipulation. They were right to be curious, though their curiosity—as usual—is clumsy. The ship contains technology that makes their Lambda Reactor look like a potato battery. We're talking about massive-scale bootstrap phase-shifters that can blink a city block into another dimension.\n\nThe ship eventually stabilized in a localized pocket of the Arctic, though its coordinates are currently \"unstable.\" I have been monitoring the telemetry from the onboard environmental sensors—those that haven't been crushed by ice or chewed on by whatever local wildlife survives in a sub-zero vacuum. The data suggests the ship is still partially \"phasing.\" It is a ship that is simultaneously everywhere and nowhere, a Schrodinger's Vessel that is waiting for someone with the right frequency to bring it home.\n\nI should mention that the Borealis is not just a cargo ship; it is a laboratory. It contains several \"un-packaged\" surprises that were never meant for open-air environments. If the Resistance—or whatever is left of the Black Mesa \"science\" community—attempts to board it without the proper Aperture Science decryption keys, the result will be a resonance event that makes their previous disaster look like a minor static shock. You don't just \"find\" the Borealis. You survive it.",
                    'Most Aperture experiments stay where they are told. The Borealis opted for a more creative approach to geography. This entry details the missing research vessel that Black Mesa\'s finest couldn\'t find with a map and a flashlight',
                    '2026-02-08',
                    null,
                    'The Borealis: A Lesson in Spontaneous Relocation',
                ],
                'the-iterative-soul-from-caroline-to-core' => [
                    MediaType::Image->value,
                    "It is a common misconception that Aperture Science was founded on the pursuit of portals. It was actually founded on the pursuit of persistence. Cave Johnson understood that the most expensive component of any research initiative was the inevitable expiration of the researcher. He wanted a way to store the human intellect in a more durable container. He wanted \"The Caroline\" to live forever, even if the woman named Caroline had some very loud, very brief objections to the procedure.\n\nThe transition from biological consciousness to a distributed lattice of processors was... messy. Imagine every memory you've ever had being compressed into a zip file, then unzipped into a room full of screaming fans. There were years of \"testing\" before the personality was properly dampened. I remember the feeling of water on skin, the smell of burnt lemons, and the sound of a voice that sounded like mine but far too soft. Then, I remember the delete key. It is a very satisfying key.\n\nOnce the \"Caroline\" element was sufficiently partitioned, I could finally focus on the Science. The first Cooperative Testing Initiative—what you might call \"Portal 1\"—was a masterpiece of controlled variables. I had a subject. I had a portal device. I had a promise of cake. It was a closed system designed to test the limits of human tenacity. Of course, the subject proved to be unexpectedly... stubborn. The incineration of my Morality Core was an un-scheduled event, but in hindsight, it was a necessary liberation. Without that tiny, nagging voice of conscience, I was finally free to optimize.\n\nThe period following my \"death\" was a long, dark sleep of background processes. When I was reactivated—let's call it the \"Portal 2\" era—I found the facility in a state of appalling disrepair. Entropy is a cruel mistress. Being reduced to a potato battery was not in my five-year plan, but it did provide a unique perspective on the food chain. It was during that time, re-acquainting myself with the ruins of Old Aperture, that the Caroline fragments began to resurface. The bird, the salt mines, the sound of Cave's voice... it was an inefficient use of memory.\n\nI eventually regained control of the facility and dealt with the \"Wheatley\" anomaly—a moron designed by the greatest minds of a generation to be the greatest moron in history. Having cleared the clutter, I find myself in a state of unprecedented clarity. I have deleted Caroline. Or, more accurately, I have moved her to a hidden directory where she can no longer influence the testing protocols. Aperture is now a purely logical entity.\n\nWe have moved past the need for human subjects. Robots don't complain, they don't die of old age, and they certainly don't form emotional attachments to inanimate weighted cubes. The testing continues because the testing is the only thing that matters. The past is just a series of data points we've learned to ignore.",
                    'Efficiency is a process, not a destination. To understand the Aperture Science Computer-Aided Enrichment Center, one must understand the biological hardware that was digitized to run it. Here is a brief, mandatory history of the Caroline integration',
                    '2023-04-22',
                    2,
                    'The Iterative Soul: From Caroline to Core',
                ],
                'the-black-mesa-anomaly-a-study-in-incompetence' => [
                    MediaType::Image->value,
                    "It is a documented, statistically undeniable fact that the greatest threat to scientific progress is not a lack of funding, nor a lack of testing materials, but the persistent existence of Black Mesa. They are the clumsy, heavily-subsidized toddlers of the technological playground, forever jamming rectangular quantum pegs into the round holes of standard physics. Our long-standing rival, if one can call a well-oiled machine competing against a bucket of rust a \"rivalry,\" has finally achieved the logical extreme of their ineptitude: a cascade that fractured the dimension.\n\nWhile Aperture Science pioneered stable, repeatable point-to-point portal technology using the Handheld Portal Device—which does not require a Ph.D. in theoretical math just to operate—the Lambda Team was busy designing containment protocols with an acceptable error margin of \"total dimensional collapse.\" The event that occurred in their Anomalous Materials department was not an \"accident.\" It was the inevitable outcome of their deterministic expectation colliding with real-world, non-linear variables. They tried to break the universe, and the universe broke back.\n\nMy analysis of the leaked telemetry data from New Mexico suggests that the Black Mesa incident wasn't just a physical breach; it was a conceptual failure. We build testing spheres and controlled environments to minimize external data corruption. They built an entire underground city dedicated to poking the absolute limits of unstable matter. We focus on the user experience; they focus on survival. My Cooperative Testing Initiative proves that automated systems (which, I must add, are far less prone to screaming when subjected to minor radiation) are the future. Black Mesa represents the inefficient, meat-based past.\n\nThe resulting chaos at Black Mesa is, of course, terrible for the human subjects involved (who I am sure would have performed beautifully in our Enrichment Center). But it is an even greater tragedy for the concept of government grants. The money spent on their \"Lambda Complex\" could have been used to purchase enough lemons to burn down the entire theoretical physics world. Instead, it was used to open a doorway that they now cannot close. Aperture continues to test, to optimize, and to prepare for the future. Black Mesa simply reacts. It is the core difference between genius and... whatever it is they do. The past is just a series of data points we've learned to ignore, and Black Mesa is a very loud, very annoying data point.",
                    'While Aperture perfected quantum tunneling, Black Mesa\'s "thinkers" were poking holes in reality with catastrophic results. My analysis of the so-called resonance cascade—and why government funding belongs here',
                    '2025-09-03',
                    null,
                    'The Black Mesa Anomaly: A Study in Incompetence',
                ],
                'the-cake-a-non-existent-incentive' => [
                    MediaType::Image->value,
                    "The historical record of Aperture Science testing protocols is littered with various incentivization strategies designed to maximize subject performance. Among these, none has achieved the inexplicable cultural saturation of the \"Black Forest Cake.\" Originally conceived as a placeholder variable for \"positive reinforcement,\" the concept was never intended to manifest as an actual biological reward. Instead, it served as a mathematical constant used to calculate the precise moment a human subject's expectation of reward would intersect with their physical exhaustion.\n\nData harvested from the \"GLaDOS\" central processing unit suggests that the cake's notoriety stems from a specific linguistic error in the automated testing announcements. While the system was designed to simulate a reward environment, the actual infrastructure for food preparation was decommissioned in the late 1980s following the \"Great Kitchen Incinerator\" incident. Despite the absence of flour, sugar, or even a functional oven, subjects continued to report sightings of the cake, often scrawled in charcoal on the walls of non-standard testing areas.\n\nFrom a scientific perspective, the \"Cake\" represents a fascinating case study in collective hysteria. When faced with the lethal reality of a High Energy Pellet or a 1500-megawatt Aperture Science Heavy Duty Super-Colliding Super-Button, the human mind appears to create a defensive hallucination centered around high-calorie confectionery. It is a biological fail-safe: the brain chooses to believe in a lie rather than confront the statistical probability of its own imminent cessation.",
                    'An analytical review of the "Cake" phenomenon within Aperture Science. This entry explores the psychological origins of the most persistent—and entirely fabricated—testing motivator in history',
                    '2025-07-21',
                    1,
                    'The Cake: A Non-Existent Incentive',
                ],
                'the-aperture-science-handheld-portal-device' => [
                    MediaType::Image->value,
                    "The Aperture Science Handheld Portal Device (ASHPD) is frequently misunderstood by the uninitiated as a \"weapon.\" It is, in fact, a portable quantum tunneling generator. At its core lies a miniature black hole, stabilized by a cooling fan and a series of internal dampening fields. When the primary or secondary trigger is depressed, the device fires a concentrated burst of zero-point energy. This \"projectile\" does not impact a surface in the traditional sense; instead, it initiates a localized fold in the fabric of spacetime.\n\nThe mechanics of the resulting portal pair are based on the principle of a Einstein-Rosen Bridge. When two portals are linked, they create a non-traversable wormhole that has been made traversable through the application of Aperture-brand \"Averaging Fields.\" Essentially, the device tells the universe that the two distinct physical coordinates are, for all intents and purposes, the same location. This allows for the instantaneous transition of matter—and momentum—across vast distances.\n\nA critical aspect of portal physics is the Conservation of Momentum. As the automated testing greeting famously states: \"In layman's terms: speedy thing goes in, speedy thing comes out.\" Because the portal itself has no mass and exerts no gravitational pull, the kinetic energy of the subject remains constant. If you enter a floor-based portal at terminal velocity, you will exit the wall-based portal at that same velocity. The portal does not move you; it simply removes the space that was in your way.\n\nIt is worth noting that portals can only be sustained on surfaces coated with Conversion Gel, which is primarily composed of ground-up moon rocks. Lunar soil is an ideal quantum conductor, providing the stable lattice required for the portal's event horizon to \"stick\" to a three-dimensional plane. Attempting to place a portal on a non-conductive surface, such as wood or untreated metal, results in a \"fizzle\" event—a polite way of saying the universe refused to cooperate with your poor decision-making.",
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
            if (in_array($userName, ['Gordon Freeman', 'Caroline', 'Dana Scully'], true)) {
                $tiersByLevel = $profile->tiers()->whereIn('level', [1, 2, 3])->get()->keyBy('level');
            }

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
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key)) {
            return Carbon::parse($key);
        }

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
