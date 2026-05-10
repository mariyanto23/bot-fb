<?php

namespace App\models;

use App\Core\Model;

final class Post extends Model
{
    public function latest(int $limit = 50): array
    {
        $stmt = $this->db->prepare('SELECT p.*, tg.name AS target_name FROM posts p LEFT JOIN target_groups tg ON tg.id = p.target_group_id ORDER BY p.id DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function pending(int $limit): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM posts
             WHERE status = "pending" AND (next_attempt_at IS NULL OR next_attempt_at <= NOW())
             ORDER BY created_at ASC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function existsByHash(string $hash): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM posts WHERE post_hash = :post_hash LIMIT 1');
        $stmt->execute(['post_hash' => $hash]);
        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO posts (target_group_id, facebook_post_id, post_url, post_hash, author_name, caption, status)
             VALUES (:target_group_id, :facebook_post_id, :post_url, :post_hash, :author_name, :caption, "pending")'
        );
        $stmt->execute([
            'target_group_id' => $data['target_group_id'] ?? null,
            'facebook_post_id' => $data['facebook_post_id'],
            'post_url' => $data['post_url'],
            'post_hash' => $data['post_hash'],
            'author_name' => $data['author_name'] ?? null,
            'caption' => $data['caption'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function markCommented(int $id, int $commentId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE posts
             SET status = "commented", comment_id = :comment_id, commented_at = NOW(), last_error = NULL, attempt_count = attempt_count + 1
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'comment_id' => $commentId]);
    }

    public function markFailed(int $id, string $error, int $cooldownSeconds): void
    {
        $nextAttemptAt = date('Y-m-d H:i:s', time() + $cooldownSeconds);
        $stmt = $this->db->prepare(
            'UPDATE posts
             SET status = "pending", last_error = :last_error, attempt_count = attempt_count + 1,
                 next_attempt_at = :next_attempt_at
             WHERE id = :id'
        );
        $stmt->bindValue('id', $id, \PDO::PARAM_INT);
        $stmt->bindValue('last_error', $error);
        $stmt->bindValue('next_attempt_at', $nextAttemptAt);
        $stmt->execute();
    }

    public function skip(int $id, string $reason): void
    {
        $stmt = $this->db->prepare('UPDATE posts SET status = "skipped", last_error = :reason WHERE id = :id');
        $stmt->execute(['id' => $id, 'reason' => $reason]);
    }

    public function stats(): array
    {
        $row = $this->db->query(
            'SELECT
                COUNT(*) AS total,
                SUM(status = "pending") AS pending,
                SUM(status = "commented") AS commented,
                SUM(status = "failed") AS failed,
                SUM(status = "skipped") AS skipped
             FROM posts'
        )->fetch();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'pending' => (int) ($row['pending'] ?? 0),
            'commented' => (int) ($row['commented'] ?? 0),
            'failed' => (int) ($row['failed'] ?? 0),
            'skipped' => (int) ($row['skipped'] ?? 0),
        ];
    }
}
