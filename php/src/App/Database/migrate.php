<?php

require_once __DIR__ . '/../vendor/autoload.php';

$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'fyh_db';
$user = getenv('DB_USER') ?: 'fyh_user';
$pass = getenv('DB_PASS') ?: 'fyhpassword';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations_log (
        id SERIAL PRIMARY KEY,
        migration_name VARCHAR(255) UNIQUE,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $files = glob(__DIR__ . '/migrations/*.sql');
    sort($files);

    foreach ($files as $file) {
        $name = basename($file);

        $stmt = $pdo->prepare("SELECT id FROM migrations_log WHERE migration_name = ?");
        $stmt->execute([$name]);

        if (!$stmt->fetch()) {
            echo "🚀 Exécution de $name...\n";
            $sql = file_get_contents($file);
            $pdo->exec($sql);

            $pdo->prepare("INSERT INTO migrations_log (migration_name) VALUES (?)")->execute([$name]);
        }
    }
    echo "Base de données à jour !\n";

} catch (Exception $e) {
    die("❌ Erreur : " . $e->getMessage() . "\n");
}