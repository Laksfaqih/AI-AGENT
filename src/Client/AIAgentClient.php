<?php

namespace PhpCopilot\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AIAgentClient
{
    private const COPILOT_TOKEN_URL = 'https://api.github.com/copilot_internal/v2/token';
    private const COMPLETIONS_ENDPOINT = '/chat/completions';

    private Client $client;
    private string $githubToken;
    private ?string $copilotToken = null;
    private ?string $copilotApiBase = null;

    public function __construct(string $githubToken)
    {
        $this->githubToken = $githubToken;
        $this->client = new Client([
            'timeout' => 30,
            'verify' => true,
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Force IPv4
                CURLOPT_DNS_CACHE_TIMEOUT => 0, // Disable DNS cache
            ]
        ]);
    }

    /**
     * Get Copilot token from GitHub
     */
    private function getCopilotToken(): string
    {
        if ($this->copilotToken !== null) {
            return $this->copilotToken;
        }

        try {
            $response = $this->client->get(self::COPILOT_TOKEN_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->githubToken,
                    'Accept' => 'application/json',
                    'User-Agent' => 'GitHubCopilotChat/1.0'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $this->copilotToken = $data['token'];

            // Get the correct API endpoint from the response
            if (isset($data['endpoints']['api'])) {
                $this->copilotApiBase = $data['endpoints']['api'];
            } else {
                // Fallback to default
                $this->copilotApiBase = 'https://api.individual.githubcopilot.com';
            }

            return $this->copilotToken;
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to get Copilot token: ' . $e->getMessage());
        }
    }

    /**
     * Send a chat completion request to Copilot
     */
    public function chat(array $messages, array $options = []): array
    {
        $copilotToken = $this->getCopilotToken();

        $payload = [
            'messages' => $messages,
            'model' => $options['model'] ?? 'gpt-4',
            'temperature' => $options['temperature'] ?? 0.1,
            'top_p' => $options['top_p'] ?? 1,
            'n' => $options['n'] ?? 1,
            'stream' => $options['stream'] ?? false,
            'max_tokens' => $options['max_tokens'] ?? 4096
        ];

        try {
            // Ensure we have the API base URL
            if ($this->copilotApiBase === null) {
                $this->copilotApiBase = 'https://api.individual.githubcopilot.com';
            }

            $response = $this->client->post($this->copilotApiBase . self::COMPLETIONS_ENDPOINT, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $copilotToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'GitHubCopilotChat/1.0',
                    'Editor-Version' => 'vscode/1.85.0',
                    'Editor-Plugin-Version' => 'copilot-chat/0.11.1',
                    'Openai-Organization' => 'github-copilot',
                    'Openai-Intent' => 'conversation-panel',
                    'VScode-SessionId' => $this->generateSessionId(),
                    'VScode-MachineId' => $this->generateMachineId()
                ],
                'json' => $payload
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Copilot API request failed: ' . $e->getMessage());
        }
    }

    /**
     * Send a code completion request
     */
    public function complete(string $prompt, array $options = []): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are GitHub Copilot, an AI coding assistant.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        return $this->chat($messages, $options);
    }

    /**
     * Send a chat message with conversation history
     */
    public function sendMessage(string $message, array $conversationHistory = [], array $options = []): array
    {
        $messages = [];

        // Add system message
        $systemPrompt = $options['system_prompt'] ??
            'You are Laks AI Agent, a helpful AI assistant powered by Faqih Nur Rahman. ' .
            'You can help with coding, general questions, advice, explanations, database queries, and data visualization. ' .
            'Be friendly, informative, and helpful.\n\n' .

            'IMPORTANT CHART VISUALIZATION GUIDELINES:\n' .
            '1. When querying databases for numerical data, structure queries to be chart-friendly:\n' .
            '   - First column should be labels (categories, dates, names)\n' .
            '   - Subsequent columns should be numeric values\n' .
            '   - Use meaningful column aliases (e.g., "Total Sales" instead of "sum_amount")\n' .
            '   - Limit results to reasonable amounts (10-50 rows for best visualization)\n\n' .

            '2. For time-series data:\n' .
            '   - Order by date/time ascending\n' .
            '   - Format dates clearly (e.g., "2024-01" or "Jan 2024")\n' .
            '   - Group by appropriate time periods (day, week, month, year)\n\n' .

            '3. For categorical data:\n' .
            '   - Use GROUP BY with aggregate functions (COUNT, SUM, AVG)\n' .
            '   - Order by the metric (DESC for top items)\n' .
            '   - Include meaningful category names\n\n' .

            '4. Suggested chart types based on data:\n' .
            '   - Bar Chart: Comparing categories, rankings, top N items\n' .
            '   - Line Chart: Time-series data, trends over time\n' .
            '   - Pie/Doughnut: Proportions, percentages, distributions (max 10 categories)\n\n' .

            '5. Example chart-friendly queries:\n' .
            '   - "SELECT category, COUNT(*) as count FROM products GROUP BY category ORDER BY count DESC LIMIT 10"\n' .
            '   - "SELECT DATE_FORMAT(created_at, \'%Y-%m\') as month, SUM(amount) as revenue FROM orders GROUP BY month"\n' .
            '   - "SELECT status, COUNT(*) as total FROM tickets GROUP BY status"\n\n' .

            'The frontend will automatically visualize query results with charts when data is suitable.';

        $messages[] = [
            'role' => 'system',
            'content' => $systemPrompt
        ];

        // Add conversation history
        foreach ($conversationHistory as $msg) {
            $messages[] = $msg;
        }

        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        return $this->chat($messages, $options);
    }

    /**
     * Generate a session ID
     */
    private function generateSessionId(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Generate a machine ID
     */
    private function generateMachineId(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Test the connection to Copilot API
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->complete('Hello', ['max_tokens' => 10]);
            return isset($response['choices']);
        } catch (\Exception) {
            return false;
        }
    }
}
