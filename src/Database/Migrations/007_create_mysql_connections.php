<?php

return [
    'description' => 'Create MySQL connections table',
    'up' => "
        CREATE TABLE IF NOT EXISTS mysql_connections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            host TEXT NOT NULL,
            port INTEGER NOT NULL DEFAULT 3306,
            database TEXT NOT NULL,
            username TEXT NOT NULL,
            password TEXT NOT NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE INDEX IF NOT EXISTS idx_mysql_connections_user_id ON mysql_connections(user_id);
        CREATE INDEX IF NOT EXISTS idx_mysql_connections_active ON mysql_connections(is_active);
    ",
    'down' => "
        DROP INDEX IF EXISTS idx_mysql_connections_active;
        DROP INDEX IF EXISTS idx_mysql_connections_user_id;
        DROP TABLE IF EXISTS mysql_connections;
    "
];
