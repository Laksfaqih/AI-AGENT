<?php

namespace PhpCopilot\Auth;

use PhpCopilot\Database\Database;

/**
 * Session class for managing user sessions
 */
class Session
{
    private Database $db;
    private int $defaultLifetime = 86400; // 24 hours

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new session
     * 
     * @param int $userId User ID
     * @param int|null $expiresInSeconds Session lifetime in seconds (default: 24 hours)
     * @param string|null $ipAddress Client IP address
     * @param string|null $userAgent Client user agent
     * @return string Session token
     */
    public function create(
        int $userId,
        ?int $expiresInSeconds = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): string {
        $expiresInSeconds = $expiresInSeconds ?? $this->defaultLifetime;
        
        // Generate cryptographically secure token
        $token = bin2hex(random_bytes(32));
        
        // Calculate expiration time
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresInSeconds);
        
        // Insert session
        $this->db->insert('sessions', [
            'user_id' => $userId,
            'session_token' => $token,
            'expires_at' => $expiresAt,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
        
        return $token;
    }

    /**
     * Validate a session token
     * 
     * @param string $token Session token
     * @return array|null Session data with user info if valid, null otherwise
     */
    public function validate(string $token): ?array
    {
        $session = $this->db->queryOne(
            'SELECT s.*, u.username, u.email, u.is_active, u.created_at as user_created_at, u.last_login_at
             FROM sessions s
             JOIN users u ON s.user_id = u.id
             WHERE s.session_token = :token
             AND s.expires_at > datetime("now")
             AND u.is_active = 1',
            [':token' => $token]
        );
        
        return $session;
    }

    /**
     * Destroy a session
     * 
     * @param string $token Session token
     * @return bool Success
     */
    public function destroy(string $token): bool
    {
        $affected = $this->db->delete(
            'sessions',
            'session_token = :token',
            [':token' => $token]
        );
        
        return $affected > 0;
    }

    /**
     * Destroy all sessions for a user
     * 
     * @param int $userId User ID
     * @return int Number of sessions destroyed
     */
    public function destroyAllForUser(int $userId): int
    {
        return $this->db->delete(
            'sessions',
            'user_id = :user_id',
            [':user_id' => $userId]
        );
    }

    /**
     * Clean up expired sessions
     * 
     * @return int Number of sessions cleaned up
     */
    public function cleanExpired(): int
    {
        return $this->db->delete(
            'sessions',
            'expires_at <= datetime("now")',
            []
        );
    }

    /**
     * Get all sessions for a user
     * 
     * @param int $userId User ID
     * @return array Array of session data
     */
    public function getUserSessions(int $userId): array
    {
        return $this->db->query(
            'SELECT id, session_token, expires_at, created_at, ip_address, user_agent
             FROM sessions
             WHERE user_id = :user_id
             AND expires_at > datetime("now")
             ORDER BY created_at DESC',
            [':user_id' => $userId]
        );
    }

    /**
     * Extend a session's expiration time
     * 
     * @param string $token Session token
     * @param int|null $seconds Number of seconds to extend
     * @return bool Success
     */
    public function extend(string $token, ?int $seconds = null): bool
    {
        $seconds = $seconds ?? $this->defaultLifetime;
        
        $newExpiresAt = date('Y-m-d H:i:s', time() + $seconds);
        
        $affected = $this->db->update(
            'sessions',
            ['expires_at' => $newExpiresAt],
            'session_token = :token',
            [':token' => $token]
        );
        
        return $affected > 0;
    }

    /**
     * Get session by token
     * 
     * @param string $token Session token
     * @return array|null Session data or null if not found
     */
    public function getByToken(string $token): ?array
    {
        return $this->db->queryOne(
            'SELECT * FROM sessions WHERE session_token = :token',
            [':token' => $token]
        );
    }

    /**
     * Check if a session exists and is valid
     * 
     * @param string $token Session token
     * @return bool True if session is valid
     */
    public function exists(string $token): bool
    {
        $count = $this->db->queryValue(
            'SELECT COUNT(*) FROM sessions
             WHERE session_token = :token
             AND expires_at > datetime("now")',
            [':token' => $token]
        );
        
        return $count > 0;
    }

    /**
     * Get total active sessions count
     * 
     * @return int Number of active sessions
     */
    public function getActiveSessions(): int
    {
        return (int)$this->db->queryValue(
            'SELECT COUNT(*) FROM sessions WHERE expires_at > datetime("now")'
        );
    }

    /**
     * Set default session lifetime
     * 
     * @param int $seconds Lifetime in seconds
     */
    public function setDefaultLifetime(int $seconds): void
    {
        $this->defaultLifetime = $seconds;
    }
}
