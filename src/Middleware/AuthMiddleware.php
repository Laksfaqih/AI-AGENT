<?php

namespace PhpCopilot\Middleware;

use PhpCopilot\Auth\Session;

/**
 * Authentication Middleware
 * Validates session tokens and attaches user data to requests
 */
class AuthMiddleware
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Validate session token from request
     * 
     * @return array|null User data if authenticated, null otherwise
     */
    public function authenticate(): ?array
    {
        $token = $this->getTokenFromRequest();

        if ($token === null) {
            return null;
        }

        $sessionData = $this->session->validate($token);

        if ($sessionData === null) {
            return null;
        }

        // Extend session on each request
        $this->session->extend($token);

        return [
            'user_id' => (int)$sessionData['user_id'],
            'username' => $sessionData['username'],
            'email' => $sessionData['email'],
            'session_token' => $token,
        ];
    }

    /**
     * Get token from request headers or query parameters
     * 
     * @return string|null Session token or null if not found
     */
    private function getTokenFromRequest(): ?string
    {
        // Check Authorization header (Bearer token)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            if (preg_match('/Bearer\s+(.+)/i', $auth, $matches)) {
                return $matches[1];
            }
        }

        // Check X-Session-Token header
        if (isset($_SERVER['HTTP_X_SESSION_TOKEN'])) {
            return $_SERVER['HTTP_X_SESSION_TOKEN'];
        }

        // Check query parameter (less secure, but useful for testing)
        if (isset($_GET['session_token'])) {
            return $_GET['session_token'];
        }

        return null;
    }

    /**
     * Require authentication or send 401 response
     * 
     * @return array User data
     */
    public function requireAuth(): array
    {
        $user = $this->authenticate();

        if ($user === null) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Unauthorized',
                'message' => 'Valid session token required'
            ]);
            exit;
        }

        return $user;
    }

    /**
     * Check if request is authenticated
     * 
     * @return bool True if authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticate() !== null;
    }
}
