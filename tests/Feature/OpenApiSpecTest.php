<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

/**
 * @return list<string>
 */
function documentedPaths(): array
{
    $spec = Yaml::parseFile(base_path('docs/openapi.yaml'));

    return array_keys($spec['paths']);
}

/**
 * @return list<string>
 */
function registeredApiPaths(): array
{
    return collect(Route::getRoutes()->getRoutes())
        ->map(fn ($route): string => $route->uri())
        ->filter(fn (string $uri): bool => str_starts_with($uri, 'api/v1/'))
        ->reject(fn (string $uri): bool => str_contains($uri, '{locale}')) // locale-prefixed duplicates
        ->reject(fn (string $uri): bool => str_contains($uri, 'probe')) // test-only probes
        ->map(fn (string $uri): string => '/'.Str::after($uri, 'api/v1/'))
        ->unique()
        ->sort()
        ->values()
        ->all();
}

test('the openapi spec parses and describes the API', function () {
    $spec = Yaml::parseFile(base_path('docs/openapi.yaml'));

    expect($spec['openapi'])->toStartWith('3.')
        ->and($spec['paths'])->not->toBeEmpty()
        ->and($spec['components']['schemas'])->toHaveKeys(['NewsItem', 'ContactsData', 'ForumData', 'OkReference']);
});

test('every registered public api route is documented in the openapi spec', function () {
    $documented = documentedPaths();

    foreach (registeredApiPaths() as $path) {
        expect($documented)->toContain($path);
    }
});

test('every documented path maps to a real route (no stale entries)', function () {
    $registered = registeredApiPaths();

    foreach (documentedPaths() as $path) {
        expect($registered)->toContain($path);
    }
});

test('the postman collection and environment are valid json', function () {
    $collection = json_decode((string) file_get_contents(base_path('docs/khf-api.postman_collection.json')), true);
    $environment = json_decode((string) file_get_contents(base_path('docs/khf-api.postman_environment.json')), true);

    expect($collection)->toBeArray()
        ->and($collection['info']['name'] ?? '')->not->toBeEmpty()
        ->and($collection['item'])->not->toBeEmpty()
        ->and($environment['values'])->not->toBeEmpty();
});
