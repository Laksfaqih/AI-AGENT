#!/usr/bin/env php
<?php

/**
 * GitHub Copilot Authentication Script
 * Run this script to authenticate with GitHub and get an access token
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpCopilot\Auth\GitHubDeviceFlow;
use PhpCopilot\Database\Database;
use PhpCopilot\Auth\User;

echo "=== GitHub Copilot Authentication ===\n\n";

// Initialize database
$db = new Database(__DIR__ . '/storage/database.sqlite');

// Get or create admin user for GitHub token storage
$user = new User($db);
$adminUser = $user->getUserByUsername('admin');

if ($adminUser === null) {
    echo "Creating admin user...\n";
    $adminUserId = $user->create('admin', 'admin123');
    echo "✓ Admin user created (ID: $adminUserId)\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n\n";
} else {
    $adminUserId = (int)$adminUser['id'];
}

// Initialize auth with database (using admin user)
$auth = new GitHubDeviceFlow($db, $adminUserId);

// Check if we already have a token
if ($auth->hasValidToken()) {
    echo "✓ You already have a valid token.\n";
    echo "  Token: " . substr($auth->getAccessToken(), 0, 20) . "...\n";
    echo "  Stored in: Database (admin user)\n\n";
    
    $response = readline("Do you want to re-authenticate? (y/N): ");
    if (strtolower(trim($response)) !== 'y') {
        echo "Using existing token.\n";
        exit(0);
    }
    
    $auth->clearToken();
    echo "Cleared existing token.\n\n";
}

try {
    // Start device flow
    echo "Starting GitHub Device Flow authentication...\n";
    $deviceFlow = $auth->startDeviceFlow();
    
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "  Please visit: " . $deviceFlow['verification_uri'] . "\n";
    echo "  And enter code: " . $deviceFlow['user_code'] . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n";
    echo "Waiting for authentication";
    
    // Poll for token
    $tokenData = $auth->pollForToken(
        $deviceFlow['device_code'],
        $deviceFlow['interval']
    );
    
    echo "\n\n";
    echo "✓ Authentication successful!\n";
    echo "  Access Token: " . substr($tokenData['access_token'], 0, 20) . "...\n";
    echo "  Token Type: " . ($tokenData['token_type'] ?? 'bearer') . "\n";
    echo "\n";
    echo "Token saved to database (admin user)\n";
    echo "You can now use the API!\n";
    
} catch (\Exception $e) {
    echo "\n\n";
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
