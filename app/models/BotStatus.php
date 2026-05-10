<?php

namespace App\models;

use App\Core\Model;

final class BotStatus extends Model
{
    public function get(string $key, mixed $default = null): mixed
    {
        $stmt = $this->db->prepare('SELECT status_value FROM bot_statuses WHERE status_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : $value;
    }

    public function set(string $key, mixed $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO bot_statuses (status_key, status_value, last_run_at)
             VALUES (:status_key, :status_value, NOW())
             ON DUPLICATE KEY UPDATE status_value = VALUES(status_value), last_run_at = NOW()'
        );
        $stmt->execute(['status_key' => $key, 'status_value' => (string) $value]);
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM bot_statuses ORDER BY status_key ASC')->fetchAll();
    }
}
