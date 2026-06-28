<?php

declare(strict_types=1);

/**
 * Jednoduchý migračný nástroj v štýle Laravelu.
 *
 *   docker compose exec php php migrate.php           # spustí čakajúce migrácie
 *
 * Pripojenie k DB sa berie z environment premenných (DB_HOST, DB_NAME,
 * DB_USER, DB_PASSWORD) – tie sú nastavené v docker-compose / .env, takže
 * skript funguje rovnako na ľubovoľnom počítači.
 */

$command = $argv[1] ?? 'migrate';
$dir     = __DIR__ . '/migrations';

$pdo = connect();
ensureMigrationsTable($pdo);

$files = glob($dir . '/*.php') ?: [];
sort($files);

switch ($command) {
    case 'migrate':
        runMigrate($pdo, $files);
        break;
    default:
        fwrite(STDERR, "Neznámy príkaz: $command (použi: migrate | rollback | status)\n");
        exit(1);
}

function connect(): PDO
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $db   = getenv('DB_NAME') ?: '';
    $user = getenv('DB_USER') ?: '';
    $pass = getenv('DB_PASSWORD') ?: '';

    try {
        return new PDO(
            "mysql:host=$host;dbname=$db;charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        fwrite(STDERR, "❌ Nepodarilo sa pripojiť k DB: {$e->getMessage()}\n");
        exit(1);
    }
}

function ensureMigrationsTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS migrations (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration   VARCHAR(255) NOT NULL UNIQUE,
            batch       INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

/** @return string[] názvy už spustených migrácií */
function ranMigrations(PDO $pdo): array
{
    return $pdo->query("SELECT migration FROM migrations ORDER BY migration")
               ->fetchAll(PDO::FETCH_COLUMN);
}

function runMigrate(PDO $pdo, array $files): void
{
    $done    = ranMigrations($pdo);
    $batch   = (int) $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations")->fetchColumn();
    $pending = array_filter($files, fn($f) => !in_array(basename($f, '.php'), $done, true));

    if (!$pending) {
        echo "Nič na migráciu – databáza je aktuálna.\n";
        return;
    }

    $insert = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
    foreach ($pending as $file) {
        $name = basename($file, '.php');
        echo "→ migrujem: $name ... ";
        (require $file)->up($pdo);
        $insert->execute([$name, $batch]);
        echo "OK\n";
    }
    echo "Hotovo (batch $batch).\n";
}