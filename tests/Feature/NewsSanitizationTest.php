<?php

use App\Models\News;
use App\Services\BodySanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the sanitizer strips scripts and event handlers', function () {
    $clean = app(BodySanitizer::class)->clean('<p onclick="steal()">Салом</p><script>alert(1)</script>');

    expect($clean)->not->toContain('script')
        ->and($clean)->not->toContain('onclick')
        ->and($clean)->toContain('Салом');
});

test('the sanitizer keeps allowed formatting', function () {
    $clean = app(BodySanitizer::class)->clean(
        '<h2>Сарлавҳа</h2><p><strong>ғафс</strong> <a href="https://khf.tj">пайванд</a></p><ul><li>як</li></ul>'
    );

    expect($clean)->toContain('<h2>')
        ->and($clean)->toContain('<strong>')
        ->and($clean)->toContain('href="https://khf.tj"')
        ->and($clean)->toContain('<li>');
});

test('the sanitizer allows youtube iframes but drops untrusted ones', function () {
    $clean = app(BodySanitizer::class)->clean(
        '<iframe src="https://www.youtube.com/embed/abc"></iframe><iframe src="https://evil.example/x"></iframe>'
    );

    expect($clean)->toContain('youtube.com/embed/abc')
        ->and($clean)->not->toContain('evil.example');
});

test('the news body is sanitized when saved', function () {
    $news = News::factory()->create([
        'body' => ['tj' => '<p>Матни хуб</p><script>alert(1)</script>'],
    ]);

    $body = $news->getTranslation('body', 'tj');

    expect($body)->not->toContain('script')
        ->and($body)->toContain('Матни хуб');
});

test('sanitization applies to every locale', function () {
    $news = News::factory()->create([
        'body' => [
            'tj' => '<p>tj</p><script>a</script>',
            'ru' => '<p>ru</p><img src="x" onerror="hack()">',
        ],
    ]);

    expect($news->getTranslation('body', 'tj'))->not->toContain('script')
        ->and($news->getTranslation('body', 'ru'))->not->toContain('onerror');
});
