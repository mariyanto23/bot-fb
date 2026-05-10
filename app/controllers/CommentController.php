<?php

namespace App\controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\helpers\ValidatorHelper;
use App\models\Comment;

final class CommentController extends Controller
{
    public function index(): void
    {
        $this->view('comments/index', [
            'title' => 'Comments',
            'comments' => (new Comment())->all(),
        ]);
    }

    public function store(Request $request): void
    {
        $body = trim((string) $request->input('body', ''));
        if ($body === '') {
            Session::flash('error', 'Comment body is required.');
            redirect('/comments');
        }

        (new Comment())->create($body, (bool) $request->input('is_active', false));
        Session::flash('success', 'Comment created.');
        redirect('/comments');
    }

    public function update(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        $body = trim((string) $request->input('body', ''));
        if ($id < 1 || $body === '') {
            Session::flash('error', 'Valid comment data is required.');
            redirect('/comments');
        }

        (new Comment())->update($id, $body, (bool) $request->input('is_active', false));
        Session::flash('success', 'Comment updated.');
        redirect('/comments');
    }

    public function delete(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        if ($id > 0) {
            (new Comment())->delete($id);
            Session::flash('success', 'Comment deleted.');
        }
        redirect('/comments');
    }
}
