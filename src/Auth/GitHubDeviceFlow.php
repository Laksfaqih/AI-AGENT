<?php

namespace PhpCopilot\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PhpCopilot\Database\Database;

class GitHubDeviceFlow
{
    private const GITHUB_DEVICE_CODE_URL = 'https://github.com/login/device/code';
    private const GITHUB_ACCESS_TOKEN_URL = 'https://github.com/login/oauth/access_token';
    private const GITHUB_COPILOT_CLIENT_ID = 'Iv1.b507a08c87ecfe98'; // GitHub Copilot Client ID
    private const COPILOT_SCOPE = 'read:user';
    
    private Client $client;
    private Database $db;
    private int $userId;

    public function __construct(Database $db, int $userId)
    {
        $this->client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'GitHubCopilotChat/1.0'
            ]
        ]);
        
        $this->db = $db;
        $this->userId = $userId;
    }

    /**
     * Start the device flow authentication process
     * Returns device code and user code for authentication
     */
    public function startDeviceFlow(): array
    {
        try {
            $response = $this->client->post(self::GITHUB_DEVICE_CODE_URL, [
                'json' => [
                    'client_id' => self::GITHUB_COPILOT_CLIENT_ID,
                    'scope' => self::COPILOT_SCOPE
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'device_code' => $data['device_code'],
                'user_code' => $data['user_code'],
                'verification_uri' => $data['verification_uri'],
                'expires_in' => $data['expires_in'],
                'interval' => $data['interval'] ?? 5
            ];
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to start device flow: ' . $e->getMessage());
        }
    }

    /**
     * Poll for access token after user has authenticated
     */
    public function pollForToken(string $deviceCode, int $interval = 5): ?array
    {
        $maxAttempts = 60; // Poll for up to 5 minutes
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            sleep($interval);
            $attempts++;

            try {
                $response = $this->client->post(self::GITHUB_ACCESS_TOKEN_URL, [
                    'json' => [
                        'client_id' => self::GITHUB_COPILOT_CLIENT_ID,
                        'device_code' => $deviceCode,
                        'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code'
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if (isset($data['access_token'])) {
                    // Save token to database
                    $this->saveToken($data['access_token']);
                    return $data;
                }

                if (isset($data['error'])) {
                    if ($data['error'] === 'authorization_pending') {
                        // Continue polling
                        continue;
                    } elseif ($data['error'] === 'slow_down') {
                        // Increase interval
                        $interval += 5;
                        continue;
                    } elseif ($data['error'] === 'expired_token') {
                        throw new \RuntimeException('Device code expired. Please start over.');
                    } elseif ($data['error'] === 'access_denied') {
                        throw new \RuntimeException('Access denied by user.');
                    }
                }
            } catch (GuzzleException $e) {
                // Continue polling on network errors
                continue;
            }
        }

        throw new \RuntimeException('Polling timeout. Please try again.');
    }

    /**
     * Save token to database
     */
    private function saveToken(string $token): void
    {
        $user = new User($this->db);
        $user->updateGitHubToken($this->userId, $token);
    }

    /**
     * Load token from database
     */
    public function loadToken(): ?string
    {
        $user = new User($this->db);
        return $user->getGitHubToken($this->userId);
    }

    /**
     * Check if we have a valid token
     */
    public function hasValidToken(): bool
    {
        $token = $this->loadToken();
        return $token !== null && !empty($token);
    }

    /**
     * Get the access token
     */
    public function getAccessToken(): ?string
    {
        return $this->loadToken();
    }

    /**
     * Clear stored token from database
     */
    public function clearToken(): void
    {
        $user = new User($this->db);
        $user->updateGitHubToken($this->userId, '');
    }

    /**
     * Initiate device flow (wrapper for API)
     */
    public function initiateDeviceFlow(): array
    {
        return $this->startDeviceFlow();
    }

    /**
     * Check token status once (non-blocking, for API use)
     */
    public function checkTokenStatus(string $deviceCode): array
    {
        try {
            $response = $this->client->post(self::GITHUB_ACCESS_TOKEN_URL, [
                'json' => [
                    'client_id' => self::GITHUB_COPILOT_CLIENT_ID,
                    'device_code' => $deviceCode,
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['access_token'])) {
                // Save token to database
                $this->saveToken($data['access_token']);
                return [
                    'status' => 'success',
                    'access_token' => $data['access_token']
                ];
            }

            if (isset($data['error'])) {
                return [
                    'status' => 'pending',
                    'error' => $data['error']
                ];
            }

            return [
                'status' => 'pending',
                'error' => 'authorization_pending'
            ];
        } catch (GuzzleException $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
}
