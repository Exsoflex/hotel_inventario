<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Start session for tests that need it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load config files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Define test constants
define('TEST_DB_NAME', 'hotel_inventario_prueba');
