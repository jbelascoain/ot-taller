<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SettingModel;

class Settings extends BaseController
{
    private const KEYS = [
        'workshop_name', 'workshop_contact', 'currency_symbol',
        'locale', 'pdf_footer', 'whatsapp_template',
    ];

    public function index()
    {
        $model = new SettingModel();

        return view('admin/settings', [
            'title'    => 'Configuración',
            'settings' => $model->allKeyed(),
        ]);
    }

    public function update()
    {
        if (! $this->validate(['workshop_name' => 'required|max_length[120]'])) {
            return redirect()->back()->with('error', 'El nombre del taller es obligatorio.');
        }

        $model = new SettingModel();
        foreach (self::KEYS as $key) {
            $model->setValue($key, trim((string) $this->request->getPost($key)));
        }

        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()->with('error', 'El logo no puede superar 2 MB.');
            }

            $mime = $file->getMimeType();
            if (! in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true)) {
                return redirect()->back()->with('error', 'El logo debe ser PNG, JPG o WEBP.');
            }

            $ext     = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
            $newName = 'logo-' . time() . '.' . $ext;
            $file->move(FCPATH . 'uploads', $newName);

            $old = $model->getValue('logo_path');
            if ($old !== '' && is_file(FCPATH . 'uploads/' . $old)) {
                @unlink(FCPATH . 'uploads/' . $old);
            }

            $model->setValue('logo_path', $newName);
        }

        if ($this->request->getPost('remove_logo') === '1') {
            $old = $model->getValue('logo_path');
            if ($old !== '' && is_file(FCPATH . 'uploads/' . $old)) {
                @unlink(FCPATH . 'uploads/' . $old);
            }
            $model->setValue('logo_path', '');
        }

        return redirect()->to('/admin/settings')->with('success', 'Configuración guardada.');
    }
}
