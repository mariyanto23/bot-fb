<?php

namespace App\services;

use App\helpers\CurlHelper;

final class FacebookService
{
    private array $lastFetchDebug = [];

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
        $attempts = [];

        foreach ($this->targetUrlVariants((string) $target['source_url']) as $url) {
            $response = CurlHelper::request($url, [
                'cookie_file' => $this->cookies->path(),
                'user_agent' => $this->randomizer->facebookUserAgent(),
                'headers' => $this->facebookHeaders(),
            ]);

            $this->lastFetchDebug = [
                'requested_url' => $url,
                'http_status' => $response['status'],
                'effective_url' => $response['info']['url'] ?? $url,
                'body_size' => strlen((string) $response['body']),
                'link_count' => 0,
                'candidate_count' => 0,
                'page_hint' => $this->pageHint($response['body']),
                'page_text_sample' => $this->pageTextSample($response['body']),
                'sample_links' => [],
                'sample_candidates' => [],
            ];

            if (!$response['ok']) {
                $attempts[] = $this->lastFetchDebug + ['error' => $response['error'] ?: 'HTTP ' . $response['status']];
                continue;
            }

            if ($this->looksLoggedOut($response['body'])) {
                throw new \RuntimeException('Facebook cookie is expired or not authenticated.');
            }

            $posts = $this->extractPosts($response['body'], $url);
            $attempts[] = $this->lastFetchDebug;

            if ($posts !== []) {
                $this->lastFetchDebug['attempts'] = $attempts;
                return $posts;
            }
        }

        $this->lastFetchDebug['attempts'] = $attempts;
        return [];
    }

    public function lastFetchDebug(): array
    {
        return $this->lastFetchDebug;
    }

    public function sendComment(array $post, string $commentBody): array
    {
        $page = CurlHelper::request($post['post_url'], [
            'cookie_file' => $this->cookies->path(),
            'user_agent' => $this->randomizer->facebookUserAgent(),
            'headers' => $this->facebookHeaders(),
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
            'user_agent' => $this->randomizer->facebookUserAgent(),
            'post' => http_build_query($payload),
            'headers' => array_merge($this->facebookHeaders(), ['Content-Type: application/x-www-form-urlencoded']),
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
            'user_agent' => $this->randomizer->facebookUserAgent(),
            'headers' => $this->facebookHeaders(),
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
            && (
                str_contains($needle, 'login')
                || str_contains($needle, 'masuk')
                || str_contains($needle, 'checkpoint')
                || str_contains($needle, 'm_login_email')
                || str_contains($needle, '/login.php')
            );
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
        $this->lastFetchDebug['link_count'] = $links->length;

        foreach ($links as $link) {
            $href = html_entity_decode((string) $link->getAttribute('href'));
            if ($href !== '' && count($this->lastFetchDebug['sample_links']) < 12) {
                $this->lastFetchDebug['sample_links'][] = [
                    'text' => mb_substr(trim(preg_replace('/\s+/', ' ', $link->textContent)), 0, 80),
                    'href' => mb_substr($href, 0, 240),
                ];
            }

            if (!$this->isPostUrl($href)) {
                continue;
            }

            $this->lastFetchDebug['candidate_count']++;
            if (count($this->lastFetchDebug['sample_candidates']) < 5) {
                $this->lastFetchDebug['sample_candidates'][] = mb_substr($href, 0, 240);
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
            || str_contains($href, 'view=permalink')
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

    private function pageHint(string $html): string
    {
        $plain = strtolower($this->cleanPageText($html));

        if ($this->looksCheckpoint($html)) {
            return 'checkpoint';
        }

        if ($this->looksLoggedOut($html)) {
            return 'login';
        }

        foreach ([
            'kesalahan' => 'facebook_error',
            'error' => 'facebook_error',
            'staticcontentonly' => 'browser_interstitial',
            'gunakan aplikasi facebook' => 'browser_interstitial',
            'use the facebook app' => 'browser_interstitial',
            'action=app_android' => 'browser_interstitial',
            'action=app_ios' => 'browser_interstitial',
            'gabung ke grup' => 'join_group',
            'join group' => 'join_group',
            'konten ini tidak tersedia' => 'content_unavailable',
            'content is not available' => 'content_unavailable',
            'anda harus masuk' => 'login_required',
            'you must log in' => 'login_required',
            'tidak dapat menampilkan' => 'cannot_display',
        ] as $needle => $hint) {
            if (str_contains($plain, $needle)) {
                return $hint;
            }
        }

        return 'unknown';
    }

    private function pageTextSample(string $html): string
    {
        return mb_substr($this->cleanPageText($html), 0, 700);
    }

    private function cleanPageText(string $html): string
    {
        $html = preg_replace('~<script\b[^>]*>.*?</script>~is', ' ', $html) ?? $html;
        $html = preg_replace('~<style\b[^>]*>.*?</style>~is', ' ', $html) ?? $html;
        $html = preg_replace('~<noscript\b[^>]*>.*?</noscript>~is', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    private function targetUrlVariants(string $sourceUrl): array
    {
        $variants = [$sourceUrl];
        $groupId = $this->extractGroupId($sourceUrl);

        if ($groupId !== null) {
            $variants[] = 'https://mbasic.facebook.com/groups/' . $groupId . '?view=group';
            $variants[] = 'https://mbasic.facebook.com/groups/' . $groupId . '?sorting_setting=CHRONOLOGICAL';
            $variants[] = 'https://mbasic.facebook.com/groups/' . $groupId . '?view=permalink';
            $variants[] = 'https://m.facebook.com/groups/' . $groupId;
        }

        return array_values(array_unique($variants));
    }

    private function extractGroupId(string $sourceUrl): ?string
    {
        if (preg_match('~/groups/([^/?#]+)~', $sourceUrl, $matches) === 1) {
            return preg_replace('/[^0-9A-Za-z._\-]/', '', $matches[1]);
        }

        return null;
    }

    private function facebookHeaders(): array
    {
        return [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
        ];
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
