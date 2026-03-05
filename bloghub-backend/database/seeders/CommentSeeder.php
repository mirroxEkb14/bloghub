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
    private const AUTHOR_NAME_MAP = [
        'Neo' => 'Thomas A. Anderson',
        'CJ (Carl Johnson)' => 'Carl Johnson',
        'CJ' => 'Carl Johnson',
    ];

    private const COMMENTS_BY_CREATOR = [
        'Caroline' => [
            'The Borealis: A Lesson in Spontaneous Relocation' => [
                ['Gordon Freeman', '...'],
                ['Fox Mulder', '"Spontaneous"? Please, Caroline. I have heavily redacted DOD files from the 70s proving this was a localized space-time fold experiment. The government has been covering up Aperture\'s portal technology for decades. The truth is out there. Tell me, did the coordinates put it somewhere in the Arctic?'],
                ['Neo', 'There is no spontaneous relocation. I watched the green lines shift right before it vanished. It wasn\'t teleportation; it was just a local rendering error in the physics engine. You\'re looking at a glitch in the system, not a miracle.'],
            ],
            'The Black Mesa Anomaly: A Study in Incompetence' => [
                ['Gordon Freeman', 'ಠ_ಠ'],
                ['Ellen Ripley', 'Let me guess: upper management ignored safety warnings, bypassed quarantine protocols, and prioritized \'the sample\' over the lives of the crew? Sounds exactly like the company I used to work for. Do yourself a favor—nuke the entire facility from orbit. It\'s the only way to be sure.'],
            ],
            'The Cake: A Non-Existent Incentive' => [
                ['Fox Mulder', 'A classic MKUltra-style psychological conditioning tactic. You dangle a fabricated reward to ensure compliance while stripping the test subject of their autonomy. I\'ve seen redacted DoD memos from the 50s outlining this exact protocol, Caroline. The real question is: what are you actually feeding them?'],
                ['Dana Scully', 'Mulder, not everything is a shadow government conspiracy. Caroline is simply describing a standard operant conditioning paradigm using positive reinforcement. However, Caroline, the ethical implications of using blatant deception to motivate human subjects violate every established protocol for informed consent.'],
                ['CJ (Carl Johnson)', 'Man, you play games with food, you asking for trouble. Reminds me of a dude I knew who cared more about his two number 9s than his own family. If there ain\'t no cake, I\'m out. I ain\'t running through no more obstacle courses for you.'],
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
                ['CJ (Carl Johnson)', 'Man, what is wrong with y\'all? It\'s the holidays! You\'re supposed to be kicking back with your family, not breaking into some creepy-ass abandoned mansion with ghosts trying to make you blast each other. I seen some crazy, unexplained stuff out in the badlands, but y\'all take the cake. Next time, just stay home, lock the doors, and order some Cluckin\' Bell.'],
            ],
            'Einstein\'s Twin Paradox: A New Interpretation' => [
                ['Caroline', 'A \'new\' interpretation, Agent Scully? How quaint. At Aperture Science, we don\'t just \'interpret\' relativity, we monetize it. Back in the 70s, we had a mandatory Bring-Your-Twin-To-Work Day specifically to test the effects of prolonged portal-induced time dilation. One aged normally, the other became a localized temporal anomaly. We had to fire them both for violating the laws of physics on company time. Theoretical physics is just an excuse for people who are too afraid to build a working quantum tunneling device.'],
                ['Gordon Freeman', 't = t0 / sqrt(1 - v^2/c^2)'],
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

                foreach ($comments as [$authorSpecName, $contentText]) {
                    $authorUserName = self::AUTHOR_NAME_MAP[$authorSpecName] ?? $authorSpecName;
                    $author = User::where('name', $authorUserName)->first();
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
