<?php

namespace App\models;

use App\Core\Model;

final class Admin extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE email = :email AND is_active = 1 LIMIT 1');
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    }

    public function touchLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function ensureDefaultAdmin(string $email, string $password, string $name = 'Administrator'): void
    {
        $stmt = $this->db->prepare('SELECT id FROM admins WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO admins (name, email, password_hash) VALUES (:name, :email, :password_hash)'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }
}
