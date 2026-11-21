<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Connexion PDO centralisée (singleton paresseux).
 * Toutes les requêtes passent par des requêtes préparées.
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    private static function connect(): void
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset']
        );

        try {
            self::$instance = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE utf8mb4_unicode_ci",
            ]);
        } catch (PDOException $e) {
            error_log('[SUNUSCORE][DB] ' . $e->getMessage());
            throw new RuntimeException('Impossible de se connecter à la base de données.', 500, $e);
        }
    }

    /** Interdit le clone (singleton) */
    private function __clone() {}
    public function __wakeup(): void { throw new RuntimeException('Wakeup interdit.'); }
}