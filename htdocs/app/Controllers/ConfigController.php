<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\ConfigService;

class ConfigController
{
    protected ConfigService $configService;

    public function __construct(?ConfigService $configService = null)
    {
        $this->configService = $configService ?? new ConfigService();
    }

    /**
     * GET /api/v1/config/public
     */
    public function publicConfig(Request $request): void
    {
        $config = $this->configService->getPublicConfig();
        Response::ok($config);
    }
}
