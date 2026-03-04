<?php

declare(strict_types=1);

require_once APP_PATH . '/Core/Model.php';

class User extends Model
{
    public function create(string $username, string $password, string $email): bool
    {
        $sql = 'INSERT INTO users (username, password, email) VALUES (:username, :password, :email)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'email' => $email,
        ]);
    }

    public function findByLoginIdentifier(string $loginIdentifier): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE username = :login_identifier OR email = :login_identifier LIMIT 1'
        );
        $stmt->execute(['login_identifier' => $loginIdentifier]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        return $this->findByLoginIdentifier($username);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function usernameExistsExceptId(string $username, int $excludedId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = :username AND id <> :id LIMIT 1');
        $stmt->execute([
            'username' => $username,
            'id' => $excludedId,
        ]);

        return (bool) $stmt->fetch();
    }

    public function emailExistsExceptId(string $email, int $excludedId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $stmt->execute([
            'email' => $email,
            'id' => $excludedId,
        ]);

        return (bool) $stmt->fetch();
    }

    public function updateProfile(int $id, string $username, string $email, ?string $newPassword): bool
    {
        if ($newPassword !== null && $newPassword !== '') {
            $stmt = $this->db->prepare(
                'UPDATE users SET username = :username, email = :email, password = :password WHERE id = :id'
            );

            return $stmt->execute([
                'id' => $id,
                'username' => $username,
                'email' => $email,
                'password' => password_hash($newPassword, PASSWORD_BCRYPT),
            ]);
        }

        $stmt = $this->db->prepare('UPDATE users SET username = :username, email = :email WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'username' => $username,
            'email' => $email,
        ]);
    }
}
