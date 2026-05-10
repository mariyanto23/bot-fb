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

    public function checkCookieStatus(): array
    {
        if (!$this->cookies->exists()) {
            return [
                'status' => 'missing',
                'label' => 'Belum ada cookie',
                'message' => 'Cookie Facebook belum disimpan.',
            ];
        }

        $baseUrl = rtrim($this->settings->string('facebook_base_url', config('config.facebook.base_url')), '/');
        $response = CurlHelper::request($baseUrl . '/home.php', [
            'cookie_file' => $this->cookies->path(),
            'user_agent' => $this->randomizer->userAgent(),
            'timeout' => 15,
        ]);

        if (!$response['ok']) {
            return [
                'status' => 'error',
                'label' => 'Tidak bisa dicek',
                'message' => $response['error'] ?: 'HTTP ' . $response['status'],
            ];
        }

        if ($this->looksCheckpoint($response['body'])) {
            return [
                'status' => 'checkpoint',
                'label' => 'Checkpoint',
                'message' => 'Akun Facebook terkena checkpoint atau verifikasi.',
            ];
        }

        if ($this->looksLoggedOut($response['body'])) {
            return [
                'status' => 'expired',
                'label' => 'Expired',
                'message' => 'Cookie sudah tidak login. Simpan cookie baru.',
            ];
        }

        return [
            'status' => 'active',
            'label' => 'Aktif',
            'message' => 'Cookie masih bisa membuka halaman Facebook.',
        ];
    }

    private function looksLoggedOut(string $html): bool
    {
        $needle = strtolower($html);
        return str_contains($needle, 'name="email"')
            && str_contains($needle, 'name="pass"')
            && (str_contains($needle, 'login') || str_contains($needle, 'checkpoint'));
    }

    private function looksCheckpoint(string $html): bool
    {
        $needle = strtolower($html);
        return str_contains($needle, 'checkpoint')
            || str_contains($needle, 'two-factor')
            || str_contains($needle, 'approvals_code')
            || str_contains($needle, 'security check');
    }

    private function extractPosts(string $html, string $sourceUrl): array
    {
        $posts = [];
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $links = $dom->getElementsByTagName('a');
        $base = parse_url($sourceUrl);
        $fallbackHost = parse_url($this->settings->string('facebook_base_url', config('config.facebook.base_url')), PHP_URL_HOST);
        $host = ($base['scheme'] ?? 'https') . '://' . ($base['host'] ?? $fallbackHost);

        foreach ($links as $link) {
            $href = html_entity_decode((string) $link->getAttribute('href'));
            if (!$this->isPostUrl($href)) {
                continue;
            }

            $postId = $this->extractPostId($href);
            if ($postId === null) {
                continue;
            }

            $absolute = $this->absoluteUrl($href, $host);
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
        $href = strtolower(html_entity_decode($href));

        return str_contains($href, 'story_fbid=')
            || str_contains($href, 'multi_permalinks=')
            || str_contains($href, 'ft_ent_identifier=')
            || str_contains($href, '/posts/')
            || str_contains($href, '/permalink/')
            || str_contains($href, '/permalink.php')
            || str_contains($href, '/story.php')
            || str_contains($href, '/photo.php')
            || preg_match('~/groups/[^/?#]+/(?:posts|permalink)/[^/?#]+~', $href) === 1;
    }

    private function extractPostId(string $href): ?string
    {
        $href = html_entity_decode($href);
        $query = parse_url($href, PHP_URL_QUERY);
        if (is_string($query)) {
            parse_str(html_entity_decode($query), $params);
            foreach (['story_fbid', 'multi_permalinks', 'fbid', 'ft_ent_identifier', 'id'] as $key) {
                if (!empty($params[$key])) {
                    return preg_replace('/[^0-9A-Za-z_\-]/', '', (string) $params[$key]);
                }
            }
        }

        if (
            preg_match('~/posts/([^/?#]+)~', $href, $matches)
            || preg_match('~/permalink/([^/?#]+)~', $href, $matches)
            || preg_match('~/groups/[^/?#]+/(?:posts|permalink)/([^/?#]+)~', $href, $matches)
        ) {
            return preg_replace('/[^0-9A-Za-z_\-]/', '', $matches[1]);
        }

        return null;
    }

    private function absoluteUrl(string $href, string $host): string
    {
        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            return $href;
        }

        if (str_starts_with($href, '//')) {
            return 'https:' . $href;
        }

        return rtrim($host, '/') . '/' . ltrim($href, '/');
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
