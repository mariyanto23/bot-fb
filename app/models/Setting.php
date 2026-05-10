<?php

namespace App\models;

use App\Core\Model;

final class Setting extends Model
{
    public function allAssoc(): array
    {
        $settings = [];
        foreach ($this->db->query('SELECT setting_key, setting_value FROM settings ORDER BY setting_key ASC')->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $stmt = $this->db->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : $value;
    }

    public function set(string $key, mixed $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO settings (setting_key, setting_value)
             VALUES (:setting_key, :setting_value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute(['setting_key' => $key, 'setting_value' => (string) $value]);
    }

    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set((string) $key, $value);
        }
    }
}
