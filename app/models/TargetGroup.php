<?php

namespace App\models;

use App\Core\Model;

final class TargetGroup extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM target_groups ORDER BY id DESC')->fetchAll();
    }

    public function active(): array
    {
        return $this->db->query('SELECT * FROM target_groups WHERE is_active = 1 ORDER BY id ASC')->fetchAll();
    }

    public function create(string $name, string $sourceUrl, ?string $facebookGroupId, bool $isActive): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO target_groups (name, source_url, facebook_group_id, is_active)
             VALUES (:name, :source_url, :facebook_group_id, :is_active)'
        );
        $stmt->execute([
            'name' => $name,
            'source_url' => $sourceUrl,
            'facebook_group_id' => $facebookGroupId,
            'is_active' => $isActive ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $sourceUrl, ?string $facebookGroupId, bool $isActive): void
    {
        $stmt = $this->db->prepare(
            'UPDATE target_groups
             SET name = :name, source_url = :source_url, facebook_group_id = :facebook_group_id, is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'source_url' => $sourceUrl,
            'facebook_group_id' => $facebookGroupId,
            'is_active' => $isActive ? 1 : 0,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM target_groups WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function touchFetched(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE target_groups SET last_fetched_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
