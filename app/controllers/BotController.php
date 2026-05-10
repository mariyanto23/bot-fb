<?php

namespace App\controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\models\BotStatus;
use App\models\Post;
use App\services\CommentService;
use App\services\CookieService;
use App\services\FacebookService;

final class BotController extends Controller
{
    public function index(): void
    {
        $this->view('bot/index', [
            'title' => 'Bot Control',
            'stats' => (new Post())->stats(),
            'statuses' => (new BotStatus())->all(),
            'cookieExists' => (new CookieService())->exists(),
            'cookieStatus' => (new FacebookService())->checkCookieStatus(),
        ]);
    }

    public function fetchPosts(): void
    {
        $result = (new CommentService())->fetchPosts();
        Session::flash('success', 'Fetch completed. Created: ' . $result['created'] . ', skipped duplicates: ' . $result['skipped'] . '.');
        redirect('/bot');
    }

    public function sendComments(): void
    {
        $result = (new CommentService())->sendPendingComments(null, false);
        Session::flash('success', $result['message']);
        redirect('/bot');
    }

    public function run(): void
    {
        $result = (new CommentService())->runBot();
        Session::flash('success', $result['message']);
        redirect('/bot');
    }

    public function saveCookie(Request $request): void
    {
        $cookie = trim((string) $request->input('cookie_content', ''));
        if ($cookie === '') {
            Session::flash('error', 'Cookie content is required.');
            redirect('/bot');
        }

        (new CookieService())->saveRaw($cookie);
        Session::flash('success', 'Facebook cookie saved.');
        redirect('/bot');
    }

    public function clearCookie(): void
    {
        (new CookieService())->clear();
        Session::flash('success', 'Facebook cookie cleared.');
        redirect('/bot');
    }
}
