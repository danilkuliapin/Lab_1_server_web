<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface;

/**
 * Хелпер для JSON-відповідей поверх PSR-7.
 */
class Response
{
    public static function json(ResponseInterface $response, mixed $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Access-Control-Allow-Origin', '*');
    }

    public static function error(ResponseInterface $response, string $message, int $status): ResponseInterface
    {
        return self::json($response, ['error' => $message], $status);
    }
}
