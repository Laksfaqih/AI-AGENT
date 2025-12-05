<?php

namespace PhpCopilot\Database;

/**
 * Database Migration class
 * Handles database schema creation and migrations
 */
class Migration
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Run all migrations
     */
    public function run(): void
    {
        echo "Running database migrations...\n";
        
        // Create migrations table if it doesn't exist
        $this->createMigrationsTable();
        
        // Run initial schema migration
        if (!$this->hasRun('001_initial_schema')) {
            echo "Running migration: 001_initial_schema\n";
            $this->createInitialSchema();
            $this->markAsExecuted('001_initial_schema', 'Create initial database schema');
            echo "✓ Migration 001_initial_schema completed\n";
        }
        
        // Run indexes migration
        if (!$this->hasRun('002_add_indexes')) {
            echo "Running migration: 002_add_indexes\n";
            $this->addIndexes();
            $this->markAsExecuted('002_add_indexes', 'Add database indexes for performance');
            echo "✓ Migration 002_add_indexes completed\n";
        }
        
        // Seed default data
        if (!$this->hasRun('003_seed_defaults')) {
            echo "Running migration: 003_seed_defaults\n";
            $this->seedDefaults();
            $this->markAsExecuted('003_seed_defaults', 'Seed default system settings');
            echo "✓ Migration 003_seed_defaults completed\n";
        }
        
        // Run file-based migrations from Migrations/ folder
        $this->runFileMigrations();
        
        echo "All migrations completed successfully!\n";
    }
    
    /**
     * Run migrations from files in Migrations/ folder
     */
    private function runFileMigrations(): void
    {
        $migrationsDir = __DIR__ . '/Migrations';
        
        if (!is_dir($migrationsDir)) {
            return;
        }
        
        $files = glob($migrationsDir . '/*.php');
        sort($files);
        
        foreach ($files as $file) {
            $filename = basename($file, '.php');
            
            if (!$this->hasRun($filename)) {
                echo "Running migration: $filename\n";
                
                $migration = require $file;
                
                if (isset($migration['up'])) {
                    $this->db->execute($migration['up']);
                    $description = $migration['description'] ?? 'File-based migration';
                    $this->markAsExecuted($filename, $description);
                    echo "✓ Migration $filename completed\n";
                }
            }
        }
    }

    /**
     * Create migrations tracking table
     */
    private function createMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                version TEXT UNIQUE NOT NULL,
                description TEXT,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $this->db->execute($sql);
    }

    /**
     * Check if a migration has been run
     */
    private function hasRun(string $version): bool
    {
        $result = $this->db->queryValue(
            'SELECT COUNT(*) FROM migrations WHERE version = :version',
            [':version' => $version]
        );
        
        return $result > 0;
    }

    /**
     * Mark a migration as executed
     */
    private function markAsExecuted(string $version, string $description): void
    {
        $this->db->insert('migrations', [
            'version' => $version,
            'description' => $description
        ]);
    }

    /**
     * Create initial database schema
     */
    private function createInitialSchema(): void
    {
        $this->db->beginTransaction();
        
        try {
            // Users table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    password_hash TEXT NOT NULL,
                    email TEXT UNIQUE,
                    github_token TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    last_login_at DATETIME,
                    is_active INTEGER DEFAULT 1
                )
            ");

            // User configurations table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS user_configs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    config_key TEXT NOT NULL,
                    config_value TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    UNIQUE(user_id, config_key)
                )
            ");

            // Conversations table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS conversations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    title TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    is_archived INTEGER DEFAULT 0,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");

            // Messages table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS messages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    conversation_id INTEGER NOT NULL,
                    role TEXT NOT NULL CHECK(role IN ('user', 'assistant', 'system')),
                    content TEXT NOT NULL,
                    model TEXT,
                    temperature REAL,
                    max_tokens INTEGER,
                    prompt_tokens INTEGER,
                    completion_tokens INTEGER,
                    total_tokens INTEGER,
                    response_time REAL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    metadata TEXT,
                    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
                )
            ");

            // Sessions table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS sessions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    session_token TEXT UNIQUE NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    ip_address TEXT,
                    user_agent TEXT,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");

            // System settings table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS system_settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key TEXT UNIQUE NOT NULL,
                    setting_value TEXT,
                    description TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Add database indexes for performance
     */
    private function addIndexes(): void
    {
        $indexes = [
            'CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)',
            'CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)',
            'CREATE INDEX IF NOT EXISTS idx_user_configs_user_id ON user_configs(user_id)',
            'CREATE INDEX IF NOT EXISTS idx_conversations_user_id ON conversations(user_id)',
            'CREATE INDEX IF NOT EXISTS idx_conversations_created_at ON conversations(created_at)',
            'CREATE INDEX IF NOT EXISTS idx_messages_conversation_id ON messages(conversation_id)',
            'CREATE INDEX IF NOT EXISTS idx_messages_created_at ON messages(created_at)',
            'CREATE INDEX IF NOT EXISTS idx_sessions_token ON sessions(session_token)',
            'CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id)',
            'CREATE INDEX IF NOT EXISTS idx_sessions_expires_at ON sessions(expires_at)',
        ];

        foreach ($indexes as $sql) {
            $this->db->execute($sql);
        }
    }

    /**
     * Seed default system settings
     */
    private function seedDefaults(): void
    {
        $defaults = [
            ['setting_key' => 'app_name', 'setting_value' => 'The Code AI Agent', 'description' => 'Application name'],
            ['setting_key' => 'app_version', 'setting_value' => '1.0.0', 'description' => 'Application version'],
            ['setting_key' => 'company_name', 'setting_value' => 'The Code Indonesia', 'description' => 'Company name'],
            ['setting_key' => 'company_website', 'setting_value' => 'https://thecode.id', 'description' => 'Company website'],
            ['setting_key' => 'default_model', 'setting_value' => 'gpt-4o', 'description' => 'Default AI model'],
            ['setting_key' => 'default_temperature', 'setting_value' => '0.1', 'description' => 'Default temperature'],
            ['setting_key' => 'default_max_tokens', 'setting_value' => '4096', 'description' => 'Default max tokens'],
            ['setting_key' => 'session_lifetime', 'setting_value' => '86400', 'description' => 'Session lifetime in seconds (24 hours)'],
            ['setting_key' => 'max_conversations_per_user', 'setting_value' => '100', 'description' => 'Maximum conversations per user'],
        ];

        foreach ($defaults as $setting) {
            try {
                $this->db->insert('system_settings', $setting);
            } catch (\Exception $e) {
                // Setting might already exist, skip
                continue;
            }
        }
    }

    /**
     * Reset database (drop all tables and recreate)
     */
    public function fresh(): void
    {
        echo "Resetting database...\n";
        
        $tables = [
            'messages',
            'conversations',
            'sessions',
            'user_configs',
            'users',
            'system_settings',
            'migrations'
        ];

        foreach ($tables as $table) {
            $this->db->execute("DROP TABLE IF EXISTS $table");
            echo "✓ Dropped table: $table\n";
        }

        echo "Running migrations...\n";
        $this->run();
    }

    /**
     * Get current migration version
     */
    public function getCurrentVersion(): ?string
    {
        if (!$this->db->tableExists('migrations')) {
            return null;
        }

        return $this->db->queryValue(
            'SELECT version FROM migrations ORDER BY id DESC LIMIT 1'
        );
    }

    /**
     * Get all executed migrations
     */
    public function getExecutedMigrations(): array
    {
        if (!$this->db->tableExists('migrations')) {
            return [];
        }

        return $this->db->query(
            'SELECT version, description, executed_at FROM migrations ORDER BY id ASC'
        );
    }
}
