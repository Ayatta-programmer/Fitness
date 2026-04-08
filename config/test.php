<?php
header('Content-Type: text/plain');

echo "PHP SAPI: " . PHP_SAPI . PHP_EOL;
echo "PHP Version: " . PHP_VERSION . PHP_EOL;
echo "Loaded php.ini: " . (php_ini_loaded_file() ?: 'none') . PHP_EOL;
echo "PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . PHP_EOL;

try {
    require_once __DIR__ . '/database.php';
    echo "Database connection: OK" . PHP_EOL;
} catch (Throwable $e) {
    echo "Database connection: FAILED" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
}
