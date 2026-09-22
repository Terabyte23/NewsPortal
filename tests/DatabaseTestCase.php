<?php
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase {
    protected static $db;

    public static function setUpBeforeClass(): void {
        global $conn;
        require_once __DIR__ . '/../db.php';
        self::$db = $conn;

        // Ensure database schema and migrations
        if (function_exists('migrate_database_if_needed')) {
            migrate_database_if_needed(self::$db);
        }
    }

    protected function setUp(): void {
        self::$db->begin_transaction();
    }

    protected function tearDown(): void {
        self::$db->rollback();
    }
}