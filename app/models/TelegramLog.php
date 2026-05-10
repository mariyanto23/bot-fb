<?php

namespace App\models;

use App\Core\Model;

final class TelegramLog extends Model
{
    public function create(string $status, string $message, ?string $responseBody = null): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO telegram_logs (status, message, response_body)
             VALUES (:status, :message, :response_body)'
        );
        $stmt->execute([
            'status' => $status,
            'message' => $message,
            'response_body' => $responseBody,
        ]);
    }

    public function latest(int $limit = 50): array
    {
        $stmt = $this->db->prepare('SELECT * FROM telegram_logs ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
