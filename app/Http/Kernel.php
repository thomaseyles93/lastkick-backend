<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use \App\Http\Middleware\ValidateApiKey;
use \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     */
    protected $middleware = [
        CheckForMaintenanceMode::class,
    ];

    /**
     * Route middleware groups.
     */
    protected $middlewareGroups = [
        'api' => [
            'throttle:api',
        ],
    ];

    /**
     * Route middleware.
     */
    protected $routeMiddleware = [
        'authenticate' => ValidateApiKey::class,
    ];
}
