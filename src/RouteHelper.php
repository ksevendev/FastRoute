<?php

namespace FastRoute;

class RouteHelper
{
    protected static array $namedRoutes = [];

    public static function registerRoute(string $name, string $path)
    {
        self::$namedRoutes[$name] = $path;
    }

    public static function getRoute(string $name, array $params = []): ?string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new \Exception("Rota '{$name}' não encontrada.");
        }

        $url = self::$namedRoutes[$name];

        foreach ($params as $key => $value) {
            $url = preg_replace('/\{' . $key . '\}/', $value, $url);
        }

        return $url;
    }
}
