<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    public function index()
    {
        return view('admin/users', [
            'title' => 'Usuarios',
            'users' => (new UserModel())->orderBy('id', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        $rules = [
            'name'     => 'required|max_length[120]',
            'email'    => 'required|valid_email|max_length[190]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[ADMIN,STAFF]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $users = new UserModel();
        $email = strtolower(trim((string) $this->request->getPost('email')));

        if ($users->findByEmail($email)) {
            return redirect()->back()->withInput()->with('error', 'Ya existe un usuario con ese email.');
        }

        $users->insert([
            'name'          => trim((string) $this->request->getPost('name')),
            'email'         => $email,
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'          => (string) $this->request->getPost('role'),
            'active'        => 1,
        ]);

        return redirect()->to('/admin/users')->with('success', 'Usuario creado.');
    }

    public function toggle(int $id)
    {
        $me   = current_user();
        $user = (new UserModel())->find($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'Usuario no encontrado.');
        }

        if ($me && (int) $me['id'] === $id) {
            return redirect()->to('/admin/users')->with('error', 'No podés desactivar tu propio usuario.');
        }

        (new UserModel())->update($id, ['active' => (int) $user['active'] === 1 ? 0 : 1]);

        return redirect()->to('/admin/users')->with('success', 'Usuario actualizado.');
    }

    public function password(int $id)
    {
        $password = (string) $this->request->getPost('password');

        if (strlen($password) < 8) {
            return redirect()->to('/admin/users')->with('error', 'La contraseña debe tener al menos 8 caracteres.');
        }

        (new UserModel())->update($id, [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/admin/users')->with('success', 'Contraseña actualizada.');
    }
}
