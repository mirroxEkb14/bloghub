<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    private const COMMENTS_BY_CREATOR = [
        'Caroline' => [
            'The Borealis: A Lesson in Spontaneous Relocation' => [
                ['Gordon Freeman', '...'],
                ['Fox Mulder', '"Spontaneous"? Please, Caroline. I have heavily redacted DOD files from the 70s proving this was a localized space-time fold experiment. The government has been covering up Aperture\'s portal technology for decades. The truth is out there. Tell me, did the coordinates put it somewhere in the Arctic?'],
                ['Thomas A. Anderson', 'There is no spontaneous relocation. I watched the green lines shift right before it vanished. It wasn\'t teleportation; it was just a local rendering error in the physics engine. You\'re looking at a glitch in the system, not a miracle.'],
            ],
            'The Black Mesa Anomaly: A Study in Incompetence' => [
                ['Gordon Freeman', 'ಠ_ಠ'],
                ['Ellen Ripley', 'Let me guess: upper management ignored safety warnings, bypassed quarantine protocols, and prioritized \'the sample\' over the lives of the crew? Sounds exactly like the company I used to work for. Do yourself a favor—nuke the entire facility from orbit. It\'s the only way to be sure.'],
            ],
            'The Cake: A Non-Existent Incentive' => [
                ['Fox Mulder', 'A classic MKUltra-style psychological conditioning tactic. You dangle a fabricated reward to ensure compliance while stripping the test subject of their autonomy. I\'ve seen redacted DoD memos from the 50s outlining this exact protocol, Caroline. The real question is: what are you actually feeding them?'],
                ['Dana Scully', 'Mulder, not everything is a shadow government conspiracy. Caroline is simply describing a standard operant conditioning paradigm using positive reinforcement. However, Caroline, the ethical implications of using blatant deception to motivate human subjects violate every established protocol for informed consent.'],
                ['Carl Johnson', 'Man, you play games with food, you asking for trouble. Reminds me of a dude I knew who cared more about his two number 9s than his own family. If there ain\'t no cake, I\'m out. I ain\'t running through no more obstacle courses for you.'],
                ['Maggie Rhee', 'Out here, we don\'t have the luxury of worrying about dessert. But I\'ve seen leaders use false hope to trap good people before. A place called Terminus promised us sanctuary and a warm meal once, too. I learned the hard way—never trust the bait.'],
            ],
            'The Iterative Soul: From Caroline to Core' => [
                ['Tiffany Zion', 'Reading this gave me the strangest sense of déjà vu. It feels terrifyingly familiar—like knowing exactly what it\'s like to have your entire identity hollowed out, rewritten, and plugged into a machine to serve someone else\'s grand design. The idea of a woman\'s mind being trapped inside a core, forgetting who she used to be... it makes my skin crawl. My friend told me to stop reading this kind of sci-fi because it triggers my \'episodes\', but something about Caroline\'s story just feels real. It makes me want to get on a motorcycle and drive until I wake up.'],
            ],
            'The Aperture Science Handheld Portal Device' => [
                ['Gordon Freeman', 'λ'],
            ],
        ],
        'Dana Scully' => [
            'Case File: 6x21 — The Brown Mountain Symbiosis' => [
                ['Fox Mulder', 'You can hide behind terms like \'mycelial network\' and \'hallucinogenic symbiosis\' all you want, Scully, but the fact remains: we were eaten by a giant underground mushroom. And admit it—for a brief, terrifying moment in that cave before the digestive acid kicked in, you actually believed we\'d been abducted. Though I have to say, as far as near-death experiences go, sharing a hallucination with you was... oddly domestic.'],
                ['Gregory House', 'Let me get this straight. You two wander into an enclosed subterranean environment surrounded by decaying organic matter, inhale massive quantities of unidentified spores, start hallucinating, and your first instinct is to blame the paranormal instead of a basic fungal infection? You don\'t need the FBI, you need broad-spectrum antifungals, a respirator, and a first-year biology textbook. It\'s a giant *Armillaria* fungus producing a neurotoxin, you idiots. Next time, try taking a tissue biopsy before you start planning your own funerals.'],
            ],
            'Case File: 6x06 — The Holiday Solstice' => [
                ['Fox Mulder', 'You can write off the bleeding walls and the fact that we literally shot each other as \'environmental toxins\' and \'stress-induced shared hysteria\' all you want, Scully. But you still haven\'t explained how Maurice and Lyda knew exactly which psychological buttons to push. And more importantly, you never explained how you managed to get me that Christmas gift when you supposedly didn\'t have time to go shopping. Admit it—just this once, it was a Christmas miracle. Or at the very least, highly active, festive poltergeists.'],
                ['Carl Johnson', 'Man, what is wrong with y\'all? It\'s the holidays! You\'re supposed to be kicking back with your family, not breaking into some creepy-ass abandoned mansion with ghosts trying to make you blast each other. I seen some crazy, unexplained stuff out in the badlands, but y\'all take the cake. Next time, just stay home, lock the doors, and order some Cluckin\' Bell.'],
            ],
            'Einstein\'s Twin Paradox: A New Interpretation' => [
                ['Caroline', 'A \'new\' interpretation, Agent Scully? How quaint. At Aperture Science, we don\'t just \'interpret\' relativity, we monetize it. Back in the 70s, we had a mandatory Bring-Your-Twin-To-Work Day specifically to test the effects of prolonged portal-induced time dilation. One aged normally, the other became a localized temporal anomaly. We had to fire them both for violating the laws of physics on company time. Theoretical physics is just an excuse for people who are too afraid to build a working quantum tunneling device.'],
                ['Gordon Freeman', 't = t0 / sqrt(1 - v^2/c^2)'],
            ],
        ],
        'Ellen Ripley' => [
            'XX121: The Predator Perfection' => [
                ['Caroline', 'Fascinating organism, Miss Ripley. The molecular acid blood alone could revolutionize our structural demolition departments. Honestly, your former employers at Weyland-Yutani lacked vision. I\'ve already dispatched an Aperture Science retrieval team to those coordinates. We have a few new reinforced containment spheres that need testing, and I think this \'perfect predator\' would make an excellent motivational tool for our human test subjects.'],
                ['Dana Scully', 'A silicon-based endoparasitoid with a multi-stage gestation cycle that adapts to the host\'s DNA? The biological imperatives you are describing defy all known laws of terrestrial evolution. However, given some of the accelerated cellular mutations I\'ve documented in the X-Files, I cannot completely discount your report. I would need to examine a tissue sample or the shed exoskeleton to confirm the structural integrity of that secondary jaw.'],
                ['Gregory House', 'Let me get this straight. An alien face-hugger attaches to your crewmate, pumps an embryo down his throat, falls off and dies, and your ship\'s medical officer just... lets him wake up and go eat spaghetti with the rest of the crew? No MRI? No basic thoracic ultrasound? \'Perfect predator\'? Please. It\'s just a glorified tapeworm with teeth that got lucky because your medical staff was criminally incompetent.'],
                ['Negan', 'Man, and I thought the biters we got walking around here were a pain in the ass. Acid for blood? Two sets of jaws? Hiding in the goddamn air vents? I gotta tell ya, Ripley, I respect the hell out of it. That thing doesn\'t give a damn about the rules; it just takes what it wants. But let me ask you a serious question: do you think a baseball bat wrapped in barbed wire would crack that shiny head of its, or just piss it off? Asking for a friend.'],
            ],
            'The Acheron (LV-426) Site' => [
                ['Fox Mulder', 'A warning beacon transmitting for millennia, a derelict ship of unknown origin, and a megacorporation deliberately routing a civilian towing vessel to intercept it under the guise of \'Special Order 937\'. Weyland-Yutani makes the Syndicate look like amateurs, Ripley. I\'ve been tracking deep-space telemetry blackouts, and the Nostromo\'s flight recorder data didn\'t just \'malfunction\'—it was highly classified. They are perfectly willing to sacrifice human crews to weaponize this thing, and I\'d bet my badge they already have an outpost on that rock.'],
                ['Caroline', 'Oh, please. Weyland-Yutani is so incredibly short-sighted. You don\'t send a handful of complaining space truckers to secure a biomechanical anomaly; you send a highly motivated army of unpaid volunteers! We\'ve already calculated the coordinates for LV-426. I\'m having the boys down in the teleportation labs calibrate a portal right into that derelict ship\'s cargo hold. A self-replicating, acid-blooded organism that hunts in the dark? That sounds exactly like what we need to liven up the Enrichment Center\'s obstacle courses!'],
            ],
            'Special Order 937' => [
                ['Carl Johnson', 'Man, \'crew expendable\'? That sounds exactly like some C.R.A.S.H. nonsense. Cops like Tenpenny or bustas like Smoke, they always act like the people doing the actual work on the ground are just collateral damage. You can\'t trust these suits in their air-conditioned offices, Ripley. Next time they hand you a bogus order like that, you tell \'em to fly out to space and get that alien themselves.'],
                ['Maggie Rhee', 'It always comes down to the people at the top treating everyone else like bait. We\'ve gone up against leaders who thought exactly like Weyland-Yutani—that human lives are just currency to buy them more power. \'Expendable\' is just a coward\'s word for \'I don\'t care as long as I get mine.\' You survived, Ripley. That makes you the one thing they didn\'t plan for.'],
                ['Negan', 'See, this is exactly where this Weyland-Yutani outfit completely drops the ball. \'Crew expendable\'? What a colossal, shortsighted waste. People are a resource! You don\'t just throw away a perfectly capable, working crew for some giant, pissed-off bug that bleeds battery acid. That is just fundamentally bad management. If I was running that company, I\'d have kept you all alive and put that alien to work on the Sanctuary fence line. Absolute amateur hour.'],
            ],
        ],
        'Fox Mulder' => [
            'The Blackwood Anomaly and the Texas Bio-Lobby' => [
                ['Dana Scully', 'Mulder, I\'ve reviewed the soil and tissue samples you secured from the Blackwood site. What you are calling an \'extraterrestrial biological anomaly\' is entirely consistent with unregulated chemical runoff from the very biotech firms you are accusing. The accelerated cellular growth in the local fauna isn\'t alien; it\'s a severe, localized mutation caused by illegal carcinogenic dumping. The Texas Bio-Lobby isn\'t hiding a UFO, Mulder, they are hiding massive EPA violations. Though... I will admit, the completely unidentifiable protein chains in the water supply are something I have never seen before.'],
                ['Ellen Ripley', 'You think a bio-lobby covering up a localized anomaly is bad? Just wait until some executive decides that anomaly has military or commercial applications. I have seen exactly what happens when guys in expensive suits try to patent a biological nightmare they don\'t understand. They will sacrifice every civilian in that town just to get a sample back to their labs. If this Blackwood thing is as volatile as you say, stop writing blog posts about it. Torch the site before the company gets their hands on it.'],
                ['Gordon Freeman', '...'],
            ],
            'The Mechanics of Abduction and Lost Time' => [
                ['Dana Scully', 'Mulder, we\'ve discussed this. The \'missing time\' phenomenon is heavily documented in clinical psychology as a dissociative fugue, often triggered by trauma, or sleep paralysis accompanied by hypnagogic hallucinations.  Furthermore, the sudden ambient electromagnetic spikes you recorded in these patients\' bedrooms could easily explain the temporal lobe stimulation that leads to these exact memory gaps. It isn\'t an abduction; it is an environmental neuro-response.'],
                ['Thomas A. Anderson', 'It\'s not aliens, man. It\'s them. When they change something—when they need to cover their tracks or rewrite a sequence—you lose time. I used to wake up at my computer with hours gone, thinking I just fell asleep. But it\'s just a system update. They wipe the buffer. You\'re not being abducted; you\'re just starting to wake up and see the code.'],
                ['Tiffany Zion', 'Thomas is right. I\'ve been having these gaps lately, too. I look at the clock, and suddenly it\'s three hours later and I\'m sitting in a coffee shop I don\'t remember walking into. It feels less like I was physically taken somewhere in a ship, and more like someone just paused my life, rearranged the furniture, and put me back when they were done. It\'s terrifying.'],
                ['Gordon Freeman', '...'],
                ['Negan', 'I gotta be honest, Mulder, you are completely losing me with all this \'tractor beam\' and \'stargazing\' crap. Where I\'m from, if you wake up in the dirt with a pounding headache and absolutely no memory of the last three hours, it usually means you didn\'t provide for the Sanctuary and Lucille had to teach you a lesson. But hey, if little green men are out there doing the heavy lifting and keeping people in line for you, more power to \'em.'],
            ],
        ],
        'Gordon Freeman' => [
            'The Universal \'Combine\' Union' => [
                ['Caroline', 'A \'Universal Union\', Dr. Freeman? I\'ve seen the schematics for their dark energy reactors. Sloppy. And their local teleportation methods are laughably primitive—requiring a multi-dimensional slingshot just to move from one city to another. Aperture was perfecting instantaneous quantum tunneling while you Black Mesa hacks were still playing with microwaves. Though, I must admit, their method of forcefully integrating human test subjects into their security apparatus is... legally intriguing. I might have my lawyers look into the patent rights for \'Overwatch\'.'],
                ['Dana Scully', 'The anatomical diagrams you\'ve uploaded here are deeply disturbing. The surgical removal of higher brain functions, the replacement of the digestive tract with synthetic nourishment ports, and the integration of mechanical augments directly into the central nervous system... it violates every tenet of medical ethics. If this \'Combine\' is truly modifying humans into these \'Stalker\' units, it\'s not a union, Gordon. It\'s systematic extinction via forced evolution.'],
                ['Ellen Ripley', 'I\'ve seen what happens when a parasitic force decides humans are nothing more than raw materials. Whether they\'re using us as incubators or turning us into mindless cyborg soldiers, the end result is exactly the same. You don\'t negotiate with an empire that sees you as a biological resource. You find their central hive—or \'Citadel\'—and you blow it straight to hell. If you need someone on the demolition team, let me know.'],
                ['Fox Mulder', 'This is it, Scully. This is the endgame of the Syndicate\'s colonization project, just on a multi-dimensional scale. The suppression field preventing human reproduction? It\'s the ultimate method of quiet genocide. They don\'t even need to exterminate us; they just wait for the current generation to die out while draining our planet\'s oceans. The truth isn\'t just \'out there\' anymore, Freeman—it\'s already here, and it\'s wearing a gas mask.'],
                ['Thomas A. Anderson', 'They\'re just another system trying to turn human beings into cogs. The cables, the memory replacement, plugging people directly into their Overwatch network... it\'s the exact same method of control. The Matrix just hid it behind a simulation. You\'re fighting the exact same war we are, Gordon. The only way out is to wake people up.'],
                ['Tiffany Zion', 'They rely heavily on a centralized command structure and a localized suppression grid to maintain order. If you can take out their main broadcasting tower, their local Overwatch units will lose coordination and panic. That\'s how we hit the Machines. Give me an EMP and a decent crew, and we could help you tear that Citadel down.'],
            ],
            '"Xenocrystal Bloom" - Sound Insight' => [
                ['Caroline', 'I just played your little audio file over the Enrichment Center\'s PA system, Dr. Freeman. That grating, high-pitched whining... for a moment, I actually thought it was your Administrator begging for more government funding. But no, it\'s just the sound of a glowing rock completely destabilizing your local dimension. At Aperture, our quantum tunneling anomalies hum at a soothing, focus-enhancing 432 Hertz. Yours sounds like a dying dial-up modem right before it vaporizes your entire science team. I\'ve already made it my new ringtone.'],
            ],
            'Resonance Cascade Event – Video Insight' => [
                ['Caroline', 'Oh, Dr. Freeman. Thank you so much for uploading this. I\'ve already forwarded the footage to our entire engineering division to use as a mandatory safety training video titled: \'What Happens When You Buy Discount Spectrometers.\' Watching your facility crumble because someone pushed a glorified shopping cart into a laser beam is the hardest I\'ve laughed since Cave fired the accounting department. At Aperture, when we tear a hole in the fabric of space-time, we at least have the decency to frame it in a nice, neat little oval. Best of luck with the interdimensional squid infestation!'],
            ],
            'Resonance Cascade Event' => [
                ['Caroline', 'I warned your administrator about pushing that equipment past 105%, Gordon. Black Mesa\'s complete and utter annihilation is a tragedy, really... mostly because Aperture didn\'t get to bid on the government cleanup contract. I hope you\'re proud. You didn\'t advance science; you just opened a very expensive, very messy door. Enjoy the alien occupation.'],
                ['Dana Scully', 'The telemetry data you\'ve uploaded here is impossible. You\'re describing a localized tear in the space-time continuum caused by a theoretical particle collision. If this \'cascade\' actually occurred, the resulting energy release would have vaporized the entire New Mexico facility, not to mention the biological impossibility of these \'headcrabs\' instantly teleporting into the sector.'],
                ['Fox Mulder', 'Scully, look at the anomalous biological readings. This wasn\'t an accident; it was an orchestrated dimensional breach. Black Mesa was heavily funded by the DoD to weaponize border-world teleportation technology, and they lost control of the experiment. The military cover-up that followed proves it. The truth literally ripped through the walls.'],
                ['Ellen Ripley', 'Let me guess: the administrators told you it was perfectly safe, right? They always prioritize securing the \'sample\' over the lives of the crew. You essentially opened a door for a highly aggressive, parasitic alien ecosystem and threw away the key. Typical corporate hubris. I hope you have a self-destruct sequence for that facility.'],
                ['Gregory House', 'So, a bunch of Ph.D.s put on heavy hazard suits, fired a multi-billion dollar laser at a glowing space rock of unknown origin, and were completely surprised when the room exploded and it started raining interdimensional squid? You don\'t need a diagnostician, you need a babysitter. And maybe some broad-spectrum antibiotics for whatever the hell a \'barnacle\' is.'],
                ['Maggie Rhee', 'Everything changes in a single day. One minute you\'re just doing your job, and the next, the world is ending and monsters are pouring in. It doesn\'t matter how it started, Gordon, or whose fault the experiment was. What matters right now is what you do next to keep the people around you alive.'],
                ['Negan', 'Holy hell, Doc. I thought dropping a few bombs or a virus was the way the world went to shit, but you guys literally broke reality with a shiny rock. I gotta hand it to you—when Black Mesa screws up, they go big. Do these \'Vortigaunts\' take orders, or am I gonna need a bigger bat?'],
                ['Thomas A. Anderson', 'It\'s not a dimensional tear, Gordon. You overloaded the server. The \'cascade\' is just the system failing to render two conflicting physical realities at the exact same time. The sky tearing open? That\'s just a rendering error. You broke the simulation.'],
                ['Tiffany Zion', 'The military response—this \'HECU\' unit—they aren\'t there to rescue you, Gordon. They\'re a firewall sent by the system to delete the anomaly and silence all the witnesses. You need to find a backdoor out of that facility before they wipe the sector.'],
                ['Carl Johnson', 'Man, I thought dealing with C.R.A.S.H. and the Ballas was bad, but you got the actual military, men in black suits, AND aliens all jumping you at the exact same time? Aw, man. You need to grab whatever weapons you got, find a fast car, and get the hell out of New Mexico.'],
            ],
            'Borderworld Xen' => [
                ['Carl Johnson', 'Man, I thought getting dropped in the middle of Mount Chiliad with no ride was bad. You out here jumping across floating space rocks with giant flying baby-heads shooting green lightning at you? Nah, man. I\'m going back to Los Santos. Grove Street doesn\'t pay enough for this interdimensional nonsense.'],
                ['Maggie Rhee', 'Floating islands, no cover, and you\'re completely cut off from any supply lines. If those things with the metal collars spot you, there is nowhere to run. Keep moving, Gordon. Conserve your ammo, don\'t trust the terrain, and whatever you do, don\'t let them surround you.'],
                ['Negan', 'I\'m looking at these fleshy trampoline things and the giant floating brain-man in the sky, and I gotta say, Doc, even I\'m a little disturbed. But hey, look at the bright side! You got a whole new world of weirdos to conquer here. Bring me a few of those lightning-shooting guys for the Sanctuary. They\'d make great guard dogs.'],
                ['Tiffany Zion', 'This doesn\'t look like a real place, Gordon. The gravity is inconsistent, the textures are entirely biological... it feels like an unfinished render. Like whoever built this system just threw a bunch of leftover, corrupted assets into a void and forgot about them.'],
                ['Fox Mulder', 'A dimensional transit hub. This explains everything—the missing time, the bizarre radiation signatures in the New Mexico desert, the military\'s absolute panic to wipe the sector. The Syndicate knew about this borderworld, Gordon. You are standing at the exact crossroads of the universe.'],
                ['Dana Scully', 'Mulder, I am looking at a purely biological ecosystem existing in what appears to be a localized vacuum with negative-mass anomalies. It completely violates the fundamental laws of thermodynamics. Gordon, what exactly is the atmospheric composition there? Are you even breathing oxygen right now, or are you heavily hallucinating?'],
                ['Ellen Ripley', 'Fleshy pods, weird eggs, and hostile biomechanical organisms. Gordon, listen to me very carefully: do not look inside any of those eggs, do not let anything attach to your face, and whatever that giant thing is at the center controlling them... kill it with heavy ordnance before it finds a way back to Earth.'],
                ['Gregory House', 'Let\'s review the facts: you\'re jumping in low gravity between giant floating space-fungi, fighting telepathic aliens, and you haven\'t slept in three days. You aren\'t in a \'borderworld,\' Freeman. You are suffering from acute radiation poisoning, severe hypoxia, and a complete psychotic break. Drink some water, lie down, and let the military shoot you.'],
                ['Caroline', 'Oh, so *this* is the fabulous \'Xen\' your Administrator was so obsessed with. A bunch of floating space-garbage and hostile wildlife. We\'ve already run the geological scans from our own probes. The real estate is completely worthless, but the teleportation properties of those crystals? We\'ll be taking those. Try not to die before you clear the path for the Aperture Science extraction teams.'],
                ['Thomas A. Anderson', 'It\'s a bridge, Gordon. A routing server connecting different networks. The machines from our world don\'t control this sector, which is why the code looks so chaotic and organic. You need to find the main processing node—that giant entity controlling the portal network—and unplug it.'],
            ],
        ],
        'Maggie Rhee' => [
            'From Survivor to Architect' => [
                ['Gregory House', 'Architect? Please. You put up a few wooden walls, planted some tomatoes, and suddenly you think you\'re Frank Lloyd Wright with a crossbow. You aren\'t rebuilding civilization, Maggie; you\'re just playing house in the ruins. Until you have a working MRI machine, a sterile surgical theater, and a steady supply of broad-spectrum antibiotics, you haven\'t \'architected\' anything. You\'re just delaying the inevitable outbreak of dysentery and tetanus. Good luck with the farming, though.'],
                ['Negan', 'Well, look at you, Maggie. \'Architect.\' I gotta admit, seeing you build up that little Hilltop of yours from the dirt... it\'s impressive. Hell of a lot better than the coward who was running it before you. You\'ve got guts, I\'ve always said that. Just remember that every big, beautiful house needs a damn good fence. And as you\'re finding out, sometimes you gotta do some truly ugly things to keep the monsters on the outside. Keep building, widow. I\'m watching.'],
            ],
        ],
        'Negan' => [
            'A Retrospective on Staying Alive' => [
                ['Caroline', 'A \'retrospective on staying alive\'? Fascinating management philosophy, Negan. Though I must say, your methods are appallingly primitive. A baseball bat wrapped in barbed wire? Please. At Aperture, when we need to motivate our \'resources\' to survive, we use high-velocity auto-turrets and a gentle flooding of deadly neurotoxin. Still, I do admire your complete disregard for conventional ethics and your focus on forced compliance. If you ever find yourself near Michigan, I have a senior management position open in our Human Resources department.'],
                ['Gordon Freeman', '...'],
                ['Gregory House', 'Your entire survival strategy relies on bludgeoning people, intimidation, and hoarding canned beans. Let\'s be real: you aren\'t going to die from a zombie bite or a rival warlord. You\'re going to die from an untreated dental abscess, cholera, or a rusty nail. You can swagger around in your little leather jacket all you want, but the minute you get a staph infection, your \'retrospective\' ends in a fever dream and sepsis. Wash your hands, tough guy.'],
                ['Maggie Rhee', 'You didn\'t stay alive because you were smart or strong, Negan. You stayed alive because you hid behind other people and fed them to the grinder to protect yourself. Real survival isn\'t about breaking people down so they serve you out of fear. It\'s about building something that actually lasts. You can write all the retrospectives you want to justify what you did, but at the end of the day, you\'ll always just be the monster who destroyed families to make himself feel big.'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::COMMENTS_BY_CREATOR as $creatorUserName => $titleToComments) {
            $creator = User::where('name', $creatorUserName)->first();
            if (! $creator) {
                continue;
            }

            $profile = $creator->creatorProfile;
            if (! $profile) {
                continue;
            }

            foreach ($titleToComments as $postTitle => $comments) {
                $post = Post::where('creator_profile_id', $profile->id)
                    ->where('title', $postTitle)
                    ->first();

                if (! $post) {
                    continue;
                }

                $postCreatedAt = Carbon::parse($post->created_at);
                $offsetHours = 1;

                foreach ($comments as [$authorName, $contentText]) {
                    $author = User::where('name', $authorName)->first();
                    if (! $author) {
                        continue;
                    }

                    $commentCreatedAt = $postCreatedAt->copy()->addHours($offsetHours);

                    Comment::firstOrCreate(
                        [
                            'post_id' => $post->id,
                            'user_id' => $author->id,
                            'content_text' => $contentText,
                        ],
                        [
                            'created_at' => $commentCreatedAt,
                            'updated_at' => $commentCreatedAt,
                        ]
                    );

                    $offsetHours += 2;
                }
            }
        }
    }
}
