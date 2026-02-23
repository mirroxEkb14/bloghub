<?php

namespace Database\Seeders;

use App\Enums\MediaType;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\User;
use App\Support\PostResourceSupport;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    private const POSTS_BY_USER = [
        'Super Admin' => [
            [
                'slug' => 'clean-architecture-in-practice',
                'title' => 'Clean architecture in practice',
                'tier_level' => null,
                'content' => <<<'MD'
                    When we talk about clean architecture, we are not talking about a single pattern or framework. We are talking about a set of principles that help you keep the core of your application independent of delivery mechanisms, UI, and infrastructure. In this post I want to share how I apply these ideas in real backend projects: how I structure folders, where I draw boundaries, and how I keep the domain logic free from framework noise.

                    The first rule I follow is that the domain layer never depends on anything outside itself. No Eloquent models in domain services, no HTTP requests, no configuration. The domain only knows about entities, value objects, and the interfaces of ports it needs (for example, a repository interface). All concrete implementations live in the outer layers. That way, you can test the core logic with simple in-memory doubles and change the database or the API shape without touching the heart of the application.

                    The second rule is that use cases (or application services) orchestrate the flow. They receive input, call the domain, call repositories or other infrastructure through interfaces, and return a result. They do not contain business rules; they only coordinate. Business rules live in the domain. This keeps use cases thin and easy to read: you see the scenario at a glance.

                    The third rule is that infrastructure adapters implement the interfaces defined by the domain or the application layer. The HTTP controller is an adapter: it translates the request into a call to a use case and translates the result into a response. The Eloquent repository is an adapter: it translates between domain entities and database rows. By keeping these adapters at the edge, you can swap them without affecting the rest of the system.

                    In Laravel, this often means resisting the urge to put logic in controllers or in model methods that go beyond persistence. Controllers become thin: validate input, call one use case, return a response. Models become simple data mappers or are hidden behind repositories. The real logic lives in dedicated domain and application classes. It takes discipline at first, but it pays off when you need to change a requirement or add a new way to trigger the same use case (e.g. a queue job or a CLI command).

                    I also recommend keeping the domain language in the code. If the business talks about “subscriptions” and “tiers”, your domain should use those terms. Avoid leaking technical terms like “table” or “row” into the domain. This makes the code readable by both developers and stakeholders and keeps the design aligned with the problem space.

                    Finally, testing becomes straightforward. You unit-test the domain with plain PHP objects. You integration-test use cases with fake repositories. You only need a few end-to-end tests to confirm that the adapters are wired correctly. This post is a high-level map; in future posts I will go into folder structure, dependency injection, and concrete examples from a Laravel codebase.
                    MD,
                'media_type' => null,
                'media_url' => null,
            ],
            [
                'slug' => 'repository-pattern-with-eloquent',
                'title' => 'Repository pattern with Eloquent',
                'tier_level' => 1,
                'content' => <<<'MD'
                    The repository pattern is one of the most useful tools for keeping your domain independent of the database. In Laravel, we usually work with Eloquent models directly in controllers or services, which is fine for small apps. As the application grows, you often want to hide the persistence details behind an interface so that the rest of the code does not care whether data comes from MySQL, Redis, or an external API. That is what a repository does: it provides a collection-like interface for your entities and hides the underlying storage.

                    The key idea is to define the repository as an interface in the domain or application layer. For example, a PostRepository interface might have methods like findById, findByCreatorProfile, and save. The contract is expressed in terms of domain objects (e.g. Post or a value object), not Eloquent models. The implementation in the infrastructure layer then uses Eloquent to fulfill that contract: it maps between your domain Post and the Eloquent Post model, or it might use the Eloquent model as the entity if you decide to keep a single representation. Both approaches are valid; the important part is that the rest of the application depends on the interface, not on Eloquent.

                    When you use an interface, dependency injection and testing become easy. In production, you bind the interface to the Eloquent implementation. In tests, you bind it to an in-memory implementation or a mock. Your use cases and domain services never import the Eloquent model; they only depend on the interface. This keeps the domain free from framework concerns and makes it possible to test without a database.

                    I recommend keeping repository methods focused. Avoid generic “get all” methods unless you really need them; prefer methods that express a use case, like “findPublishedPostsByCreator” or “findSubscriptionByUserAndTier”. This makes the intent clear and prevents the repository from turning into a dumping ground for random queries. If you need a complex query, put it in the repository implementation, not in the use case. The use case should only call high-level methods that describe what it needs.

                    Laravel’s service container makes it simple to bind the interface to the implementation. You can do it in a service provider or use a convention-based approach. Just ensure that only the infrastructure layer knows about the concrete class; the rest of the app should type-hint the interface. With this in place, you get a clean separation between “what the application needs” and “how we store and retrieve it”, which is the goal of the repository pattern.
                    MD,
                'media_type' => MediaType::Image,
                'media_url' => null,
            ],
        ],
        'User' => [
            [
                'slug' => 'why-i-write-in-the-morning',
                'title' => 'Why I write in the morning',
                'tier_level' => null,
                'content' => <<<'MD'
                    For a long time I tried to fit writing into the gaps of the day: after meetings, late at night, or when I had “nothing else” to do. The result was that I rarely wrote at all. The gap never felt big enough, and by the time I sat down, my mind was already full of the day’s noise. Then I shifted writing to the first hour after I wake up, before opening email or messages. The change was immediate. In this post I want to share why morning writing works for me and how you can try it without overhauling your whole schedule.

                    The main reason is that in the morning, your mind is still close to the state of sleep. You have not yet been bombarded with other people’s priorities, notifications, and the hundred small decisions of the day. That makes it easier to access your own thoughts. You are not yet in reactive mode. For creative work and reflection, that window is precious. I use it for long-form writing, journaling, or planning the day in a way that aligns with my goals instead of other people’s requests.

                    A second reason is that writing in the morning guarantees that it happens. If you leave it for later, “later” often gets eaten by urgent tasks. By making writing the first substantial task of the day, you protect it. It does not depend on how the rest of the day goes. Even on busy days, you have already done the thing that matters to you. That builds consistency and reduces the guilt of “I never have time to write.”

                    A third reason is that writing clarifies your mind for the rest of the day. When you put your thoughts on paper (or on the screen), you sort them. You see what you actually think, what is vague, and what you need to decide. That clarity carries into meetings, emails, and other work. You are less likely to be pulled in random directions because you have already anchored yourself in your own words.

                    I am not saying you must wake up at 5 a.m. The point is to choose a time that is reliably yours and to put writing there before the world gets a say. For some people that might be early morning; for others it might be the first block after they start work, as long as it is before they open inbox or chat. Experiment: try one week of writing first thing and see how it feels. You might find that the habit sticks not because you have more discipline, but because you gave it the only slot that was not already claimed.
                    MD,
                'media_type' => null,
                'media_url' => null,
            ],
            [
                'slug' => 'habits-and-systems-over-goals',
                'title' => 'Habits and systems over goals',
                'tier_level' => 2,
                'content' => <<<'MD'
                    Goals are useful for direction. They tell you where you want to go. But they have a downside: once you hit a goal, you often stop. And if you miss, you can feel like a failure even when you made real progress. That is why I have shifted my focus from goals to habits and systems. In this post I explain what that means in practice and how it has changed the way I work and create.

                    A habit is something you do regularly, regardless of the outcome. You do not “run a marathon” as a habit; you “run three times a week.” You do not “write a book” as a habit; you “write for thirty minutes every morning.” The habit is under your control; the outcome (marathon, book) is not fully under your control. So you design for the habit, and the outcomes tend to follow over time. This reduces the pressure of a single big target and spreads progress across many small steps.

                    A system is the set of habits and routines that support a direction. For example, if your direction is “get better at writing,” your system might include: morning writing, reading for an hour, saving ideas in a note app, and reviewing those notes once a week. You do not set a goal like “publish 12 posts this year.” You set a system: “I write every morning and publish when something is ready.” The system runs regardless of the number of posts; the number becomes a side effect of the system.

                    The benefit of this approach is that you can feel successful every day. You ran. You wrote. You showed up. You do not have to wait until the end of the year to know if you “succeeded.” You succeed a little every time you complete the habit. That builds motivation and makes it easier to keep going when a particular project is slow or stuck.

                    I still have directions and intentions. I do not abandon goals entirely. But I treat them as compass points, not as contracts. The real commitment is to the system. If the system is in place, the results will come. If the system is missing, even a clear goal will not save you. So my advice is: choose one or two habits that move you toward what you want, put them in a fixed time slot, and protect that slot. Let the goals emerge from the system instead of the other way around.
                    MD,
                'media_type' => null,
                'media_url' => null,
            ],
        ],
        'Admin' => [
            [
                'slug' => 'zoos-and-conservation-today',
                'title' => 'Zoos and conservation today',
                'tier_level' => null,
                'content' => <<<'MD'
                    Modern zoos and aquariums are no longer just places to look at animals. They play a growing role in conservation, research, and education. In this post I want to share how I see this change from the perspective of someone who visits and documents these spaces: what has improved, what is still debated, and why I think the conversation about “zoos good or bad” is too simple.

                    The first thing that stands out is breeding programmes. Many species are now managed in coordinated programmes across institutions. The goal is to maintain genetic diversity and, in some cases, to prepare animals for reintroduction into the wild. So when you see a certain species in a zoo, it might be part of a network that stretches across countries. That does not make every enclosure perfect, but it does mean that the role of zoos has shifted from pure display to active participation in species survival.

                    The second thing is education. Good zoos invest in interpretation: signs, talks, and experiences that explain not only what the animal is, but why it matters and what threats it faces in the wild. For many people, a zoo visit is the only contact they will ever have with these animals. If that contact is thoughtful, it can spark curiosity and support for conservation. If it is shallow, it can reinforce the idea that animals are there for our entertainment. So the quality of education matters a lot.

                    The third thing is the welfare debate. Even with the best intentions, captivity is not the wild. Space, social structure, and natural behaviour are hard to fully replicate. Some species adapt; others do not. The debate is not “zoos yes or no” in the abstract, but “under what conditions is it acceptable to keep which species, and for what purpose?” That requires case-by-case thinking and a willingness to change when new evidence appears.

                    I document zoos and conservation parks because I find the mix of care, science, and compromise fascinating. I do not pretend there are easy answers. I try to show what I see: the good efforts, the limitations, and the questions we should keep asking. If you are curious about wildlife and human-made environments, I hope this post gives you a starting point for your own reflection.
                    MD,
                'media_type' => MediaType::Video,
                'media_url' => null,
            ],
            [
                'slug' => 'urban-wildlife-coexistence',
                'title' => 'Urban wildlife: coexistence in practice',
                'tier_level' => 1,
                'content' => <<<'MD'
                    Cities are not only for humans. Birds, mammals, insects, and plants share the same space, often in surprising ways. In this post I share what I have learned from observing and reading about urban wildlife: how animals adapt, what we can do to support them, and why coexistence is both a practical and an ethical choice.

                    The first thing to notice is how many species already live in cities. From peregrines on skyscrapers to hedgehogs in gardens, urban areas can host a lot of life if we leave some room for it. The problem is not that cities are “unnatural” by definition; it is that we often design them in a way that excludes everything except us. When we add green corridors, nesting sites, and water sources, many species respond quickly. So urban wildlife is partly a design question: what do we want our cities to be?

                    The second thing is conflict. Sometimes wildlife and humans clash: birds hitting windows, foxes in bins, or rodents in buildings. The answer is rarely “remove the animals.” It is usually a combination of better design (e.g. bird-safe glass), changing our behaviour (e.g. securing waste), and tolerance. Coexistence does not mean no limits; it means finding a balance where both humans and other species can live without constant harm. That balance is different in each place and for each species.

                    The third thing is the bigger picture. What happens in cities is connected to what happens in the countryside. Habitat loss, pollution, and climate change affect urban wildlife too. So supporting urban biodiversity is not a substitute for protecting wild places; it is one part of a larger effort. It also brings nature closer to people who might otherwise never think about it. For many, a city park or a garden bird is the first step toward caring about conservation elsewhere.

                    I document and share these observations because I believe that paying attention to urban wildlife changes how we see the places we live in. We start to see the city as a shared space. If you have a balcony, a garden, or even a windowsill, there is usually something you can do: water, shelter, or simply not poisoning or removing every “pest.” Small actions add up. This post is an invitation to look around and ask: who else lives here, and what do they need?
                    MD,
                'media_type' => MediaType::Image,
                'media_url' => null,
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::POSTS_BY_USER as $userName => $postsData) {
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

            $tiersByLevel = $profile->tiers()->orderBy('level')->get()->keyBy('level');

            foreach ($postsData as $data) {
                $content = $data['content'];

                $requiredTierId = null;
                if (isset($data['tier_level']) && $data['tier_level'] !== null) {
                    $tier = $tiersByLevel->get($data['tier_level']);
                    $requiredTierId = $tier?->id;
                }

                Post::firstOrCreate(
                    [
                        'creator_profile_id' => $profile->id,
                        'slug' => $data['slug'],
                    ],
                    [
                        'title' => mb_substr($data['title'], 0, PostResourceSupport::TITLE_MAX_LENGTH),
                        'content_text' => $content,
                        'required_tier_id' => $requiredTierId,
                        'media_type' => $data['media_type'],
                        'media_url' => $data['media_url'],
                    ]
                );
            }
        }
    }
}
