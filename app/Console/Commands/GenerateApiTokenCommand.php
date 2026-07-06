<?php

namespace App\Console\Commands;

use App\Services\ApiTokenService;
use Illuminate\Console\Command;

class GenerateApiTokenCommand extends Command
{
    protected $signature = 'khf:generate-api-token';

    protected $description = 'Generate a new static Bearer token for the frontend API client';

    public function handle(ApiTokenService $apiTokenService): int
    {
        $token = $apiTokenService->generateFrontendToken();

        $this->components->info('Frontend API token generated. Store it in the frontend API_TOKEN env variable.');
        $this->line($token);

        return self::SUCCESS;
    }
}
