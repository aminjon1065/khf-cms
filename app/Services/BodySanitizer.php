<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Server-side whitelist sanitizer for Tiptap/RichEditor HTML (ToR §5.2 / §10).
 * Only the editor's allowed formatting survives; scripts, event handlers, and
 * unknown elements/attributes are stripped. Iframes are restricted to trusted
 * video hosts (YouTube embed).
 */
class BodySanitizer
{
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig)
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('strong')
            ->allowElement('b')
            ->allowElement('em')
            ->allowElement('i')
            ->allowElement('u')
            ->allowElement('s')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('h4')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('blockquote')
            ->allowElement('hr')
            ->allowElement('a', ['href', 'target', 'rel'])
            ->allowElement('img', ['src', 'alt', 'width', 'height'])
            ->allowElement('table')
            ->allowElement('thead')
            ->allowElement('tbody')
            ->allowElement('tr')
            ->allowElement('th')
            ->allowElement('td')
            ->allowElement('iframe', ['src', 'width', 'height', 'allowfullscreen', 'frameborder'])
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->allowMediaHosts(['youtube.com', 'www.youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com'])
            ->allowRelativeMedias()
            ->forceHttpsUrls();

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function clean(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        return $this->sanitizer->sanitize($html);
    }
}
