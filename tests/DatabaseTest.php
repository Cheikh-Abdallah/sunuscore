<?php
declare(strict_types=1);

/**
 * Test manuel de connexion PDO (Phase 1).
 * Exécution : php tests/DatabaseTest.php
 */

require dirname(__DIR__) . '/config/app.php';
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Database;

try {
    $pdo = Database::getInstance();
    $one = (int) $pdo->query('SELECT 1')->fetchColumn();
    $db  = $pdo->query('SELECT DATABASE()')->fetchColumn();

    echo "[OK] Connexion PDO établie.\n";
    echo "[OK] SELECT 1 => {$one}\n";
    echo "[OK] Base active : {$db}\n";

    $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
    echo "[OK] " . count($tables) . " tables trouvées :\n";
    foreach ($tables as $t) {
        echo "     - {$t}\n";
    }
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[KO] " . $e->getMessage() . "\n");
    exit(1);
}