<?php

namespace App\models;

use App\Core\Model;

final class Log extends Model
{
    public function create(string $level, string $status, string $message, ?int $relatedPostId = null, array $context = []): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO logs (level, status, message, related_post_id, context)
             VALUES (:level, :status, :message, :related_post_id, :context)'
        );
        $stmt->execute([
            'level' => $level,
            'status' => $status,
            'message' => $message,
            'related_post_id' => $relatedPostId,
            'context' => $context === [] ? null : json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function latest(int $limit = 100): array
    {
        $stmt = $this->db->prepare('SELECT * FROM logs ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
