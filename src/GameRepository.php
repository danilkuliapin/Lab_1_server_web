<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Доступ до ігор у SQLite.
 */
class GameRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM games')->fetchColumn();
    }

    public function all(?string $genre = null, ?string $search = null, ?bool $free = null): array
    {
        $sql = 'SELECT * FROM games WHERE 1=1';
        $params = [];

        if ($genre !== null && $genre !== '') {
            $sql .= ' AND LOWER(genre) = LOWER(:genre)';
            $params[':genre'] = $genre;
        }
        if ($search !== null && $search !== '') {
            $sql .= ' AND title LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }
        if ($free !== null) {
            $sql .= ' AND free_to_play = :free';
            $params[':free'] = $free ? 1 : 0;
        }
        $sql .= ' ORDER BY id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM games WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    public function genres(): array
    {
        return $this->pdo
            ->query('SELECT DISTINCT genre FROM games ORDER BY genre')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    private function hydrate(array $row): array
    {
        return [
            'id'           => (int) $row['id'],
            'title'        => $row['title'],
            'genre'        => $row['genre'],
            'developer'    => $row['developer'],
            'release_year' => (int) $row['release_year'],
            'platforms'    => json_decode($row['platforms'], true) ?: [],
            'free_to_play' => (bool) $row['free_to_play'],
            'description'  => $row['description'],
        ];
    }
}
