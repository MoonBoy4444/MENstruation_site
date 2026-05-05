<?php

declare(strict_types=1);

namespace App\Infrastructure;

use PDO;
use RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;

final class SqliteStore
{
    private const REQUIRED_TABLES = [
        'typeclient',
        'client',
        'adresse',
        'possede',
        'paiement',
        'typeproduits',
        'produits',
        'commande',
        'lignecommande',
        'livraison',
        'transactionpaiement',
        'avis',
    ];

    private const REQUIRED_PRODUCT_COLUMNS = [
        'CouleurProd',
        'GammeProd',
        'AbsorptionProd',
        'UsageProd',
        'PointsFortsProd',
        'BadgeProd',
    ];

    private readonly string $databasePath;
    private readonly string $schemaPath;
    private readonly string $seedPath;
    private ?PDO $connection = null;

    public function __construct(KernelInterface $kernel)
    {
        $projectDir = $kernel->getProjectDir();
        $this->databasePath = $projectDir.'/database/gamerdry.sqlite3';
        $this->schemaPath = $projectDir.'/database/schema.sql';
        $this->seedPath = $projectDir.'/database/seed.sql';
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $this->initializeDatabase();

        $pdo = new PDO('sqlite:'.$this->databasePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON;');

        return $this->connection = $pdo;
    }

    private function initializeDatabase(): void
    {
        $databaseDir = \dirname($this->databasePath);
        if (!is_dir($databaseDir) && !mkdir($databaseDir, 0777, true) && !is_dir($databaseDir)) {
            throw new RuntimeException('Impossible de creer le dossier de base de donnees.');
        }

        $mustCreateFreshDatabase = !file_exists($this->databasePath) || $this->mustRebuildDatabase();
        if ($mustCreateFreshDatabase && file_exists($this->databasePath) && !unlink($this->databasePath)) {
            throw new RuntimeException('Impossible de reinitialiser la base de donnees SQLite.');
        }

        $pdo = $this->createPdo();

        if ($mustCreateFreshDatabase) {
            $this->importSqlFile($pdo, $this->schemaPath, 'schema');
            $this->importSqlFile($pdo, $this->seedPath, 'jeu de donnees');
        }

        $this->normalizeLegacyData($pdo);
    }

    private function mustRebuildDatabase(): bool
    {
        try {
            $pdo = $this->createPdo();
            $tables = array_map(
                static fn (mixed $value): string => (string) $value,
                $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(PDO::FETCH_COLUMN) ?: []
            );

            if ([] !== array_diff(self::REQUIRED_TABLES, $tables)) {
                return true;
            }

            $columns = [];
            foreach ($pdo->query("PRAGMA table_info(produits)") as $row) {
                $columns[] = $row['name'];
            }

            return [] !== array_diff(self::REQUIRED_PRODUCT_COLUMNS, $columns);
        } catch (\Throwable) {
            return true;
        }
    }

    private function createPdo(): PDO
    {
        $pdo = new PDO('sqlite:'.$this->databasePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON;');

        return $pdo;
    }

    private function importSqlFile(PDO $pdo, string $path, string $label): void
    {
        $sql = file_get_contents($path);
        if (false === $sql) {
            throw new RuntimeException(sprintf('Impossible de lire le fichier SQL de %s.', $label));
        }

        $pdo->exec($sql);
    }

    private function normalizeLegacyData(PDO $pdo): void
    {
        $pdo->exec("UPDATE produits SET ImageProd = substr(ImageProd, 2) WHERE ImageProd LIKE '/assets/%'");
    }
}
