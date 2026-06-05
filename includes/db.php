<?php
require_once __DIR__ . '/helpers.php';

// use db.local.php on my laptop, environment variables when hosted online
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$useLocalDbFile = is_readable(__DIR__ . '/db.local.php')
    && (
        str_starts_with($httpHost, 'localhost')
        || str_starts_with($httpHost, '127.0.0.1')
        || str_ends_with($httpHost, '.local')
        || str_ends_with($httpHost, '.test')
    );

if ($useLocalDbFile) {
    require __DIR__ . '/db.local.php';
} else {
    $host     = env_or_default('DB_HOST');
    $dbname   = env_or_default('DB_NAME');
    $username = env_or_default('DB_USER');
    $password = env_or_default('DB_PASSWORD');

    if ($host === '' || $dbname === '' || $username === '') {
        die(json_encode([
            'error' => 'Database configuration is unavailable. Please try again later.',
        ]));
    }
}

// Azure MySQL needs SSL
$pdoOptions = [];
if (env_or_default('DB_SSL') === 'true') {
    $sslCa = env_or_default('DB_SSL_CA', __DIR__ . '/DigiCertGlobalRootG2.crt.pem');
    if (is_readable($sslCa)) {
        $pdoOptions[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
    }
    $pdoOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        $pdoOptions
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die(json_encode(['error' => 'Unable to connect to the database. Please try again later.']));
}

// make sure newer tables exist even if the DB was created earlier
require_once __DIR__ . '/migrate.php';
ensure_schema($pdo);
?>
