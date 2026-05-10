<?php

namespace App\controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\helpers\ValidatorHelper;
use App\models\Admin;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->view('auth/login', ['title' => 'Login'], 'layouts/guest');
    }

    public function login(Request $request): void
    {
        $email = ValidatorHelper::sanitizeString($request->input('email', ''));
        $password = (string) $request->input('password', '');

        if (!ValidatorHelper::email($email) || $password === '') {
            Session::flash('error', 'Please enter a valid email and password.');
            redirect('/login');
        }

        $admin = (new Admin())->findByEmail($email);
        if ($admin === null || !password_verify($password, (string) $admin['password_hash'])) {
            Session::flash('error', 'Invalid login credentials.');
            redirect('/login');
        }

        Session::authenticate($admin);
        (new Admin())->touchLogin((int) $admin['id']);
        redirect('/dashboard');
    }

    public function logout(): void
    {
        Session::destroy();
        redirect('/login');
    }
}
