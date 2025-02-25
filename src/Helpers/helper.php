<?php

use FastRoute\RouteHelper;

if (!function_exists('route_to')) {
    function route_to(string $name, array $params = []): string
    {
        return RouteHelper::getRoute($name, $params);
    }
}
