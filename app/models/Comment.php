<?php

namespace App\models;

use App\Core\Model;

final class Comment extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM comments ORDER BY id DESC')->fetchAll();
    }

    public function active(): array
    {
        return $this->db->query('SELECT * FROM comments WHERE is_active = 1 ORDER BY COALESCE(last_used_at, "1970-01-01"), RAND()')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM comments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $comment = $stmt->fetch();
        return $comment ?: null;
    }

    public function create(string $body, bool $isActive = true): int
    {
        $stmt = $this->db->prepare('INSERT INTO comments (body, is_active) VALUES (:body, :is_active)');
        $stmt->execute(['body' => $body, 'is_active' => $isActive ? 1 : 0]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $body, bool $isActive): void
    {
        $stmt = $this->db->prepare('UPDATE comments SET body = :body, is_active = :is_active WHERE id = :id');
        $stmt->execute(['id' => $id, 'body' => $body, 'is_active' => $isActive ? 1 : 0]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE comments SET last_used_at = NOW(), used_count = used_count + 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
