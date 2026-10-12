<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Seeder;

$pdo = Database::connect(__DIR__ . '/../data/games.db');
$count = Seeder::run($pdo, __DIR__ . '/../data/games.json');

echo "Готово: завантажено $count ігор у data/games.db\n";
