<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\SqliteStore;
use PDO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

final class DatabasePreviewController
{
    public function __construct(
        private readonly SqliteStore $store,
        private readonly KernelInterface $kernel,
    )
    {
    }

    #[Route('/database-preview', name: 'app_database_preview', methods: ['GET'])]
    public function __invoke(): Response
    {
        $pdo = $this->store->connection();
        $tables = $this->tableNames($pdo);

        $stats = [];
        $sections = [];

        foreach ($tables as $table) {
            $rowCount = (int) $pdo->query(sprintf('SELECT COUNT(*) FROM "%s"', $table))->fetchColumn();
            $rows = $pdo->query(sprintf('SELECT * FROM "%s"', $table))->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $stats[] = sprintf(
                '<div class="stat-card"><strong>%s</strong><span>%d ligne(s)</span></div>',
                $this->escape($table),
                $rowCount
            );
            $sections[] = $this->renderTableSection($table, $rows);
        }

        $html = sprintf(
            <<<'HTML'
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Apercu base de donnees</title>
    <style>
      :root {
        color-scheme: light;
        --bg: #f4efe6;
        --panel: #fffdf8;
        --line: #d7cec2;
        --text: #1f1a15;
        --muted: #6c655d;
        --accent: #35291e;
      }
      * { box-sizing: border-box; }
      body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: linear-gradient(180deg, #efe8de 0%%, #f7f2eb 100%%);
        color: var(--text);
      }
      .page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 32px 20px 60px;
      }
      .hero, .panel {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
      }
      .hero {
        padding: 28px;
        margin-bottom: 24px;
      }
      h1, h2 {
        margin: 0 0 12px;
      }
      p {
        margin: 0;
        line-height: 1.5;
      }
      .muted {
        color: var(--muted);
      }
      .actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 18px;
      }
      .actions a {
        color: white;
        background: var(--accent);
        text-decoration: none;
        padding: 10px 14px;
        border-radius: 12px;
      }
      .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin: 24px 0;
      }
      .stat-card {
        display: grid;
        gap: 6px;
        padding: 16px;
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 16px;
      }
      .section-list {
        display: grid;
        gap: 18px;
      }
      .panel {
        padding: 18px;
        overflow: hidden;
      }
      .table-wrap {
        overflow-x: auto;
        margin-top: 14px;
      }
      table {
        width: 100%%;
        border-collapse: collapse;
        font-size: 14px;
      }
      th, td {
        border: 1px solid var(--line);
        padding: 8px 10px;
        text-align: left;
        vertical-align: top;
      }
      th {
        background: #f1e8dc;
      }
      code {
        background: #f1e8dc;
        padding: 2px 6px;
        border-radius: 8px;
      }
    </style>
  </head>
  <body>
    <main class="page">
      <section class="hero">
        <h1>Apercu de la base de donnees</h1>
        <p class="muted">Cette page affiche les tables SQLite du projet pour la presentation. Elle permet de montrer rapidement que la base existe, qu elle est remplie, et qu elle est partageable.</p>
        <div class="actions">
          <a href="./">Retour au site</a>
          <a href="database-dump">Voir le dump SQL</a>
          <a href="database-download">Telecharger la base SQLite</a>
        </div>
      </section>

      <section class="stats">
        %s
      </section>

      <section class="section-list">
        %s
      </section>
    </main>
  </body>
</html>
HTML,
            implode('', $stats),
            implode('', $sections)
        );

        return new Response($html, Response::HTTP_OK, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    #[Route('/database-dump', name: 'app_database_dump', methods: ['GET'])]
    public function dump(): Response
    {
        $path = $this->kernel->getProjectDir().'/database/gamerdry_dump.sql';
        $content = file_get_contents($path);

        return new Response((string) $content, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    #[Route('/database-download', name: 'app_database_download', methods: ['GET'])]
    public function download(): Response
    {
        $path = $this->kernel->getProjectDir().'/database/gamerdry.sqlite3';
        $content = file_get_contents($path);

        return new Response((string) $content, Response::HTTP_OK, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="gamerdry.sqlite3"',
        ]);
    }

    /**
     * @return list<string>
     */
    private function tableNames(PDO $pdo): array
    {
        return array_values(array_filter(
            array_map(
                static fn (mixed $value): string => (string) $value,
                $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN) ?: []
            ),
            static fn (string $table): bool => !str_starts_with($table, 'sqlite_')
        ));
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function renderTableSection(string $table, array $rows): string
    {
        if ([] === $rows) {
            return sprintf(
                '<section class="panel"><h2>%s</h2><p class="muted">Table vide.</p></section>',
                $this->escape($table)
            );
        }

        $columns = array_keys($rows[0]);
        $head = implode('', array_map(
            fn (string $column): string => sprintf('<th>%s</th>', $this->escape($column)),
            $columns
        ));

        $body = implode('', array_map(function (array $row) use ($columns): string {
            $cells = implode('', array_map(function (string $column) use ($row): string {
                $value = $row[$column] ?? '';

                return sprintf('<td>%s</td>', $this->escape($this->stringify($value)));
            }, $columns));

            return sprintf('<tr>%s</tr>', $cells);
        }, $rows));

        return sprintf(
            '<section class="panel"><h2>%s</h2><p class="muted">%d ligne(s)</p><div class="table-wrap"><table><thead><tr>%s</tr></thead><tbody>%s</tbody></table></div></section>',
            $this->escape($table),
            count($rows),
            $head,
            $body
        );
    }

    private function stringify(mixed $value): string
    {
        if (null === $value) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
