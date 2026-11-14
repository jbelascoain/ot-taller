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
            'must_change_password' => 1,
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

    public function delete(int $id)
    {
        $me = current_user();

        if ($me && (int) $me['id'] === $id) {
            return redirect()->to('/admin/users')->with('error', 'No podés eliminar tu propio usuario.');
        }

        $users = new UserModel();
        $user  = $users->find($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'Usuario no encontrado.');
        }

        if ($user['role'] === 'ADMIN' && (int) $user['active'] === 1 && $users->countActiveAdmins() <= 1) {
            return redirect()->to('/admin/users')->with('error', 'No podés eliminar al último administrador activo.');
        }

        $users->delete($id);

        return redirect()->to('/admin/users')->with('success', 'Usuario eliminado.');
    }

    public function password(int $id)
    {
        $password = (string) $this->request->getPost('password');

        if (strlen($password) < 8) {
            return redirect()->to('/admin/users')->with('error', 'La contraseña debe tener al menos 8 caracteres.');
        }

        $me = current_user();
        $mustChange = ($me && (int) $me['id'] === $id) ? 0 : 1;

        (new UserModel())->update($id, [
            'password_hash'        => password_hash($password, PASSWORD_DEFAULT),
            'must_change_password' => $mustChange,
        ]);

        $message = $mustChange
            ? 'Contraseña actualizada. El usuario deberá cambiarla en el próximo login.'
            : 'Contraseña actualizada.';

        return redirect()->to('/admin/users')->with('success', $message);
    }
}
