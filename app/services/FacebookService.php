<?php

namespace App\services;

use App\helpers\CurlHelper;

final class FacebookService
{
    public function __construct(
        private ?CookieService $cookies = null,
        private ?RandomizerService $randomizer = null,
        private ?SettingService $settings = null
    ) {
        $this->cookies ??= new CookieService();
        $this->randomizer ??= new RandomizerService();
        $this->settings ??= new SettingService();
    }

    public function fetchPostsFromTarget(array $target): array
    {
        $response = CurlHelper::request($target['source_url'], [
            'cookie_file' => $this->cookies->path(),
            'user_agent' => $this->randomizer->userAgent(),
        ]);

        if (!$response['ok']) {
            throw new \RuntimeException('Facebook fetch failed: ' . ($response['error'] ?: 'HTTP ' . $response['status']));
        }

        if ($this->looksLoggedOut($response['body'])) {
            throw new \RuntimeException('Facebook cookie is expired or not authenticated.');
        }

        return $this->extractPosts($response['body'], (string) $target['source_url']);
    }

    public function sendComment(array $post, string $commentBody): array
    {
        $page = CurlHelper::request($post['post_url'], [
            'cookie_file' => $this->cookies->path(),
            'user_agent' => $this->randomizer->userAgent(),
        ]);

        if (!$page['ok']) {
            return ['success' => false, 'message' => 'Failed opening post: HTTP ' . $page['status']];
        }

        if ($this->looksLoggedOut($page['body'])) {
            return ['success' => false, 'message' => 'Facebook cookie is expired.'];
        }

        $form = $this->extractCommentForm($page['body']);
        if ($form === null) {
            return ['success' => false, 'message' => 'Comment form was not found on the post page.'];
        }

        $payload = $form['fields'];
        $payload['comment_text'] = $commentBody;
        $payload['comment'] = $commentBody;

        $response = CurlHelper::request($form['action'], [
            'cookie_file' => $this->cookies->path(),
            'user_agent' => $this->randomizer->userAgent(),
            'post' => http_build_query($payload),
            'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        if (!$response['ok']) {
            return ['success' => false, 'message' => 'Comment request failed: HTTP ' . $response['status']];
        }

        if ($this->looksLoggedOut($response['body'])) {
            return ['success' => false, 'message' => 'Facebook session expired during comment request.'];
        }

        return ['success' => true, 'message' => 'Comment submitted to Facebook.'];
    }

    private function looksLoggedOut(string $html): bool
    {
        $needle = strtolower($html);
        return str_contains($needle, 'name="email"')
            && str_contains($needle, 'name="pass"')
            && (str_contains($needle, 'login') || str_contains($needle, 'checkpoint'));
    }

    private function extractPosts(string $html, string $sourceUrl): array
    {
        $posts = [];
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $links = $dom->getElementsByTagName('a');
        $base = parse_url($sourceUrl);
        $host = ($base['scheme'] ?? 'https') . '://' . ($base['host'] ?? parse_url((string) config('config.facebook.base_url'), PHP_URL_HOST));

        foreach ($links as $link) {
            $href = html_entity_decode((string) $link->getAttribute('href'));
            if (!$this->isPostUrl($href)) {
                continue;
            }

            $absolute = str_starts_with($href, 'http') ? $href : rtrim($host, '/') . '/' . ltrim($href, '/');
            $postId = $this->extractPostId($href);
            if ($postId === null) {
                continue;
            }

            $posts[$postId] = [
                'facebook_post_id' => $postId,
                'post_url' => $absolute,
                'post_hash' => hash('sha256', $postId . '|' . $absolute),
                'caption' => trim($link->textContent) ?: null,
                'author_name' => null,
            ];
        }

        libxml_clear_errors();
        return array_values($posts);
    }

    private function isPostUrl(string $href): bool
    {
        return str_contains($href, 'story_fbid=')
            || str_contains($href, '/posts/')
            || str_contains($href, '/permalink/')
            || str_contains($href, 'multi_permalinks=');
    }

    private function extractPostId(string $href): ?string
    {
        $query = parse_url($href, PHP_URL_QUERY);
        if (is_string($query)) {
            parse_str(html_entity_decode($query), $params);
            foreach (['story_fbid', 'multi_permalinks', 'fbid'] as $key) {
                if (!empty($params[$key])) {
                    return preg_replace('/[^0-9A-Za-z_\-]/', '', (string) $params[$key]);
                }
            }
        }

        if (preg_match('~/posts/([^/?#]+)~', $href, $matches) || preg_match('~/permalink/([^/?#]+)~', $href, $matches)) {
            return preg_replace('/[^0-9A-Za-z_\-]/', '', $matches[1]);
        }

        return null;
    }

    private function extractCommentForm(string $html): ?array
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $forms = $dom->getElementsByTagName('form');

        foreach ($forms as $form) {
            $action = html_entity_decode((string) $form->getAttribute('action'));
            if (!str_contains($action, 'comment')) {
                continue;
            }

            $fields = [];
            foreach ($form->getElementsByTagName('input') as $input) {
                $name = (string) $input->getAttribute('name');
                if ($name !== '') {
                    $fields[$name] = (string) $input->getAttribute('value');
                }
            }

            $base = $this->settings->string('facebook_base_url', config('config.facebook.base_url'));
            $action = str_starts_with($action, 'http') ? $action : rtrim($base, '/') . '/' . ltrim($action, '/');
            libxml_clear_errors();
            return ['action' => $action, 'fields' => $fields];
        }

        libxml_clear_errors();
        return null;
    }
}
