<?php

namespace PhpCopilot\Database;

/**
 * MySQL Connection Manager
 * Manages MySQL database connections for users
 */
class MySQLConnection
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Add a new MySQL connection
     *
     * @param int $userId User ID
     * @param string $name Connection name
     * @param string $host Database host
     * @param int $port Database port
     * @param string $database Database name
     * @param string $username Database username
     * @param string $password Database password
     * @return int Connection ID
     */
    public function addConnection(
        int $userId,
        string $name,
        string $host,
        int $port,
        string $database,
        string $username,
        string $password
    ): int {
        // Encrypt password (basic encryption - use better encryption in production)
        $encryptedPassword = base64_encode($password);

        $connectionId = $this->db->insert('mysql_connections', [
            'user_id' => $userId,
            'name' => $name,
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $encryptedPassword,
        ]);

        return (int)$connectionId;
    }

    /**
     * Get all connections for a user
     *
     * @param int $userId User ID
     * @return array Array of connections
     */
    public function getUserConnections(int $userId): array
    {
        $connections = $this->db->query(
            'SELECT id, name, host, port, database, username, is_active, created_at, updated_at
             FROM mysql_connections
             WHERE user_id = :user_id
             ORDER BY name ASC',
            [':user_id' => $userId]
        );

        return $connections;
    }

    /**
     * Get a specific connection
     *
     * @param int $connectionId Connection ID
     * @param int $userId User ID (for security)
     * @return array|null Connection data
     */
    public function getConnection(int $connectionId, int $userId): ?array
    {
        return $this->db->queryOne(
            'SELECT * FROM mysql_connections
             WHERE id = :id AND user_id = :user_id',
            [':id' => $connectionId, ':user_id' => $userId]
        );
    }

    /**
     * Test a MySQL connection
     *
     * @param int $connectionId Connection ID
     * @param int $userId User ID
     * @return array Result with success status and message
     */
    public function testConnection(int $connectionId, int $userId): array
    {
        $connection = $this->getConnection($connectionId, $userId);

        if (!$connection) {
            return ['success' => false, 'message' => 'Connection not found'];
        }

        try {
            $pdo = $this->createPDO($connection);
            $pdo->query('SELECT 1');

            return [
                'success' => true,
                'message' => 'Connection successful'
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get database schema (tables and columns)
     *
     * @param int $connectionId Connection ID
     * @param int $userId User ID
     * @return array Schema information
     */
    public function getSchema(int $connectionId, int $userId): array
    {
        $connection = $this->getConnection($connectionId, $userId);

        if (!$connection) {
            throw new \RuntimeException('Connection not found');
        }

        try {
            $pdo = $this->createPDO($connection);

            // Get all tables
            $tablesQuery = "
                SELECT table_name
                FROM information_schema.tables
                WHERE table_schema = :database
                AND table_type = 'BASE TABLE'
                ORDER BY table_name
            ";

            $stmt = $pdo->prepare($tablesQuery);
            $stmt->execute([':database' => $connection['database']]);
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            $schema = [];

            // Get columns for each table
            foreach ($tables as $table) {
                $columnsQuery = "
                    SELECT
                        COLUMN_NAME as column_name,
                        DATA_TYPE as data_type,
                        IS_NULLABLE as is_nullable,
                        COLUMN_DEFAULT as column_default
                    FROM information_schema.columns
                    WHERE table_schema = :database
                    AND table_name = :table_name
                    ORDER BY ordinal_position
                ";

                $stmt = $pdo->prepare($columnsQuery);
                $stmt->execute([
                    ':database' => $connection['database'],
                    ':table_name' => $table
                ]);
                $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $schema[$table] = $columns;
            }

            return $schema;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Failed to get schema: ' . $e->getMessage());
        }
    }

    /**
     * Execute a query on a MySQL connection
     *
     * @param int $connectionId Connection ID
     * @param int $userId User ID
     * @param string $query SQL query
     * @return array Query results
     */
    public function executeQuery(int $connectionId, int $userId, string $query): array
    {
        $connection = $this->getConnection($connectionId, $userId);

        if (!$connection) {
            throw new \RuntimeException('Connection not found');
        }

        // Security: Only allow SELECT queries
        $query = trim($query);
        if (!preg_match('/^SELECT/i', $query)) {
            throw new \RuntimeException('Only SELECT queries are allowed');
        }

        try {
            $pdo = $this->createPDO($connection);
            $stmt = $pdo->query($query);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'rows' => $results,
                'count' => count($results)
            ];
        } catch (\PDOException $e) {
            // Throw exception so it can be caught and fixed automatically
            throw new \RuntimeException('Query error: ' . $e->getMessage());
        }
    }

    /**
     * Delete a connection
     *
     * @param int $connectionId Connection ID
     * @param int $userId User ID
     * @return bool Success
     */
    public function deleteConnection(int $connectionId, int $userId): bool
    {
        $affected = $this->db->delete(
            'mysql_connections',
            'id = :id AND user_id = :user_id',
            [':id' => $connectionId, ':user_id' => $userId]
        );

        return $affected > 0;
    }

    /**
     * Create PDO connection
     *
     * @param array $connection Connection data
     * @return \PDO PDO instance
     */
    private function createPDO(array $connection): \PDO
    {
        $password = base64_decode($connection['password']);

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $connection['host'],
            $connection['port'],
            $connection['database']
        );

        $pdo = new \PDO($dsn, $connection['username'], $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    }

    /**
     * Format schema for AI context
     *
     * @param array $schema Database schema
     * @return string Formatted schema description
     */
    public function formatSchemaForAI(array $schema): string
    {
        $description = "Database Schema:\n\n";

        foreach ($schema as $table => $columns) {
            $description .= "Table: $table\n";
            $description .= "Columns:\n";

            foreach ($columns as $column) {
                $nullable = $column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
                $description .= sprintf(
                    "  - %s (%s, %s)\n",
                    $column['column_name'],
                    $column['data_type'],
                    $nullable
                );
            }

            $description .= "\n";
        }

        return $description;
    }
}
