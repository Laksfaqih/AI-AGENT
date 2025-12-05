<?php

namespace PhpCopilot\Auth;

use PhpCopilot\Database\Database;

/**
 * User class for authentication and user management
 */
class User
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new user
     * 
     * @param string $username Username
     * @param string $password Plain text password
     * @param string|null $email Email address (optional)
     * @return int User ID
     * @throws \RuntimeException
     */
    public function create(string $username, string $password, ?string $email = null): int
    {
        // Validate username
        if (strlen($username) < 3) {
            throw new \RuntimeException('Username must be at least 3 characters long');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            throw new \RuntimeException('Username can only contain letters, numbers, underscores, and hyphens');
        }

        // Validate password
        if (strlen($password) < 6) {
            throw new \RuntimeException('Password must be at least 6 characters long');
        }

        // Validate email if provided
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Invalid email address');
        }

        // Check if username already exists
        if ($this->getUserByUsername($username) !== null) {
            throw new \RuntimeException('Username already exists');
        }

        // Check if email already exists
        if ($email !== null && $this->getUserByEmail($email) !== null) {
            throw new \RuntimeException('Email already exists');
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

        // Insert user
        $userId = $this->db->insert('users', [
            'username' => $username,
            'password_hash' => $passwordHash,
            'email' => $email,
        ]);

        return (int)$userId;
    }

    /**
     * Authenticate a user
     * 
     * @param string $username Username
     * @param string $password Plain text password
     * @return array|null User data if authenticated, null otherwise
     */
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->getUserByUsername($username);

        if ($user === null) {
            return null;
        }

        // Check if user is active
        if (!$user['is_active']) {
            return null;
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        // Update last login time
        $this->updateLastLogin((int)$user['id']);

        // Remove password hash from returned data
        unset($user['password_hash']);

        return $user;
    }

    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|null User data or null if not found
     */
    public function getUserById(int $userId): ?array
    {
        $user = $this->db->queryOne(
            'SELECT * FROM users WHERE id = :id',
            [':id' => $userId]
        );

        if ($user !== null) {
            unset($user['password_hash']);
        }

        return $user;
    }

    /**
     * Get user by username
     * 
     * @param string $username Username
     * @return array|null User data or null if not found
     */
    public function getUserByUsername(string $username): ?array
    {
        return $this->db->queryOne(
            'SELECT * FROM users WHERE username = :username',
            [':username' => $username]
        );
    }

    /**
     * Get user by email
     * 
     * @param string $email Email address
     * @return array|null User data or null if not found
     */
    public function getUserByEmail(string $email): ?array
    {
        return $this->db->queryOne(
            'SELECT * FROM users WHERE email = :email',
            [':email' => $email]
        );
    }

    /**
     * Update user password
     * 
     * @param int $userId User ID
     * @param string $newPassword New plain text password
     * @return bool Success
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        if (strlen($newPassword) < 6) {
            throw new \RuntimeException('Password must be at least 6 characters long');
        }

        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);

        $affected = $this->db->update(
            'users',
            [
                'password_hash' => $passwordHash,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = :id',
            [':id' => $userId]
        );

        return $affected > 0;
    }

    /**
     * Update GitHub token for user
     * 
     * @param int $userId User ID
     * @param string $token GitHub access token
     * @return bool Success
     */
    public function updateGitHubToken(int $userId, string $token): bool
    {
        $affected = $this->db->update(
            'users',
            [
                'github_token' => $token,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = :id',
            [':id' => $userId]
        );

        return $affected > 0;
    }

    /**
     * Get GitHub token for user
     * 
     * @param int $userId User ID
     * @return string|null GitHub token or null if not set
     */
    public function getGitHubToken(int $userId): ?string
    {
        $token = $this->db->queryValue(
            'SELECT github_token FROM users WHERE id = :id',
            [':id' => $userId]
        );

        return $token ?: null;
    }

    /**
     * Update last login timestamp
     * 
     * @param int $userId User ID
     * @return bool Success
     */
    public function updateLastLogin(int $userId): bool
    {
        $affected = $this->db->update(
            'users',
            ['last_login_at' => date('Y-m-d H:i:s')],
            'id = :id',
            [':id' => $userId]
        );

        return $affected > 0;
    }

    /**
     * Deactivate a user
     * 
     * @param int $userId User ID
     * @return bool Success
     */
    public function deactivate(int $userId): bool
    {
        $affected = $this->db->update(
            'users',
            [
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = :id',
            [':id' => $userId]
        );

        return $affected > 0;
    }

    /**
     * Activate a user
     * 
     * @param int $userId User ID
     * @return bool Success
     */
    public function activate(int $userId): bool
    {
        $affected = $this->db->update(
            'users',
            [
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = :id',
            [':id' => $userId]
        );

        return $affected > 0;
    }

    /**
     * Update user email
     * 
     * @param int $userId User ID
     * @param string $email New email address
     * @return bool Success
     */
    public function updateEmail(int $userId, string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Invalid email address');
        }

        // Check if email already exists for another user
        $existing = $this->getUserByEmail($email);
        if ($existing !== null && (int)$existing['id'] !== $userId) {
            throw new \RuntimeException('Email already exists');
        }

        $affected = $this->db->update(
            'users',
            [
                'email' => $email,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = :id',
            [':id' => $userId]
        );

        return $affected > 0;
    }

    /**
     * Get total user count
     * 
     * @return int Total number of users
     */
    public function getTotalUsers(): int
    {
        return (int)$this->db->queryValue('SELECT COUNT(*) FROM users');
    }

    /**
     * Get active user count
     * 
     * @return int Number of active users
     */
    public function getActiveUsers(): int
    {
        return (int)$this->db->queryValue('SELECT COUNT(*) FROM users WHERE is_active = 1');
    }
}
