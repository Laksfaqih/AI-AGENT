<?php

/**
 * Indonesia AI Agent API
 * Main API entry point
 * 
 * Built for the Indonesian tech community
 * Website: https://indonesiasatu.com
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpCopilot\Auth\GitHubDeviceFlow;
use PhpCopilot\Client\AIAgentClient;
use PhpCopilot\Database\Database;
use PhpCopilot\Database\PostgresConnection;
use PhpCopilot\Database\MySQLConnection;
use PhpCopilot\Auth\User;
use PhpCopilot\Auth\Session;
use PhpCopilot\Chat\ChatHistory;
use PhpCopilot\Utils\Geocoder;

// Start PHP session for session management
session_start();

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Simple router
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Helper function to send JSON response
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Helper function to get JSON input
function getJsonInput(): array
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse(['error' => 'Invalid JSON input'], 400);
    }
    
    return $data ?? [];
}

// Initialize database
$db = new Database(__DIR__ . '/../storage/database.sqlite');
$userManager = new User($db);
$sessionManager = new Session($db);
$postgresManager = new PostgresConnection($db);
$mysqlManager = new MySQLConnection($db);
$chatHistory = new ChatHistory($db);

// Helper function to get current user from session
function getCurrentUser(): ?array
{
    global $sessionManager;
    
    // Check for session token in multiple places:
    // 1. Custom header (for API calls from frontend)
    // 2. PHP session (for traditional session management)
    $sessionToken = null;
    
    if (isset($_SERVER['HTTP_X_SESSION_TOKEN'])) {
        $sessionToken = $_SERVER['HTTP_X_SESSION_TOKEN'];
    } elseif (isset($_SESSION['session_token'])) {
        $sessionToken = $_SESSION['session_token'];
    }
    
    if (!$sessionToken) {
        return null;
    }
    
    $session = $sessionManager->validate($sessionToken);
    return $session;
}

// Helper function to require authentication
function requireAuth(): array
{
    $user = getCurrentUser();
    if ($user === null) {
        jsonResponse([
            'error' => 'Not authenticated',
            'message' => 'Please login first'
        ], 401);
    }
    return $user;
}

// Check authentication for protected routes
// Note: /api/status is public so users can check auth status before logging in
$protectedRoutes = [
    '/api/chat', '/api/complete', '/api/test', '/api/auth/start', '/api/auth/poll',
    '/api/user/profile', '/api/logout',
    '/api/postgres/connections', '/api/postgres/connection', '/api/postgres/test',
    '/api/postgres/schema', '/api/postgres/query',
    '/api/mysql/connections', '/api/mysql/connection', '/api/mysql/test',
    '/api/mysql/schema', '/api/mysql/query'
];
if (in_array($path, $protectedRoutes) || strpos($path, '/api/postgres/') === 0 || strpos($path, '/api/mysql/') === 0) {
    requireAuth();
}

// Routes
try {
    switch ($path) {
        case '/':
        case '/api':
            jsonResponse([
                'name' => 'Indonesia AI Agent API',
                'version' => '1.0.0',
                'description' => 'Powerful AI Chat Application',
                'website' => 'https://indonesiasatu.com',
                'endpoints' => [
                    'POST /api/login' => 'Login user',
                    'POST /api/logout' => 'Logout user',
                    'GET /api/user/profile' => 'Get current user profile',
                    'GET /api/status' => 'Check authentication status',
                    'GET /api/test' => 'Test Copilot connection',
                    'GET /api/models' => 'Get available AI models',
                    'POST /api/chat' => 'Send a chat message',
                    'POST /api/complete' => 'Get code completion',
                    'POST /api/auth/start' => 'Start GitHub device flow',
                    'POST /api/auth/poll' => 'Poll GitHub auth status',
                    'GET /api/postgres/connections' => 'Get all PostgreSQL connections',
                    'POST /api/postgres/connections' => 'Add new PostgreSQL connection',
                    'DELETE /api/postgres/connection' => 'Delete PostgreSQL connection',
                    'POST /api/postgres/test' => 'Test PostgreSQL connection',
                    'POST /api/postgres/schema' => 'Get database schema',
                    'POST /api/postgres/query' => 'Execute SQL query',
                    'GET /api/mysql/connections' => 'Get all MySQL connections',
                    'POST /api/mysql/connections' => 'Add new MySQL connection',
                    'DELETE /api/mysql/connection' => 'Delete MySQL connection',
                    'POST /api/mysql/test' => 'Test MySQL connection',
                    'POST /api/mysql/schema' => 'Get MySQL database schema',
                    'POST /api/mysql/query' => 'Execute MySQL query'
                ]
            ]);
            break;

        case '/api/login':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $input = getJsonInput();
            
            if (!isset($input['username']) || !isset($input['password'])) {
                jsonResponse(['error' => 'Missing required fields: username, password'], 400);
            }
            
            $userData = $userManager->authenticate($input['username'], $input['password']);
            
            if ($userData === null) {
                jsonResponse([
                    'success' => false,
                    'error' => 'Invalid username or password'
                ], 401);
            }
            
            // Create session
            $sessionToken = $sessionManager->create(
                (int)$userData['id'],
                null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );
            
            // Store session token in PHP session
            $_SESSION['session_token'] = $sessionToken;
            
            jsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $userData['id'],
                    'username' => $userData['username'],
                    'email' => $userData['email']
                ],
                'session_token' => $sessionToken
            ]);
            break;

        case '/api/logout':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            
            if (isset($_SESSION['session_token'])) {
                $sessionManager->destroy($_SESSION['session_token']);
                unset($_SESSION['session_token']);
            }
            
            session_destroy();
            
            jsonResponse([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
            break;

        case '/api/user/profile':
            if ($method !== 'GET') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            
            jsonResponse([
                'success' => true,
                'user' => [
                    'id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'is_active' => $user['is_active'],
                    'created_at' => $user['user_created_at'] ?? $user['created_at'],
                    'last_login_at' => $user['last_login_at'] ?? null
                ]
            ]);
            break;

        case '/api/status':
            if ($method !== 'GET') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $currentUser = getCurrentUser();
            $isLoggedIn = $currentUser !== null;
            $hasGitHubToken = false;
            
            if ($isLoggedIn) {
                $githubToken = $userManager->getGitHubToken((int)$currentUser['user_id']);
                $hasGitHubToken = !empty($githubToken);
            }
            
            jsonResponse([
                'logged_in' => $isLoggedIn,
                'authenticated' => $hasGitHubToken,
                'token_exists' => $hasGitHubToken,
                'user' => $isLoggedIn ? [
                    'username' => $currentUser['username'],
                    'email' => $currentUser['email']
                ] : null,
                'message' => $hasGitHubToken 
                    ? 'Ready to use AI Agent' 
                    : ($isLoggedIn ? 'Please authenticate with GitHub to access AI models' : 'Please login first')
            ]);
            break;

        case '/api/test':
            if ($method !== 'GET') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            $githubToken = $userManager->getGitHubToken((int)$user['user_id']);
            
            if (empty($githubToken)) {
                jsonResponse([
                    'error' => 'GitHub token not found',
                    'message' => 'Please authenticate with GitHub first'
                ], 401);
            }
            
            $client = new AIAgentClient($githubToken);
            $isConnected = $client->testConnection();
            
            jsonResponse([
                'connected' => $isConnected,
                'message' => $isConnected 
                    ? 'AI Agent is ready!' 
                    : 'Failed to connect to AI Agent'
            ]);
            break;

        case '/api/models':
            if ($method !== 'GET') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            jsonResponse([
                'models' => [
                    // FREE MODELS (0x cost)
                    [
                        'id' => 'gpt-4o',
                        'name' => 'GPT-4o',
                        'provider' => 'OpenAI',
                        'description' => 'Latest GPT-4 optimized model',
                        'speed' => 'fast',
                        'quality' => 'high',
                        'pricing' => 'free',
                        'cost_multiplier' => '0x'
                    ],
                    [
                        'id' => 'gpt-4.1',
                        'name' => 'GPT-4.1',
                        'provider' => 'OpenAI',
                        'description' => 'Enhanced GPT-4 model',
                        'speed' => 'fast',
                        'quality' => 'high',
                        'pricing' => 'free',
                        'cost_multiplier' => '0x'
                    ],
                    [
                        'id' => 'gpt-5-mini',
                        'name' => 'GPT-5 Mini',
                        'provider' => 'OpenAI',
                        'description' => 'Lightweight GPT-5 model',
                        'speed' => 'very-fast',
                        'quality' => 'high',
                        'pricing' => 'free',
                        'cost_multiplier' => '0x'
                    ],
                    [
                        'id' => 'grok-code-fast-1',
                        'name' => 'Grok Code Fast',
                        'provider' => 'xAI',
                        'description' => 'Fast code generation model',
                        'speed' => 'very-fast',
                        'quality' => 'high',
                        'pricing' => 'free',
                        'cost_multiplier' => '0x'
                    ],
                    
                    // PAID MODELS - Low Cost (0.33x)
                    [
                        'id' => 'claude-haiku-4.5',
                        'name' => 'Claude Haiku 4.5',
                        'provider' => 'Anthropic',
                        'description' => 'Fast and efficient model',
                        'speed' => 'very-fast',
                        'quality' => 'medium',
                        'pricing' => 'paid',
                        'cost_multiplier' => '0.33x'
                    ],
                    
                    // PAID MODELS - Standard Cost (1x)
                    [
                        'id' => 'claude-3.5-sonnet',
                        'name' => 'Claude 3.5 Sonnet',
                        'provider' => 'Anthropic',
                        'description' => 'Excellent for code generation',
                        'speed' => 'fast',
                        'quality' => 'high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'claude-sonnet-4',
                        'name' => 'Claude Sonnet 4',
                        'provider' => 'Anthropic',
                        'description' => 'Latest Sonnet generation',
                        'speed' => 'fast',
                        'quality' => 'high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'claude-sonnet-4.5',
                        'name' => 'Claude Sonnet 4.5',
                        'provider' => 'Anthropic',
                        'description' => 'Enhanced Sonnet 4 model',
                        'speed' => 'fast',
                        'quality' => 'very-high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'gemini-2.5-pro',
                        'name' => 'Gemini 2.5 Pro',
                        'provider' => 'Google',
                        'description' => 'Advanced multimodal model',
                        'speed' => 'fast',
                        'quality' => 'very-high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'gpt-5',
                        'name' => 'GPT-5',
                        'provider' => 'OpenAI',
                        'description' => 'Next generation GPT model',
                        'speed' => 'fast',
                        'quality' => 'very-high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'gpt-5-codex',
                        'name' => 'GPT-5 Codex (Preview)',
                        'provider' => 'OpenAI',
                        'description' => 'Specialized for code generation',
                        'speed' => 'fast',
                        'quality' => 'very-high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    
                    // Additional Models (pricing may vary)
                    [
                        'id' => 'claude-3.7-sonnet',
                        'name' => 'Claude 3.7 Sonnet',
                        'provider' => 'Anthropic',
                        'description' => 'Enhanced Sonnet model',
                        'speed' => 'fast',
                        'quality' => 'high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'claude-3.7-sonnet-thought',
                        'name' => 'Claude 3.7 Sonnet (Thought)',
                        'provider' => 'Anthropic',
                        'description' => 'Sonnet with extended thinking',
                        'speed' => 'medium',
                        'quality' => 'very-high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'claude-opus-4',
                        'name' => 'Claude Opus 4',
                        'provider' => 'Anthropic',
                        'description' => 'Most capable Claude model',
                        'speed' => 'medium',
                        'quality' => 'very-high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'claude-opus-41',
                        'name' => 'Claude Opus 4.1',
                        'provider' => 'Anthropic',
                        'description' => 'Enhanced Opus model',
                        'speed' => 'medium',
                        'quality' => 'very-high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'gemini-2.0-flash-001',
                        'name' => 'Gemini 2.0 Flash',
                        'provider' => 'Google',
                        'description' => 'Fast multimodal model',
                        'speed' => 'very-fast',
                        'quality' => 'high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'o3',
                        'name' => 'O3',
                        'provider' => 'OpenAI',
                        'description' => 'Advanced reasoning model',
                        'speed' => 'medium',
                        'quality' => 'very-high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'o3-mini',
                        'name' => 'O3 Mini',
                        'provider' => 'OpenAI',
                        'description' => 'Faster reasoning model',
                        'speed' => 'fast',
                        'quality' => 'high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ],
                    [
                        'id' => 'o4-mini',
                        'name' => 'O4 Mini',
                        'provider' => 'OpenAI',
                        'description' => 'Latest mini reasoning model',
                        'speed' => 'fast',
                        'quality' => 'high',
                        'pricing' => 'paid',
                        'cost_multiplier' => '1x'
                    ]
                ]
            ]);
            break;

        case '/api/chat':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            $githubToken = $userManager->getGitHubToken((int)$user['user_id']);
            
            if (empty($githubToken)) {
                jsonResponse([
                    'error' => 'GitHub token not found',
                    'message' => 'Please authenticate with GitHub first'
                ], 401);
            }
            
            $input = getJsonInput();
            
            if (!isset($input['message'])) {
                jsonResponse(['error' => 'Missing required field: message'], 400);
            }
            
            $client = new AIAgentClient($githubToken);
            
            $conversationHistory = $input['history'] ?? [];
            $options = $input['options'] ?? [];
            
            // Get system context if provided (date + identity context from frontend)
            $systemContext = $input['system_context'] ?? '';
            
            // Check if user has PostgreSQL or MySQL connections and enhance message with schema context
            $enhancedMessage = $input['message'];
            $databaseContext = null;
            $postgresConnections = [];
            $mysqlConnections = [];
            $connections = [];

            try {
                $postgresConnections = $postgresManager->getUserConnections((int)$user['user_id']);
                $mysqlConnections = $mysqlManager->getUserConnections((int)$user['user_id']);
                $connections = array_merge($postgresConnections, $mysqlConnections);

                if (!empty($connections)) {
                    // Build database context from all connections
                    $schemaContext = "\n\n=== DATABASE ACCESS ===\n";
                    $schemaContext .= "You have access to PostgreSQL and MySQL databases. Use them ONLY when the user asks about data in these databases.\n";
                    $schemaContext .= "For all other questions (general knowledge, coding help, advice, etc.), respond normally without using the database.\n\n";
                    $schemaContext .= "Available databases:\n";

                    // Add PostgreSQL connections
                    foreach ($postgresConnections as $conn) {
                        try {
                            $schema = $postgresManager->getSchema((int)$conn['id'], (int)$user['user_id']);
                            $schemaText = $postgresManager->formatSchemaForAI($schema);

                            $schemaContext .= "\nConnection: {$conn['name']} (ID: {$conn['id']}, Type: PostgreSQL)\n";
                            $schemaContext .= "Database: {$conn['database']} on {$conn['host']}\n";
                            $schemaContext .= $schemaText . "\n";
                        } catch (\Exception $e) {
                            // Skip connections that fail
                            continue;
                        }
                    }

                    // Add MySQL connections
                    foreach ($mysqlConnections as $conn) {
                        try {
                            $schema = $mysqlManager->getSchema((int)$conn['id'], (int)$user['user_id']);
                            $schemaText = $mysqlManager->formatSchemaForAI($schema);

                            $schemaContext .= "\nConnection: {$conn['name']} (ID: {$conn['id']}, Type: MySQL)\n";
                            $schemaContext .= "Database: {$conn['database']} on {$conn['host']}\n";
                            $schemaContext .= $schemaText . "\n";
                        } catch (\Exception $e) {
                            // Skip connections that fail
                            continue;
                        }
                    }
                    
                    // Add instructions for the AI
                    $schemaContext .= "\n=== DATABASE QUERY RULES ===\n";
                    $schemaContext .= "ONLY generate SQL if the user is asking about data in the database tables above.\n";
                    $schemaContext .= "Examples that SHOULD generate SQL:\n";
                    $schemaContext .= "  - 'show me all customers'\n";
                    $schemaContext .= "  - 'what are the total sales?'\n";
                    $schemaContext .= "  - 'list orders from yesterday'\n";
                    $schemaContext .= "\nExamples that should NOT generate SQL:\n";
                    $schemaContext .= "  - 'hello' or 'hi'\n";
                    $schemaContext .= "  - 'who are you?'\n";
                    $schemaContext .= "  - 'find me best place to vacation'\n";
                    $schemaContext .= "  - 'help me write code'\n";
                    $schemaContext .= "\nIf generating SQL:\n";
                    $schemaContext .= "- Generate a SQL SELECT query in a ```sql code block\n";
                    $schemaContext .= "- Include -- connection_id: X as the first line inside the SQL block\n";
                    $schemaContext .= "- IMPORTANT: TO_CHAR only accepts 2 parameters (column, format)\n";
                    $schemaContext .= "- AFTER closing the SQL block (```), add EXACTLY ONE LINE with visualization intent:\n";
                    $schemaContext .= "\nVisualization intent options:\n";
                    $schemaContext .= "  VIZ:TABLE - for table view\n";
                    $schemaContext .= "  VIZ:CHART - for chart/graph view\n";
                    $schemaContext .= "  VIZ:MAP - for map view\n";
                    $schemaContext .= "\nResponse format example:\n";
                    $schemaContext .= "```sql\n";
                    $schemaContext .= "-- connection_id: 1\n";
                    $schemaContext .= "SELECT name, total_spent FROM customers ORDER BY total_spent DESC LIMIT 10;\n";
                    $schemaContext .= "```\n";
                    $schemaContext .= "VIZ:CHART\n";
                    $schemaContext .= "\n=== VISUALIZATION SELECTION RULES ===\n";
                    $schemaContext .= "Choose the BEST visualization based on:\n";
                    $schemaContext .= "\n1. USER EXPLICIT REQUEST (highest priority):\n";
                    $schemaContext .= "   - If user says 'using chart view', 'as chart', 'graph' → VIZ:CHART\n";
                    $schemaContext .= "   - If user says 'using table view', 'as table', 'list' → VIZ:TABLE\n";
                    $schemaContext .= "   - If user says 'using map view', 'on map', 'show locations' → VIZ:MAP\n";
                    $schemaContext .= "\n2. DATA STRUCTURE ANALYSIS (if no explicit request):\n";
                    $schemaContext .= "   \n";
                    $schemaContext .= "   Use VIZ:CHART when:\n";
                    $schemaContext .= "   - Query returns MULTIPLE rows to compare (NOT single value!)\n";
                    $schemaContext .= "   - Query has GROUP BY with multiple categories (comparing data)\n";
                    $schemaContext .= "   - Query has ORDER BY with LIMIT (top N results where N > 1)\n";
                    $schemaContext .= "   - User asks for 'trends', 'comparison', 'top', 'analysis'\n";
                    $schemaContext .= "   - User asks 'per bulan', 'per minggu', 'per kategori' (time series/categories)\n";
                    $schemaContext .= "   Examples: 'top 10 customers', 'penjualan per bulan', 'revenue comparison'\n";
                    $schemaContext .= "   \n";
                    $schemaContext .= "   Use VIZ:MAP when:\n";
                    $schemaContext .= "   - Query selects location columns (city, province, address, region)\n";
                    $schemaContext .= "   - User asks about 'locations', 'where', 'distribution', 'geography', 'sebaran'\n";
                    $schemaContext .= "   - Query focuses on geographic data\n";
                    $schemaContext .= "   Examples: 'where are customers located', 'customer distribution by city'\n";
                    $schemaContext .= "   \n";
                    $schemaContext .= "   Use VIZ:TABLE when:\n";
                    $schemaContext .= "   - Query returns SINGLE VALUE (total, sum, count without GROUP BY)\n";
                    $schemaContext .= "   - Query has only 1 expected result row (e.g., 'berapa total X?', 'total pengeluaran bulan Y')\n";
                    $schemaContext .= "   - Query selects ALL columns (SELECT *)\n";
                    $schemaContext .= "   - Query selects many columns (>5 columns)\n";
                    $schemaContext .= "   - Query mixes text, numbers, dates, and other types\n";
                    $schemaContext .= "   - User asks to 'show all', 'list all', 'display all data'\n";
                    $schemaContext .= "   - Data is not suitable for charts or maps\n";
                    $schemaContext .= "   Examples: 'total pengeluaran bulan april', 'berapa pendapatan?', 'show all customer data'\n";
                    $schemaContext .= "\n3. EXAMPLES:\n";
                    $schemaContext .= "   'total pengeluaran bulan april' → SELECT SUM(amount) → VIZ:TABLE (single value!)\n";
                    $schemaContext .= "   'berapa pendapatan hari ini?' → SELECT SUM(amount) → VIZ:TABLE (single value!)\n";
                    $schemaContext .= "   'penjualan per bulan' → SELECT month, SUM(amount) GROUP BY month → VIZ:CHART (multiple rows)\n";
                    $schemaContext .= "   'top 10 customers by spending' → SELECT name, total_spent LIMIT 10 → VIZ:CHART (multiple rows)\n";
                    $schemaContext .= "   'customer locations' → SELECT name, city → VIZ:MAP\n";
                    $schemaContext .= "   'show all customer data' → SELECT * → VIZ:TABLE (many columns)\n";
                    $schemaContext .= "\nDO NOT add any explanations or other text after VIZ: marker.\n";

                    // Business rules untuk konteks domain spesifik
                    $schemaContext .= "\n=== BUSINESS RULES (GRANDMA LAUNDRY) ===\n";
                    $schemaContext .= "Definisi untuk database laundry:\n";
                    $schemaContext .= "- 'Total penjualan' atau 'pendapatan' = SUM(payments.received_amount) - uang yang sudah diterima\n";
                    $schemaContext .= "- 'Total order' atau 'nilai order' = SUM(orders.total) - nilai semua order termasuk belum bayar\n";
                    $schemaContext .= "- 'Outstanding' atau 'piutang' = orders.total - SUM(payments) per order\n";
                    $schemaContext .= "- 'Order lunas' = order dimana SUM(payments) >= orders.total\n";
                    $schemaContext .= "- 'Order belum lunas' = order dimana SUM(payments) < orders.total\n";
                    $schemaContext .= "- Satu order bisa punya beberapa pembayaran (cicil)\n";
                    $schemaContext .= "- JOIN orders dengan payments menggunakan: payments.order_id = orders.id\n\n";

                    // Profit/Loss rules
                    $schemaContext .= "=== PROFIT/LOSS RULES ===\n";
                    $schemaContext .= "- 'Laba' atau 'untung' atau 'profit' = SUM(payments.received_amount) - SUM(expenses.amount)\n";
                    $schemaContext .= "- 'Rugi' atau 'loss' = ketika SUM(expenses.amount) > SUM(payments.received_amount)\n";
                    $schemaContext .= "- 'Total pengeluaran' atau 'total biaya' = SUM(expenses.amount)\n";
                    $schemaContext .= "- Untuk laporan laba rugi per periode, filter by date range pada kedua tabel\n";
                    $schemaContext .= "- JOIN tidak diperlukan antara payments dan expenses, hitung terpisah lalu kurangi\n";
                    $schemaContext .= "- Contoh query laba: SELECT (SELECT COALESCE(SUM(received_amount),0) FROM payments) - (SELECT COALESCE(SUM(amount),0) FROM expenses) AS laba\n\n";

                    $schemaContext .= "\nIf NOT a database question:\n";
                    $schemaContext .= "- Respond normally as a helpful AI assistant\n";
                    $schemaContext .= "- Do NOT generate SQL queries\n";
                    $schemaContext .= "- Do NOT mention the database unless relevant\n\n";
                    
                    // Prepend schema context to the message
                    $enhancedMessage = $schemaContext . "User: " . $input['message'];
                    $databaseContext = $schemaContext;
                }
            } catch (\Exception $e) {
                // If schema retrieval fails, continue without it
            }
            
            // Log if we're sending visualization instructions
            if ($databaseContext !== null) {
                error_log("=== SENDING AI REQUEST WITH VIZ INSTRUCTIONS ===");
                error_log("User message: " . $input['message']);
                $hasVizInstructions = strpos($enhancedMessage, 'VIZ:') !== false;
                error_log("Contains VIZ instructions: " . ($hasVizInstructions ? 'YES' : 'NO'));
            }
            
            // Add system context (date + identity) from frontend to enhanced message
            if (!empty($systemContext)) {
                $enhancedMessage .= $systemContext;
            }
            
            $response = $client->sendMessage(
                $enhancedMessage,
                $conversationHistory,
                $options
            );
            
            // Post-process response to remove explanations when database context is present
            // But keep VIZ: marker for intent extraction (will be removed later)
            if ($databaseContext !== null && isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
                
                // Log raw AI response for debugging
                error_log("=== RAW AI RESPONSE ===");
                error_log($content);
                error_log("=== END RAW AI RESPONSE ===");
                
                // If response contains SQL query, keep SQL block and VIZ marker only
                if (preg_match('/```sql\s*\n?(.*?)\n?```/s', $content, $matches)) {
                    $cleanContent = '```sql' . "\n" . trim($matches[1]) . "\n" . '```';
                    
                    // Preserve VIZ: marker if present
                    if (preg_match('/VIZ:(TABLE|CHART|MAP)/i', $content, $vizMatches)) {
                        $cleanContent .= "\n" . $vizMatches[0];
                    }
                    
                    $response['choices'][0]['message']['content'] = $cleanContent;
                }
            }
            
            // Initialize user intent flags (will be set by AI if SQL query is generated)
            $wantsChart = false;
            $wantsMap = false;
            $wantsTable = false;
            
            // Check if response contains SQL query and execute it
            $queryResults = null;
            $connectionId = null;
            $sqlBlock = null;
            
            if (isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
                
                file_put_contents('/tmp/query-debug.txt', "Content length: " . strlen($content) . "\n", FILE_APPEND);
                error_log("Response content: " . substr($content, 0, 200));
                
                // Extract SQL query from code block
                if (preg_match('/```sql\s*\n?(.*?)\n?```/s', $content, $matches)) {
                    $sqlBlock = trim($matches[1]);
                    file_put_contents('/tmp/query-debug.txt', "Found SQL block\n", FILE_APPEND);
                    error_log("Found SQL block: " . $sqlBlock);
                    
                    // Extract visualization intent (format: VIZ:TABLE, VIZ:CHART, VIZ:MAP)
                    error_log("Checking for VIZ marker in content...");
                    error_log("Content for VIZ check: " . $content);
                    
                    if (preg_match('/VIZ:(TABLE|CHART|MAP)/i', $content, $vizMatches)) {
                        $vizIntent = strtoupper(trim($vizMatches[1]));
                        error_log("✓ AI detected visualization intent: VIZ:" . $vizIntent);
                        
                        // Set intent flags based on AI's decision
                        if ($vizIntent === 'TABLE') {
                            $wantsTable = true;
                            $wantsChart = false;
                            $wantsMap = false;
                            error_log("Set wants_table = true");
                        } elseif ($vizIntent === 'CHART') {
                            $wantsChart = true;
                            $wantsTable = false;
                            $wantsMap = false;
                            error_log("Set wants_chart = true");
                        } elseif ($vizIntent === 'MAP') {
                            $wantsMap = true;
                            $wantsTable = false;
                            $wantsChart = false;
                            error_log("Set wants_map = true");
                        }
                    } else {
                        // Default to table if no intent specified
                        error_log("✗ No VIZ: marker found in AI response, defaulting to table");
                        $wantsTable = true;
                        $wantsChart = false;
                        $wantsMap = false;
                    }
                    
                    // Extract connection_id from comment (can be anywhere in the SQL)
                    if (preg_match('/--\s*connection_id:\s*(\d+)/i', $sqlBlock, $connMatches)) {
                        $connectionId = (int)$connMatches[1];
                        file_put_contents('/tmp/query-debug.txt', "Found connection_id: {$connectionId}\n", FILE_APPEND);
                    } else {
                        file_put_contents('/tmp/query-debug.txt', "No connection_id found in SQL\n", FILE_APPEND);
                    }
                    
                    // Remove connection_id comment from SQL
                    $sqlQuery = preg_replace('/--\s*connection_id:\s*\d+\s*/i', '', $sqlBlock);
                    $sqlQuery = trim($sqlQuery);
                    
                    error_log("Extracted SQL query: " . $sqlQuery);
                    error_log("Connection ID from comment: " . ($connectionId ?? 'null'));
                    error_log("Available connections: " . count($connections));
                    
                    // If no connection ID specified, use the first connection
                    if ($connectionId === null && !empty($connections)) {
                        $connectionId = (int)$connections[0]['id'];
                        file_put_contents('/tmp/query-debug.txt', "Using first connection ID: {$connectionId}\n", FILE_APPEND);
                        error_log("Using first connection ID: " . $connectionId);
                    }
                    
                    file_put_contents('/tmp/query-debug.txt', "Before if check - connectionId: " . var_export($connectionId, true) . "\n", FILE_APPEND);
                    
                    if ($connectionId) {
                        file_put_contents('/tmp/query-debug.txt', "Inside if block! Connection ID: {$connectionId}\nSQL: {$sqlQuery}\n", FILE_APPEND);
                        error_log("Starting query execution with connection ID: " . $connectionId);
                        $maxRetries = 3;
                        $retryCount = 0;
                        $lastError = null;
                        $queryFixed = false;
                        
                        while ($retryCount < $maxRetries) {
                            try {
                                file_put_contents('/tmp/query-debug.txt', "Attempting query execution...\n", FILE_APPEND);

                                // Determine connection type and use appropriate manager
                                $isPostgres = false;
                                $isMySQL = false;

                                foreach ($postgresConnections as $conn) {
                                    if ((int)$conn['id'] === $connectionId) {
                                        $isPostgres = true;
                                        break;
                                    }
                                }

                                if (!$isPostgres) {
                                    foreach ($mysqlConnections as $conn) {
                                        if ((int)$conn['id'] === $connectionId) {
                                            $isMySQL = true;
                                            break;
                                        }
                                    }
                                }

                                // Execute query with appropriate manager
                                if ($isPostgres) {
                                    $queryResults = $postgresManager->executeQuery(
                                        $connectionId,
                                        (int)$user['user_id'],
                                        $sqlQuery
                                    );
                                } elseif ($isMySQL) {
                                    $queryResults = $mysqlManager->executeQuery(
                                        $connectionId,
                                        (int)$user['user_id'],
                                        $sqlQuery
                                    );
                                } else {
                                    throw new \RuntimeException('Connection not found');
                                }
                                
                                file_put_contents('/tmp/query-debug.txt', "Query SUCCESS! Rows: " . $queryResults['count'] . "\n", FILE_APPEND);
                                
                                // Query succeeded - update response with working query
                                if ($queryFixed) {
                                    $response['choices'][0]['message']['content'] = "```sql\n{$sqlBlock}\n```";
                                }
                                
                                // Log success for debugging
                                error_log("Query executed successfully. Rows: " . $queryResults['count']);
                                break; // Success - exit retry loop
                            } catch (\Exception $e) {
                                file_put_contents('/tmp/query-debug.txt', "Query FAILED: " . $e->getMessage() . "\n", FILE_APPEND);
                                $lastError = $e->getMessage();
                                $retryCount++;
                                
                                // Log error for debugging
                                error_log("Query failed (attempt {$retryCount}): " . $lastError);
                                
                                // Attempt to fix the query automatically (before checking max retries)
                                $dbType = $isPostgres ? 'PostgreSQL' : 'MySQL';
                                $fixPrompt = "Fix this {$dbType} query error. Return ONLY the corrected SQL query in a ```sql code block.\n\n";
                                $fixPrompt .= "Error: {$lastError}\n\n";
                                $fixPrompt .= "Failed Query:\n```sql\n{$sqlQuery}\n```\n\n";
                                $fixPrompt .= "Requirements:\n";
                                $fixPrompt .= "- Include -- connection_id: {$connectionId} as first line\n";
                                $fixPrompt .= "- Fix the SQL syntax error\n";
                                if ($isPostgres) {
                                    $fixPrompt .= "- IMPORTANT: TO_CHAR only accepts 2 parameters. Remove any locale/third parameter\n";
                                    $fixPrompt .= "- Use TO_CHAR(column, 'format') not TO_CHAR(column, 'format', 'locale')\n";
                                }
                                $fixPrompt .= "- Return ONLY the SQL code block, no explanations\n";
                                
                                try {
                                    file_put_contents('/tmp/query-debug.txt', "Attempting AI fix...\n", FILE_APPEND);
                                    error_log("Attempting to fix query with AI...");
                                    $fixResponse = $client->sendMessage($fixPrompt, [], $options);
                                    file_put_contents('/tmp/query-debug.txt', "AI fix response received\n", FILE_APPEND);
                                    
                                    if (isset($fixResponse['choices'][0]['message']['content'])) {
                                        $fixedContent = $fixResponse['choices'][0]['message']['content'];
                                        file_put_contents('/tmp/query-debug.txt', "AI returned: " . substr($fixedContent, 0, 200) . "\n", FILE_APPEND);
                                        
                                        if (preg_match('/```sql\s*\n?(.*?)\n?```/s', $fixedContent, $fixMatches)) {
                                            $fixedSqlBlock = trim($fixMatches[1]);
                                            $sqlQuery = preg_replace('/--\s*connection_id:\s*\d+\s*/i', '', $fixedSqlBlock);
                                            $sqlQuery = trim($sqlQuery);
                                            
                                            file_put_contents('/tmp/query-debug.txt', "Fixed query extracted: " . $sqlQuery . "\n", FILE_APPEND);
                                            error_log("Fixed query: " . $sqlQuery);
                                            
                                            // Update the SQL block for response
                                            $sqlBlock = $fixedSqlBlock;
                                            $queryFixed = true;
                                        } else {
                                            file_put_contents('/tmp/query-debug.txt', "Could not extract SQL from AI response\n", FILE_APPEND);
                                            error_log("Could not extract fixed SQL from AI response");
                                            // Continue to next retry
                                        }
                                    } else {
                                        file_put_contents('/tmp/query-debug.txt', "AI fix response has no content\n", FILE_APPEND);
                                        error_log("AI fix response has no content");
                                        // Continue to next retry
                                    }
                                } catch (\Exception $fixException) {
                                    error_log("Fix attempt exception: " . $fixException->getMessage());
                                    // Continue to next retry
                                }
                                
                                // Check if we've exhausted all retries
                                if ($retryCount >= $maxRetries) {
                                    error_log("Max retries reached. Query failed permanently.");
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            
            // Geocode query results ONLY if user asks for map/location visualization
            error_log('=== GEOCODING CHECK ===');
            error_log('Query results is null: ' . ($queryResults === null ? 'YES' : 'NO'));
            error_log('User message: ' . $input['message']);
            error_log('AI determined intent - Chart: ' . ($wantsChart ? 'YES' : 'NO') . ', Map: ' . ($wantsMap ? 'YES' : 'NO') . ', Table: ' . ($wantsTable ? 'YES' : 'NO'));
            
            if ($queryResults !== null && $wantsMap && !$wantsTable) {
                error_log('Query results rows: ' . count($queryResults['rows'] ?? []));
                error_log('Has location data: ' . (Geocoder::hasLocationData($queryResults['rows'] ?? []) ? 'YES' : 'NO'));
                
                if (Geocoder::hasLocationData($queryResults['rows'] ?? [])) {
                    error_log('GEOCODING NOW...');
                    $queryResults = Geocoder::geocodeResults($queryResults);
                    error_log('GEOCODING COMPLETE');
                } else {
                    error_log('NO LOCATION DATA - SKIPPING GEOCODING');
                }
            } else {
                error_log('SKIPPING GEOCODING - User does not want map visualization');
            }
            error_log('=== GEOCODING CHECK END ===');
            
            // Save to database (with geocoded coordinates)
            try {
                // Calculate response time
                $responseTime = null;
                if (isset($input['start_time'])) {
                    $responseTime = round((microtime(true) * 1000 - $input['start_time']) / 1000, 2);
                }
                
                // Get or create conversation for this user
                $conversationId = null;
                if (isset($input['conversation_id']) && $input['conversation_id']) {
                    $conversationId = (int)$input['conversation_id'];
                } else {
                    // Create new conversation
                    $conversationId = $chatHistory->createConversation(
                        (int)$user['user_id'],
                        'Chat ' . date('Y-m-d H:i:s')
                    );
                }
                
                // Save user message
                $chatHistory->addMessage(
                    $conversationId,
                    'user',
                    $input['message']
                );
                
                // Save assistant message with metadata (including geocoded query results and user intent)
                $assistantContent = $response['choices'][0]['message']['content'] ?? '';
                
                // Remove VIZ: marker from content before saving (it's only for backend processing)
                $assistantContent = preg_replace('/\s*VIZ:(TABLE|CHART|MAP)\s*/i', '', $assistantContent);
                
                $metadata = [
                    'model' => $response['model'] ?? $options['model'] ?? 'gpt-4o',
                    'temperature' => $options['temperature'] ?? 0.1,
                    'max_tokens' => $options['max_tokens'] ?? 4096,
                    'total_tokens' => $response['usage']['total_tokens'] ?? 0,
                    'prompt_tokens' => $response['usage']['prompt_tokens'] ?? 0,
                    'completion_tokens' => $response['usage']['completion_tokens'] ?? 0,
                    'response_time' => $responseTime,
                    'extra' => [
                        'database_context' => $databaseContext !== null,
                        'query_results' => $queryResults, // Now includes geocoded coordinates
                        'user_intent' => [
                            'wants_table' => $wantsTable,
                            'wants_map' => $wantsMap && !$wantsTable,
                            'wants_chart' => !$wantsTable && !$wantsMap
                        ]
                    ]
                ];
                
                $chatHistory->addMessage(
                    $conversationId,
                    'assistant',
                    $assistantContent,
                    $metadata
                );
            } catch (\Exception $e) {
                error_log("Failed to save chat history: " . $e->getMessage());
            }
            
            // Debug output
            $debugInfo = [
                'has_sql_block' => isset($matches) && !empty($matches),
                'connection_id' => $connectionId ?? null,
                'query_results_is_null' => $queryResults === null,
                'connections_count' => count($connections),
                'geocoded' => $queryResults !== null && isset($queryResults['rows'][0]['latitude'])
            ];
            
            // Add geocoding info to response
            $geocodingInfo = [
                'attempted' => $queryResults !== null,
                'had_location_data' => $queryResults !== null && Geocoder::hasLocationData($queryResults['rows'] ?? []),
                'has_coordinates' => $queryResults !== null && isset($queryResults['rows'][0]['latitude']),
                'row_count' => $queryResults !== null ? count($queryResults['rows'] ?? []) : 0
            ];
            
            // Add user intent flags for frontend
            $userIntent = [
                'wants_table' => $wantsTable,
                'wants_map' => $wantsMap && !$wantsTable,
                'wants_chart' => !$wantsTable && !$wantsMap
            ];
            
            // Remove VIZ: marker from response content before sending to frontend
            if (isset($response['choices'][0]['message']['content'])) {
                $response['choices'][0]['message']['content'] = preg_replace('/\s*VIZ:(TABLE|CHART|MAP)\s*/i', '', $response['choices'][0]['message']['content']);
            }
            
            jsonResponse([
                'success' => true,
                'response' => $response,
                'database_context' => $databaseContext !== null,
                'query_results' => $queryResults,
                'conversation_id' => $conversationId ?? null,
                'debug' => $debugInfo,
                'geocoding' => $geocodingInfo,
                'user_intent' => $userIntent
            ]);
            break;

        case '/api/conversations':
            if ($method !== 'GET') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            
            try {
                $conversations = $chatHistory->getUserConversations((int)$user['user_id']);
                jsonResponse([
                    'success' => true,
                    'conversations' => $conversations
                ]);
            } catch (\Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            break;
        
        case '/api/conversation/messages':
            if ($method !== 'GET') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            
            if (!isset($_GET['conversation_id'])) {
                jsonResponse(['error' => 'Missing conversation_id parameter'], 400);
            }
            
            try {
                $conversationId = (int)$_GET['conversation_id'];
                $messages = $chatHistory->getMessages($conversationId);
                
                jsonResponse([
                    'success' => true,
                    'messages' => $messages
                ]);
            } catch (\Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            break;
        
        case '/api/conversation/current':
            if ($method !== 'GET') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            
            try {
                // Check if user wants to start a new conversation
                $startNew = isset($_GET['new']) && $_GET['new'] === 'true';
                
                if ($startNew) {
                    // Create a new conversation
                    $conversationId = $chatHistory->createConversation(
                        (int)$user['user_id'],
                        'New Conversation - ' . date('Y-m-d H:i:s')
                    );
                    
                    jsonResponse([
                        'success' => true,
                        'conversation_id' => $conversationId,
                        'messages' => []
                    ]);
                } else {
                    // Get the most recent conversation
                    $conversations = $chatHistory->getUserConversations((int)$user['user_id']);

                    if (!empty($conversations)) {
                        $conversationId = (int)$conversations[0]['id'];
                        $messages = $chatHistory->getMessages($conversationId);

                        jsonResponse([
                            'success' => true,
                            'conversation_id' => $conversationId,
                            'messages' => $messages
                        ]);
                    } else {
                        jsonResponse([
                            'success' => true,
                            'conversation_id' => null,
                            'messages' => []
                        ]);
                    }
                }
            } catch (\Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            break;

        case '/api/complete':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            $githubToken = $userManager->getGitHubToken((int)$user['user_id']);
            
            if (empty($githubToken)) {
                jsonResponse([
                    'error' => 'GitHub token not found',
                    'message' => 'Please authenticate with GitHub first'
                ], 401);
            }
            
            $input = getJsonInput();
            
            if (!isset($input['prompt'])) {
                jsonResponse(['error' => 'Missing required field: prompt'], 400);
            }
            
            $client = new AIAgentClient($githubToken);
            $options = $input['options'] ?? [];
            
            $response = $client->complete($input['prompt'], $options);
            
            jsonResponse([
                'success' => true,
                'response' => $response
            ]);
            break;

        case '/api/auth/start':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            
            try {
                // Create GitHubDeviceFlow instance for current user
                $auth = new GitHubDeviceFlow($db, (int)$user['user_id']);
                $deviceFlow = $auth->initiateDeviceFlow();
                
                jsonResponse([
                    'success' => true,
                    'device_code' => $deviceFlow['device_code'],
                    'user_code' => $deviceFlow['user_code'],
                    'verification_uri' => $deviceFlow['verification_uri'],
                    'expires_in' => $deviceFlow['expires_in'],
                    'interval' => $deviceFlow['interval']
                ]);
            } catch (\Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            break;

        case '/api/auth/poll':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            
            $input = getJsonInput();
            
            if (!isset($input['device_code'])) {
                jsonResponse(['error' => 'Missing required field: device_code'], 400);
            }
            
            try {
                // Create GitHubDeviceFlow instance for current user
                $auth = new GitHubDeviceFlow($db, (int)$user['user_id']);
                $result = $auth->checkTokenStatus($input['device_code']);
                
                if ($result['status'] === 'success') {
                    jsonResponse([
                        'success' => true,
                        'authenticated' => true,
                        'message' => 'Authentication successful'
                    ]);
                } else {
                    jsonResponse([
                        'success' => false,
                        'authenticated' => false,
                        'error' => $result['error'] ?? 'authorization_pending'
                    ]);
                }
            } catch (\Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            break;

        case '/api/postgres/connections':
            $user = requireAuth();
            
            if ($method === 'GET') {
                // Get all connections for user
                $connections = $postgresManager->getUserConnections((int)$user['user_id']);
                jsonResponse([
                    'success' => true,
                    'connections' => $connections
                ]);
            } elseif ($method === 'POST') {
                // Add new connection
                $input = getJsonInput();
                
                $required = ['name', 'host', 'database', 'username', 'password'];
                foreach ($required as $field) {
                    if (!isset($input[$field])) {
                        jsonResponse(['error' => "Missing required field: $field"], 400);
                    }
                }
                
                try {
                    $connectionId = $postgresManager->addConnection(
                        (int)$user['user_id'],
                        $input['name'],
                        $input['host'],
                        $input['port'] ?? 5432,
                        $input['database'],
                        $input['username'],
                        $input['password']
                    );
                    
                    jsonResponse([
                        'success' => true,
                        'message' => 'Connection added successfully',
                        'connection_id' => $connectionId
                    ], 201);
                } catch (\Exception $e) {
                    jsonResponse([
                        'success' => false,
                        'error' => $e->getMessage()
                    ], 500);
                }
            } else {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            break;

        case '/api/postgres/connection':
            $user = requireAuth();
            
            if ($method === 'DELETE') {
                $input = getJsonInput();
                
                if (!isset($input['connection_id'])) {
                    jsonResponse(['error' => 'Missing required field: connection_id'], 400);
                }
                
                $success = $postgresManager->deleteConnection(
                    (int)$input['connection_id'],
                    (int)$user['user_id']
                );
                
                jsonResponse([
                    'success' => $success,
                    'message' => $success ? 'Connection deleted' : 'Connection not found'
                ]);
            } else {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            break;

        case '/api/postgres/test':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            $input = getJsonInput();
            
            if (!isset($input['connection_id'])) {
                jsonResponse(['error' => 'Missing required field: connection_id'], 400);
            }
            
            $result = $postgresManager->testConnection(
                (int)$input['connection_id'],
                (int)$user['user_id']
            );
            
            jsonResponse($result);
            break;

        case '/api/postgres/schema':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            $input = getJsonInput();
            
            if (!isset($input['connection_id'])) {
                jsonResponse(['error' => 'Missing required field: connection_id'], 400);
            }
            
            try {
                $schema = $postgresManager->getSchema(
                    (int)$input['connection_id'],
                    (int)$user['user_id']
                );
                
                jsonResponse([
                    'success' => true,
                    'schema' => $schema
                ]);
            } catch (\Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            break;

        case '/api/postgres/query':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            
            $user = requireAuth();
            $input = getJsonInput();
            
            if (!isset($input['connection_id']) || !isset($input['query'])) {
                jsonResponse(['error' => 'Missing required fields: connection_id, query'], 400);
            }
            
            try {
                $result = $postgresManager->executeQuery(
                    (int)$input['connection_id'],
                    (int)$user['user_id'],
                    $input['query']
                );
                
                jsonResponse($result);
            } catch (\Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            break;

        case '/api/mysql/connections':
            $user = requireAuth();

            if ($method === 'GET') {
                // Get all connections for user
                $connections = $mysqlManager->getUserConnections((int)$user['user_id']);
                jsonResponse([
                    'success' => true,
                    'connections' => $connections
                ]);
            } elseif ($method === 'POST') {
                // Add new connection
                $input = getJsonInput();

                $required = ['name', 'host', 'database', 'username', 'password'];
                foreach ($required as $field) {
                    if (!isset($input[$field])) {
                        jsonResponse(['error' => "Missing required field: $field"], 400);
                    }
                }

                try {
                    $connectionId = $mysqlManager->addConnection(
                        (int)$user['user_id'],
                        $input['name'],
                        $input['host'],
                        $input['port'] ?? 3306,
                        $input['database'],
                        $input['username'],
                        $input['password']
                    );

                    jsonResponse([
                        'success' => true,
                        'message' => 'Connection added successfully',
                        'connection_id' => $connectionId
                    ], 201);
                } catch (\Exception $e) {
                    jsonResponse([
                        'success' => false,
                        'error' => $e->getMessage()
                    ], 500);
                }
            } else {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            break;

        case '/api/mysql/connection':
            $user = requireAuth();

            if ($method === 'DELETE') {
                $input = getJsonInput();

                if (!isset($input['connection_id'])) {
                    jsonResponse(['error' => 'Missing required field: connection_id'], 400);
                }

                $success = $mysqlManager->deleteConnection(
                    (int)$input['connection_id'],
                    (int)$user['user_id']
                );

                jsonResponse([
                    'success' => $success,
                    'message' => $success ? 'Connection deleted' : 'Connection not found'
                ]);
            } else {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            break;

        case '/api/mysql/test':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }

            $user = requireAuth();
            $input = getJsonInput();

            if (!isset($input['connection_id'])) {
                jsonResponse(['error' => 'Missing required field: connection_id'], 400);
            }

            $result = $mysqlManager->testConnection(
                (int)$input['connection_id'],
                (int)$user['user_id']
            );

            jsonResponse($result);
            break;

        case '/api/mysql/schema':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }

            $user = requireAuth();
            $input = getJsonInput();

            if (!isset($input['connection_id'])) {
                jsonResponse(['error' => 'Missing required field: connection_id'], 400);
            }

            try {
                $schema = $mysqlManager->getSchema(
                    (int)$input['connection_id'],
                    (int)$user['user_id']
                );

                jsonResponse([
                    'success' => true,
                    'schema' => $schema
                ]);
            } catch (\Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            break;

        case '/api/mysql/query':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }

            $user = requireAuth();
            $input = getJsonInput();

            if (!isset($input['connection_id']) || !isset($input['query'])) {
                jsonResponse(['error' => 'Missing required fields: connection_id, query'], 400);
            }

            try {
                $result = $mysqlManager->executeQuery(
                    (int)$input['connection_id'],
                    (int)$user['user_id'],
                    $input['query']
                );

                jsonResponse($result);
            } catch (\Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            break;

        default:
            jsonResponse(['error' => 'Not found'], 404);
    }
} catch (\Exception $e) {
    jsonResponse([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}
