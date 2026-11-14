<?php

namespace App\Controllers;

use App\Libraries\DemoSeeder;
use App\Models\SettingModel;
use App\Models\UserModel;

class Setup extends BaseController
{
    public function index()
    {
        if ((new UserModel())->countAllResults() > 0) {
            return redirect()->to('/login');
        }

        return view('setup/index', ['tokenRequired' => env('SETUP_TOKEN', '') !== '']);
    }

    public function create()
    {
        if ((new UserModel())->countAllResults() > 0) {
            return redirect()->to('/login');
        }

        $rules = [
            'name'     => 'required|min_length[2]|max_length[120]',
            'email'    => 'required|valid_email|max_length[190]',
            'password' => 'required|min_length[8]',
            'workshop' => 'required|max_length[120]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $expected = env('SETUP_TOKEN', '');
        if ($expected !== '' && ! hash_equals($expected, (string) $this->request->getPost('setup_token'))) {
            return redirect()->back()->withInput()->with('error', 'Token de instalación inválido.');
        }

        $users = new UserModel();
        $users->insert([
            'name'          => trim((string) $this->request->getPost('name')),
            'email'         => strtolower(trim((string) $this->request->getPost('email'))),
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'          => 'ADMIN',
            'active'        => 1,
        ]);

        $settings = new SettingModel();
        $settings->setValue('workshop_name', trim((string) $this->request->getPost('workshop')));

        if ($this->request->getPost('demo') === '1') {
            (new DemoSeeder())->run();
        }

        return redirect()->to('/login')->with('success', 'Instalación lista. Ya podés iniciar sesión.');
    }
}
