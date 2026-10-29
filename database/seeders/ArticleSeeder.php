<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');
        $tags = Tag::pluck('id', 'name');
        $readers = User::where('role', 'reader')->get();

        // [writer email, category, title, content, status, days ago, tags, comments]
        $articles = [
            ['writer@technova.com', 'Engineering & Development Updates', 'Moving Our Background Jobs to Redis Queues',
                'This month the backend team moved every slow task, such as emails, report generation and image processing, from the request cycle to Redis-backed queues. Pages that used to wait several seconds now respond immediately, and failed jobs are retried automatically and logged for review.',
                'published', 2, ['Redis', 'Performance'], ['Great change, the admin reports are much faster now.', 'Does this also cover the weekly newsletter emails?']],
            ['writer@technova.com', 'Engineering & Development Updates', 'How We Stopped Cache Stampedes on the Dashboard',
                'When the dashboard cache expired, dozens of requests used to rebuild the same statistics at once and slow down the database. We now take a short atomic lock: the first request rebuilds the cache while the others wait a moment and read the fresh result.',
                'published', 5, ['Redis', 'Performance', 'Laravel'], ['Clear explanation, thanks for sharing the details.']],
            ['writer@technova.com', 'Engineering & Development Updates', 'Version 2 of the Newsroom API Is Live',
                'The mobile team asked for reading time, tags and comment counts on every article. Instead of changing the existing endpoints, we released version 2 of the API. Web clients keep using version 1 without any changes, and new clients can move to version 2 when they are ready.',
                'published', 8, ['Laravel', 'Announcements'], ['The reading time is a nice touch in the app.']],
            ['admin@technova.com', 'News from the Top', 'Security Training Is Now Required for All Teams',
                'Starting next month, every employee will complete a short security training covering phishing, password managers and safe handling of customer data. The sessions take about forty minutes and can be booked through the internal calendar.',
                'published', 10, ['Security', 'Announcements'], ['Can we choose an online session instead of the office one?']],
            ['rana@technova.com', 'Employee Events & Celebrations', 'Highlights from the Annual Team Retreat',
                'More than eighty colleagues joined this year\'s retreat by the sea. The program included a product roadmap session, team workshops, a beach football tournament and a dinner where we celebrated the people who joined us this year.',
                'published', 14, ['Events'], ['Best retreat so far, the workshops were really useful.', 'Already waiting for next year!']],
            ['rana@technova.com', 'News from the Top', 'We Are Hiring Five Backend Engineers',
                'The engineering department is growing. We are looking for five backend engineers with experience in Laravel, relational databases and automated testing. Referrals from employees are welcome and are reviewed first by the hiring team.',
                'published', 18, ['Hiring', 'Laravel'], []],
            ['writer@technova.com', 'Engineering & Development Updates', 'Rate Limiting Protects Our Public Endpoints',
                'Public endpoints now accept up to sixty requests per minute for each user or IP address. Normal usage is not affected, but scripts that send too many requests receive a clear error instead of slowing down the service for everyone else.',
                'published', 21, ['Security', 'Performance'], ['Good call, we saw a spike from a broken script last week.']],
            ['rana@technova.com', 'Employee Events & Celebrations', 'Draft: Plans for the End of Year Party',
                'This draft collects the first ideas for the end of year party: possible venues, dates in the second half of December and a short survey about food and music preferences. It will be published after the committee confirms the budget.',
                'draft', 1, ['Events'], []],
        ];

        foreach ($articles as [$email, $category, $title, $content, $status, $daysAgo, $articleTags, $comments]) {
            $date = now()->subDays($daysAgo);

            $article = new Article([
                'category_id' => $categories[$category],
                'title' => $title,
                'content' => $content,
                'status' => $status,
                'published_at' => $status === 'published' ? $date : null,
            ]);
            $article->user()->associate(User::where('email', $email)->firstOrFail());
            $article->created_at = $article->updated_at = $date;
            $article->save();
            $article->tags()->attach($tags->only($articleTags)->values());

            foreach ($comments as $i => $text) {
                $comment = new Comment(['content' => $text]);
                $comment->user()->associate($readers[$i % $readers->count()]);
                $article->comments()->save($comment);
            }
        }

        $first = Article::where('title', 'Moving Our Background Jobs to Redis Queues')->firstOrFail();
        Attachment::create([
            'user_id' => $first->user_id,
            'file_path' => 'uploads/articles/queue-architecture.pdf',
            'file_name' => 'queue-architecture.pdf',
            'mime_type' => 'application/pdf',
            'attachable_id' => $first->id,
            'attachable_type' => Article::class,
        ]);
    }
}
