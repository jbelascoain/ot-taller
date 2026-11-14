<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session('user')) {
            return redirect()->to('/');
        }

        return view('auth/login', [
            'needsSetup' => (new UserModel())->countAllResults() === 0,
        ]);
    }

    public function attempt()
    {
        $lockedUntil = (int) session('login_locked_until');
        if ($lockedUntil > time()) {
            return redirect()->back()->with('error', 'Demasiados intentos. Probá de nuevo en 15 minutos.');
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        if ($email === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Completá email y contraseña.');
        }

        $user = (new UserModel())->findByEmail($email);

        if (! $user || (int) $user['active'] !== 1 || ! password_verify($password, $user['password_hash'])) {
            $attempts = (int) session('login_attempts') + 1;
            session()->set('login_attempts', $attempts);

            if ($attempts >= 5) {
                session()->set('login_locked_until', time() + 900);
                session()->set('login_attempts', 0);
            }

            return redirect()->back()->withInput()->with('error', 'Email o contraseña incorrectos.');
        }

        session()->remove(['login_attempts', 'login_locked_until']);
        session()->regenerate();
        session()->set('user', [
            'id'                   => (int) $user['id'],
            'name'                 => $user['name'],
            'email'                => $user['email'],
            'role'                 => $user['role'],
            'must_change_password' => (int) $user['must_change_password'],
        ]);

        if ((int) $user['must_change_password'] === 1) {
            return redirect()->to('/change-password');
        }

        return redirect()->to('/');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
