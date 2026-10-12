<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\GameRepository;
use App\Response;
use App\Seeder;
use Psr\Http\Message\ResponseInterface as Res;
use Psr\Http\Message\ServerRequestInterface as Req;
use Slim\Exception\HttpException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

// --- База даних (SQLite) ---
$pdo = Database::connect(__DIR__ . '/../data/games.db');
$repo = new GameRepository($pdo);
if ($repo->count() === 0) {
    Seeder::run($pdo, __DIR__ . '/../data/games.json');
}

// --- Slim ---
$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

// Помилки віддаємо у форматі JSON
$errorMiddleware = $app->addErrorMiddleware(false, true, true);
$errorMiddleware->setDefaultErrorHandler(
    function (Req $request, Throwable $exception) use ($app): Res {
        $response = $app->getResponseFactory()->createResponse();

        if ($exception instanceof HttpNotFoundException) {
            return Response::error($response, 'Маршрут не знайдено', 404);
        }
        if ($exception instanceof HttpMethodNotAllowedException) {
            return Response::error($response, 'Метод не дозволено', 405);
        }
        if ($exception instanceof HttpException) {
            return Response::error($response, $exception->getMessage(), $exception->getCode());
        }
        return Response::error($response, 'Внутрішня помилка сервера', 500);
    }
);

// --- Маршрути ---
$app->get('/', function (Req $request, Res $response) {
    return Response::json($response, [
        'name' => 'Online Games API',
        'endpoints' => [
            'GET /api/games?genre=&search=&free=true|false',
            'GET /api/games/{id}',
            'GET /api/genres',
        ],
    ]);
});

$app->get('/api/games', function (Req $request, Res $response) use ($repo) {
    $q = $request->getQueryParams();
    $free = isset($q['free']) ? filter_var($q['free'], FILTER_VALIDATE_BOOLEAN) : null;
    $games = $repo->all($q['genre'] ?? null, $q['search'] ?? null, $free);

    return Response::json($response, ['count' => count($games), 'data' => $games]);
});

$app->get('/api/games/{id:[0-9]+}', function (Req $request, Res $response, array $args) use ($repo) {
    $game = $repo->find((int) $args['id']);
    if ($game === null) {
        return Response::error($response, 'Гру не знайдено', 404);
    }
    return Response::json($response, $game);
});

$app->get('/api/genres', function (Req $request, Res $response) use ($repo) {
    return Response::json($response, $repo->genres());
});

$app->run();
