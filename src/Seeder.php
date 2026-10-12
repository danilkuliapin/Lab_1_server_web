<?php

declare(strict_types=1);

namespace App;

use PDO;
use RuntimeException;

/**
 * Заповнює таблицю games даними з JSON-файлу.
 */
class Seeder
{
    public static function run(PDO $pdo, string $jsonFile): int
    {
        if (!is_file($jsonFile)) {
            throw new RuntimeException("Файл даних не знайдено: $jsonFile");
        }
        $games = json_decode((string) file_get_contents($jsonFile), true);
        if (!is_array($games)) {
            throw new RuntimeException('Некоректний JSON у файлі даних');
        }

        $stmt = $pdo->prepare('
            INSERT OR REPLACE INTO games
                (id, title, genre, developer, release_year, platforms, free_to_play, description)
            VALUES
                (:id, :title, :genre, :developer, :year, :platforms, :free, :description)
        ');

        $pdo->beginTransaction();
        foreach ($games as $g) {
            $stmt->execute([
                ':id'          => $g['id'],
                ':title'       => $g['title'],
                ':genre'       => $g['genre'],
                ':developer'   => $g['developer'],
                ':year'        => $g['release_year'],
                ':platforms'   => json_encode($g['platforms'], JSON_UNESCAPED_UNICODE),
                ':free'        => $g['free_to_play'] ? 1 : 0,
                ':description' => $g['description'],
            ]);
        }
        $pdo->commit();

        return count($games);
    }
}
