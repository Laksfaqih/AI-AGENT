<?php

/**
 * Migration: Add millisecond precision timestamps to messages table
 * 
 * SQLite's CURRENT_TIMESTAMP only has second precision, which causes
 * messages sent within the same second to have the same timestamp.
 * This migration adds a created_at_ms column to store millisecond timestamps.
 */

return [
    'description' => 'Add millisecond precision timestamps to messages table',
    
    'up' => "
        -- Add created_at_ms column to store millisecond timestamps
        ALTER TABLE messages ADD COLUMN created_at_ms TEXT;
        
        -- Update existing records to use current created_at with .000 milliseconds
        UPDATE messages SET created_at_ms = created_at || '.000' WHERE created_at_ms IS NULL;
        
        -- Create index on created_at_ms for faster sorting
        CREATE INDEX IF NOT EXISTS idx_messages_created_at_ms ON messages(created_at_ms);
    ",
    
    'down' => "
        DROP INDEX IF EXISTS idx_messages_created_at_ms;
        -- Note: SQLite doesn't support DROP COLUMN, so we can't remove created_at_ms
        -- This is acceptable as the column will just be ignored if migration is rolled back
    "
];
