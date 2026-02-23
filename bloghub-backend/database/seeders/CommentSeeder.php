<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\User;
use App\Support\CommentResourceSupport;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    private const COMMENTS_BY_USER = [
        'User' => [
            [
                'post_creator_name' => 'Super Admin',
                'post_slug' => 'clean-architecture-in-practice',
                'content_text' => 'This resonates a lot. Keeping the domain independent of Eloquent and HTTP has made our codebase much easier to test and change.',
            ],
            [
                'post_creator_name' => 'Super Admin',
                'post_slug' => 'repository-pattern-with-eloquent',
                'content_text' => 'We use the same approach: repository interface in the app layer, Eloquent implementation in infrastructure. Dependency injection makes swapping implementations trivial.',
            ],
            [
                'post_creator_name' => 'User',
                'post_slug' => 'why-i-write-in-the-morning',
                'content_text' => 'Morning writing has been a game-changer for me too. The first hour is now sacred.',
            ],
        ],
        'Admin' => [
            [
                'post_creator_name' => 'User',
                'post_slug' => 'habits-and-systems-over-goals',
                'content_text' => 'Systems over goals is a mindset I try to apply everywhere. Thanks for articulating it so clearly.',
            ],
            [
                'post_creator_name' => 'Admin',
                'post_slug' => 'zoos-and-conservation-today',
                'content_text' => 'The shift from display to conservation and education is real. Good to see it documented.',
            ],
        ],
        'Super Admin' => [
            [
                'post_creator_name' => 'User',
                'post_slug' => 'why-i-write-in-the-morning',
                'content_text' => 'Agree on protecting the first slot. I block it in the calendar so nothing else can take it.',
            ],
            [
                'post_creator_name' => 'Admin',
                'post_slug' => 'urban-wildlife-coexistence',
                'content_text' => 'Urban wildlife is underrated. Small design choices—green corridors, nesting sites—really do add up.',
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::COMMENTS_BY_USER as $userName => $commentsData) {
            $user = User::where('name', $userName)->first();
            if (! $user) {
                $this->command->warn("User \"{$userName}\" not found, skipping comments.");

                continue;
            }

            foreach ($commentsData as $data) {
                $creator = CreatorProfile::whereHas('user', fn ($q) => $q->where('name', $data['post_creator_name']))->first();
                if (! $creator) {
                    $this->command->warn("Creator profile for \"{$data['post_creator_name']}\" not found, skipping comment.");

                    continue;
                }

                $post = Post::where('creator_profile_id', $creator->id)->where('slug', $data['post_slug'])->first();
                if (! $post) {
                    $this->command->warn("Post \"{$data['post_slug']}\" not found, skipping comment.");

                    continue;
                }

                $content = mb_substr($data['content_text'], 0, CommentResourceSupport::CONTENT_TEXT_MAX_LENGTH);

                Comment::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'post_id' => $post->id,
                        'content_text' => $content,
                    ],
                    []
                );
            }
        }
    }
}
