<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing MySQL connection...\n\n";

$host = '127.0.0.1';
$port = '3306';
$db = 'jericho_wordpress';
$user = 'wp_user';
$pass = 'wp_password';

echo "Host: $host:$port\n";
echo "Database: $db\n";
echo "User: $user\n\n";

// Try mysqli
echo "Testing mysqli...\n";
$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_error) {
    echo "FAILED: " . $mysqli->connect_error . "\n\n";
} else {
    echo "SUCCESS: Connected via mysqli\n";
    echo "Server info: " . $mysqli->server_info . "\n\n";
    $mysqli->close();
}

// Try PDO
echo "Testing PDO...\n";
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    echo "SUCCESS: Connected via PDO\n";
    echo "PDO driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n\n";
}

echo "PHP MySQL modules:\n";
print_r(get_loaded_extensions());
