<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session('user');

        if (! $user) {
            return redirect()->to('/login')->with('error', 'Iniciá sesión para continuar.');
        }

        if (($user['role'] ?? '') !== 'ADMIN') {
            return redirect()->to('/')->with('error', 'Esa sección es solo para administradores.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
