<?php

namespace App\Controllers;

use App\Models\UserModel;

class ChangePassword extends BaseController
{
    public function index()
    {
        $user = (new UserModel())->find((int) current_user()['id']);

        return view('auth/change_password', [
            'title'  => 'Cambiar contraseña',
            'forced' => (int) ($user['must_change_password'] ?? 0) === 1,
        ]);
    }

    public function update()
    {
        $me   = current_user();
        $user = (new UserModel())->find((int) $me['id']);

        $current = (string) $this->request->getPost('current_password');
        $new     = (string) $this->request->getPost('new_password');
        $confirm = (string) $this->request->getPost('confirm_password');

        if (! $user || ! password_verify($current, $user['password_hash'])) {
            return redirect()->back()->with('error', 'La contraseña actual no es correcta.');
        }

        if (strlen($new) < 8) {
            return redirect()->back()->with('error', 'La nueva contraseña debe tener al menos 8 caracteres.');
        }

        if ($new !== $confirm) {
            return redirect()->back()->with('error', 'Las contraseñas nuevas no coinciden.');
        }

        if (password_verify($new, $user['password_hash'])) {
            return redirect()->back()->with('error', 'La nueva contraseña debe ser distinta de la actual.');
        }

        (new UserModel())->update((int) $user['id'], [
            'password_hash'        => password_hash($new, PASSWORD_DEFAULT),
            'must_change_password' => 0,
        ]);

        session()->set('user', array_merge($me, ['must_change_password' => 0]));

        return redirect()->to('/')->with('success', 'Contraseña actualizada.');
    }
}
