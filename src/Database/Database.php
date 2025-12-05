<?php

namespace PhpCopilot\Database;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Database class for managing SQLite connections and operations
 */
class Database
{
    private ?PDO $connection = null;
    private string $dbPath;
    private bool $inTransaction = false;

    /**
     * Constructor
     * 
     * @param string $dbPath Path to SQLite database file
     */
    public function __construct(string $dbPath)
    {
        $this->dbPath = $dbPath;
        
        // Ensure storage directory exists
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $this->connect();
    }

    /**
     * Establish database connection
     */
    private function connect(): void
    {
        try {
            $this->connection = new PDO(
                'sqlite:' . $this->dbPath,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            
            // Enable foreign keys
            $this->connection->exec('PRAGMA foreign_keys = ON');
            
            // Enable WAL mode for better concurrency
            $this->connection->exec('PRAGMA journal_mode = WAL');
            
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get PDO connection instance
     * 
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        
        return $this->connection;
    }

    /**
     * Begin a transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        if ($this->inTransaction) {
            return false;
        }
        
        $result = $this->getConnection()->beginTransaction();
        $this->inTransaction = $result;
        
        return $result;
    }

    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        
        $result = $this->getConnection()->commit();
        $this->inTransaction = false;
        
        return $result;
    }

    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        
        $result = $this->getConnection()->rollBack();
        $this->inTransaction = false;
        
        return $result;
    }

    /**
     * Execute a SQL statement
     * 
     * @param string $sql SQL statement
     * @param array $params Parameters to bind
     * @return PDOStatement
     * @throws PDOException
     */
    public function execute(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new PDOException('Query execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute a query and return all results
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a query and return a single row
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array|null
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetch();
        
        return $result === false ? null : $result;
    }

    /**
     * Execute a query and return a single value
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return mixed
     */
    public function queryValue(string $sql, array $params = []): mixed
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchColumn();
    }

    /**
     * Insert a record and return the last insert ID
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return string Last insert ID
     */
    public function insert(string $table, array $data): string
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $params = [];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }
        
        $this->execute($sql, $params);
        
        return $this->lastInsertId();
    }

    /**
     * Update records
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setParts = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $setParts[] = $key . ' = :set_' . $key;
            $params[':set_' . $key] = $value;
        }
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $setParts),
            $where
        );
        
        $params = array_merge($params, $whereParams);
        
        $stmt = $this->execute($sql, $params);
        
        return $stmt->rowCount();
    }

    /**
     * Delete records
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = sprintf('DELETE FROM %s WHERE %s', $table, $where);
        $stmt = $this->execute($sql, $params);
        
        return $stmt->rowCount();
    }

    /**
     * Get the last insert ID
     * 
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Check if a table exists
     * 
     * @param string $tableName Table name
     * @return bool
     */
    public function tableExists(string $tableName): bool
    {
        $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name=:name";
        $result = $this->queryValue($sql, [':name' => $tableName]);
        
        return $result !== false;
    }

    /**
     * Close the database connection
     */
    public function close(): void
    {
        $this->connection = null;
        $this->inTransaction = false;
    }

    /**
     * Destructor - ensure connection is closed
     */
    public function __destruct()
    {
        $this->close();
    }
}
