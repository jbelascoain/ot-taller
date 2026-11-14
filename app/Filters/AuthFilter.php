<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $sessionUser = session('user');

        if (! $sessionUser) {
            return redirect()->to('/login')->with('error', 'Iniciá sesión para continuar.');
        }

        $user = (new UserModel())->find((int) $sessionUser['id']);

        if (! $user || (int) $user['active'] !== 1) {
            session()->remove('user');

            return redirect()->to('/login')->with('error', 'Tu cuenta fue desactivada. Contactá al administrador.');
        }

        session()->set('user', [
            'id'                   => (int) $user['id'],
            'name'                 => $user['name'],
            'email'                => $user['email'],
            'role'                 => $user['role'],
            'must_change_password' => (int) $user['must_change_password'],
        ]);

        $path = trim($request->getUri()->getPath(), '/');
        if ((int) $user['must_change_password'] === 1 && ! in_array($path, ['change-password', 'logout'], true)) {
            return redirect()->to('/change-password');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
