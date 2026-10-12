<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Підключення до SQLite та створення схеми.
 */
class Database
{
    public static function connect(string $path): PDO
    {
        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $pdo->exec('
            CREATE TABLE IF NOT EXISTS games (
                id           INTEGER PRIMARY KEY,
                title        TEXT    NOT NULL,
                genre        TEXT    NOT NULL,
                developer    TEXT    NOT NULL,
                release_year INTEGER NOT NULL,
                platforms    TEXT    NOT NULL,
                free_to_play INTEGER NOT NULL DEFAULT 0,
                description  TEXT    NOT NULL
            )
        ');

        return $pdo;
    }
}
