<?php

namespace PhpCopilot\Chat;

use PhpCopilot\Database\Database;

/**
 * ChatHistory class for managing conversations and messages
 */
class ChatHistory
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new conversation
     * 
     * @param int $userId User ID
     * @param string|null $title Conversation title
     * @return int Conversation ID
     */
    public function createConversation(int $userId, ?string $title = null): int
    {
        $title = $title ?? 'New Conversation';

        $conversationId = $this->db->insert('conversations', [
            'user_id' => $userId,
            'title' => $title,
        ]);

        return (int)$conversationId;
    }

    /**
     * Get a conversation by ID
     * 
     * @param int $conversationId Conversation ID
     * @return array|null Conversation data or null if not found
     */
    public function getConversation(int $conversationId): ?array
    {
        return $this->db->queryOne(
            'SELECT * FROM conversations WHERE id = :id',
            [':id' => $conversationId]
        );
    }

    /**
     * Get all conversations for a user
     * 
     * @param int $userId User ID
     * @param bool $includeArchived Include archived conversations
     * @return array Array of conversations
     */
    public function getUserConversations(int $userId, bool $includeArchived = false): array
    {
        $sql = 'SELECT c.*, 
                       (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id) as message_count,
                       (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_at
                FROM conversations c
                WHERE c.user_id = :user_id';

        if (!$includeArchived) {
            $sql .= ' AND c.is_archived = 0';
        }

        $sql .= ' ORDER BY c.updated_at DESC';

        return $this->db->query($sql, [':user_id' => $userId]);
    }

    /**
     * Add a message to a conversation
     * 
     * @param int $conversationId Conversation ID
     * @param string $role Message role (user, assistant, system)
     * @param string $content Message content
     * @param array $metadata Additional metadata (model, tokens, etc.)
     * @return int Message ID
     */
    public function addMessage(
        int $conversationId,
        string $role,
        string $content,
        array $metadata = []
    ): int {
        // Validate role
        if (!in_array($role, ['user', 'assistant', 'system'])) {
            throw new \InvalidArgumentException('Invalid message role');
        }

        // Generate timestamp with millisecond precision
        $now = microtime(true);
        $timestamp = date('Y-m-d H:i:s', (int)$now);
        $milliseconds = sprintf('%03d', ($now - floor($now)) * 1000);
        $timestampMs = $timestamp . '.' . $milliseconds;

        $data = [
            'conversation_id' => $conversationId,
            'role' => $role,
            'content' => $content,
            'created_at_ms' => $timestampMs,
        ];

        // Add optional metadata fields
        if (isset($metadata['model'])) {
            $data['model'] = $metadata['model'];
        }
        if (isset($metadata['temperature'])) {
            $data['temperature'] = $metadata['temperature'];
        }
        if (isset($metadata['max_tokens'])) {
            $data['max_tokens'] = $metadata['max_tokens'];
        }
        if (isset($metadata['prompt_tokens'])) {
            $data['prompt_tokens'] = $metadata['prompt_tokens'];
        }
        if (isset($metadata['completion_tokens'])) {
            $data['completion_tokens'] = $metadata['completion_tokens'];
        }
        if (isset($metadata['total_tokens'])) {
            $data['total_tokens'] = $metadata['total_tokens'];
        }
        if (isset($metadata['response_time'])) {
            $data['response_time'] = $metadata['response_time'];
        }
        if (isset($metadata['extra'])) {
            $data['metadata'] = json_encode($metadata['extra']);
        }

        $messageId = $this->db->insert('messages', $data);

        // Update conversation's updated_at timestamp
        $this->db->update(
            'conversations',
            ['updated_at' => date('Y-m-d H:i:s')],
            'id = :id',
            [':id' => $conversationId]
        );

        return (int)$messageId;
    }

    /**
     * Get messages for a conversation
     * 
     * @param int $conversationId Conversation ID
     * @param int $limit Maximum number of messages to return
     * @param int $offset Offset for pagination
     * @return array Array of messages
     */
    public function getMessages(int $conversationId, int $limit = 100, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT * FROM messages 
             WHERE conversation_id = :conversation_id 
             ORDER BY COALESCE(created_at_ms, created_at) ASC 
             LIMIT :limit OFFSET :offset',
            [
                ':conversation_id' => $conversationId,
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );
    }

    /**
     * Get recent messages for a conversation (for context)
     * 
     * @param int $conversationId Conversation ID
     * @param int $limit Number of recent messages
     * @return array Array of messages
     */
    public function getRecentMessages(int $conversationId, int $limit = 10): array
    {
        $messages = $this->db->query(
            'SELECT * FROM messages 
             WHERE conversation_id = :conversation_id 
             ORDER BY COALESCE(created_at_ms, created_at) DESC 
             LIMIT :limit',
            [
                ':conversation_id' => $conversationId,
                ':limit' => $limit
            ]
        );

        // Reverse to get chronological order
        return array_reverse($messages);
    }

    /**
     * Update conversation title
     * 
     * @param int $conversationId Conversation ID
     * @param string $title New title
     * @return bool Success
     */
    public function updateConversationTitle(int $conversationId, string $title): bool
    {
        $affected = $this->db->update(
            'conversations',
            [
                'title' => $title,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = :id',
            [':id' => $conversationId]
        );

        return $affected > 0;
    }

    /**
     * Archive a conversation
     * 
     * @param int $conversationId Conversation ID
     * @return bool Success
     */
    public function archiveConversation(int $conversationId): bool
    {
        $affected = $this->db->update(
            'conversations',
            [
                'is_archived' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = :id',
            [':id' => $conversationId]
        );

        return $affected > 0;
    }

    /**
     * Unarchive a conversation
     * 
     * @param int $conversationId Conversation ID
     * @return bool Success
     */
    public function unarchiveConversation(int $conversationId): bool
    {
        $affected = $this->db->update(
            'conversations',
            [
                'is_archived' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = :id',
            [':id' => $conversationId]
        );

        return $affected > 0;
    }

    /**
     * Delete a conversation and all its messages
     * 
     * @param int $conversationId Conversation ID
     * @return bool Success
     */
    public function deleteConversation(int $conversationId): bool
    {
        // Messages will be deleted automatically due to CASCADE
        $affected = $this->db->delete(
            'conversations',
            'id = :id',
            [':id' => $conversationId]
        );

        return $affected > 0;
    }

    /**
     * Export a conversation
     * 
     * @param int $conversationId Conversation ID
     * @return array Conversation data with messages
     */
    public function exportConversation(int $conversationId): array
    {
        $conversation = $this->getConversation($conversationId);
        
        if ($conversation === null) {
            throw new \RuntimeException('Conversation not found');
        }

        $messages = $this->getMessages($conversationId);

        return [
            'conversation' => $conversation,
            'messages' => $messages,
            'exported_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get conversation statistics
     * 
     * @param int $conversationId Conversation ID
     * @return array Statistics
     */
    public function getConversationStats(int $conversationId): array
    {
        $stats = $this->db->queryOne(
            'SELECT 
                COUNT(*) as total_messages,
                SUM(CASE WHEN role = "user" THEN 1 ELSE 0 END) as user_messages,
                SUM(CASE WHEN role = "assistant" THEN 1 ELSE 0 END) as assistant_messages,
                SUM(total_tokens) as total_tokens,
                AVG(response_time) as avg_response_time,
                MIN(created_at) as first_message_at,
                MAX(created_at) as last_message_at
             FROM messages
             WHERE conversation_id = :conversation_id',
            [':conversation_id' => $conversationId]
        );

        return $stats ?? [];
    }

    /**
     * Get user statistics
     * 
     * @param int $userId User ID
     * @return array Statistics
     */
    public function getUserStats(int $userId): array
    {
        $stats = $this->db->queryOne(
            'SELECT 
                COUNT(DISTINCT c.id) as total_conversations,
                COUNT(m.id) as total_messages,
                SUM(m.total_tokens) as total_tokens,
                AVG(m.response_time) as avg_response_time
             FROM conversations c
             LEFT JOIN messages m ON c.id = m.conversation_id
             WHERE c.user_id = :user_id',
            [':user_id' => $userId]
        );

        return $stats ?? [];
    }

    /**
     * Search messages
     * 
     * @param int $userId User ID
     * @param string $query Search query
     * @param int $limit Maximum results
     * @return array Array of messages with conversation info
     */
    public function searchMessages(int $userId, string $query, int $limit = 50): array
    {
        return $this->db->query(
            'SELECT m.*, c.title as conversation_title
             FROM messages m
             JOIN conversations c ON m.conversation_id = c.id
             WHERE c.user_id = :user_id
             AND m.content LIKE :query
             ORDER BY m.created_at DESC
             LIMIT :limit',
            [
                ':user_id' => $userId,
                ':query' => '%' . $query . '%',
                ':limit' => $limit
            ]
        );
    }

    /**
     * Delete old conversations for a user
     * 
     * @param int $userId User ID
     * @param int $keepCount Number of conversations to keep
     * @return int Number of conversations deleted
     */
    public function deleteOldConversations(int $userId, int $keepCount = 100): int
    {
        // Get IDs of conversations to delete
        $toDelete = $this->db->query(
            'SELECT id FROM conversations
             WHERE user_id = :user_id
             ORDER BY updated_at DESC
             LIMIT -1 OFFSET :offset',
            [
                ':user_id' => $userId,
                ':offset' => $keepCount
            ]
        );

        $count = 0;
        foreach ($toDelete as $conv) {
            if ($this->deleteConversation((int)$conv['id'])) {
                $count++;
            }
        }

        return $count;
    }
}
