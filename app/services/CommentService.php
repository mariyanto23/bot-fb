<?php

namespace App\services;

use App\Core\CronLock;
use App\models\BotStatus;
use App\models\Comment;
use App\models\Post;
use App\models\TargetGroup;

final class CommentService
{
    public function __construct(
        private ?Post $posts = null,
        private ?Comment $comments = null,
        private ?TargetGroup $targets = null,
        private ?FacebookService $facebook = null,
        private ?LoggerService $logger = null,
        private ?TelegramService $telegram = null,
        private ?RandomizerService $randomizer = null,
        private ?BotStatus $status = null,
        private ?SettingService $settings = null
    ) {
        $this->posts ??= new Post();
        $this->comments ??= new Comment();
        $this->targets ??= new TargetGroup();
        $this->facebook ??= new FacebookService();
        $this->logger ??= new LoggerService();
        $this->telegram ??= new TelegramService();
        $this->randomizer ??= new RandomizerService();
        $this->status ??= new BotStatus();
        $this->settings ??= new SettingService();
    }

    public function fetchPosts(): array
    {
        $created = 0;
        $skipped = 0;

        foreach ($this->targets->active() as $target) {
            try {
                $items = $this->facebook->fetchPostsFromTarget($target);
                $targetCreated = 0;
                $targetSkipped = 0;

                foreach ($items as $item) {
                    if ($this->posts->existsByHash($item['post_hash'])) {
                        $skipped++;
                        $targetSkipped++;
                        continue;
                    }

                    $item['target_group_id'] = (int) $target['id'];
                    $this->posts->create($item);
                    $created++;
                    $targetCreated++;
                }
                $this->targets->touchFetched((int) $target['id']);

                if ($items === []) {
                    $this->logger->warning('fetch_posts_empty', 'Tidak ada link post yang dikenali dari target ' . $target['name'], null, [
                        'target_id' => $target['id'],
                        'source_url' => $target['source_url'],
                        'debug' => $this->facebook->lastFetchDebug(),
                    ]);
                    continue;
                }

                $this->logger->info('fetch_posts', 'Fetched posts from ' . $target['name'], null, [
                    'target_id' => $target['id'],
                    'found' => count($items),
                    'created' => $targetCreated,
                    'skipped' => $targetSkipped,
                    'debug' => $this->facebook->lastFetchDebug(),
                ]);
            } catch (\Throwable $exception) {
                $this->logger->error('fetch_posts_failed', $exception->getMessage(), null, ['target_id' => $target['id']]);
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    public function sendPendingComments(?int $limit = null, bool $respectDelay = true): array
    {
        $limit ??= $this->settings->int('bot_batch_limit', (int) config('config.bot.batch_limit', 5));
        $activeComments = $this->comments->active();
        if ($activeComments === []) {
            $this->logger->warning('no_comments', 'No active comments are available.');
            return ['sent' => 0, 'failed' => 0, 'message' => 'No active comments available.'];
        }

        $sent = 0;
        $failed = 0;
        $cooldown = $this->settings->int('bot_cooldown_seconds', (int) config('config.bot.cooldown_seconds', 900));

        foreach ($this->posts->pending($limit) as $post) {
            $comment = $this->randomizer->pick($activeComments);
            if ($comment === null) {
                break;
            }

            try {
                $result = $this->facebook->sendComment($post, (string) $comment['body']);
                if ($result['success'] === true) {
                    $this->posts->markCommented((int) $post['id'], (int) $comment['id']);
                    $this->comments->markUsed((int) $comment['id']);
                    $this->logger->info('comment_success', $result['message'], (int) $post['id']);
                    $this->telegram->send('Comment success: ' . $post['post_url']);
                    $sent++;
                } else {
                    $this->posts->markFailed((int) $post['id'], $result['message'], $cooldown);
                    $this->logger->warning('comment_failed', $result['message'], (int) $post['id']);
                    $this->telegram->send('Comment failed: ' . $result['message']);
                    $failed++;
                }
            } catch (\Throwable $exception) {
                $this->posts->markFailed((int) $post['id'], $exception->getMessage(), $cooldown);
                $this->logger->error('comment_exception', $exception->getMessage(), (int) $post['id']);
                $failed++;
            }

            if ($respectDelay) {
                sleep($this->randomizer->delaySeconds(
                    $this->settings->int('bot_min_delay_seconds', (int) config('config.bot.min_delay_seconds', 20)),
                    $this->settings->int('bot_max_delay_seconds', (int) config('config.bot.max_delay_seconds', 90))
                ));
            }
        }

        $message = sprintf('Sent %d comments, %d failed.', $sent, $failed);
        $this->status->set('last_result', $message);
        return ['sent' => $sent, 'failed' => $failed, 'message' => $message];
    }

    public function runBot(): array
    {
        if (!$this->settings->bool('bot_enabled', (bool) config('config.bot.enabled', true))) {
            return ['message' => 'Bot is disabled.', 'fetch' => [], 'comments' => []];
        }

        $lock = new CronLock('run_bot');
        if (!$lock->acquire()) {
            return ['message' => 'Bot is already running.', 'fetch' => [], 'comments' => []];
        }

        try {
            $this->status->set('last_result', 'Bot started.');
            $fetch = $this->fetchPosts();
            $comments = $this->sendPendingComments();
            $this->status->set('last_result', $comments['message']);
            return ['message' => 'Bot run completed.', 'fetch' => $fetch, 'comments' => $comments];
        } finally {
            $lock->release();
        }
    }
}
