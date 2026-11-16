<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ImageTools;
use App\Libraries\Mailer;
use App\Models\SettingModel;

class Settings extends BaseController
{
    private const KEYS = [
        'workshop_name', 'workshop_contact', 'currency_symbol',
        'locale', 'pdf_footer', 'whatsapp_template',
        'whatsapp_quote_template', 'whatsapp_ready_template',
        'smtp_host', 'smtp_port', 'smtp_crypto', 'smtp_user',
        'smtp_from_email', 'smtp_from_name',
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

        $port = $this->request->getPost('smtp_port');
        if ($port !== '' && (! is_numeric($port) || (int) $port < 1 || (int) $port > 65535)) {
            return redirect()->back()->with('error', 'El puerto SMTP no es válido.');
        }

        $crypto = (string) $this->request->getPost('smtp_crypto');
        if (! in_array($crypto, ['', 'tls', 'ssl'], true)) {
            return redirect()->back()->with('error', 'El cifrado SMTP no es válido.');
        }

        $from = trim((string) $this->request->getPost('smtp_from_email'));
        if ($from !== '' && ! filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'El email remitente no es válido.');
        }

        $model = new SettingModel();
        foreach (self::KEYS as $key) {
            $model->setValue($key, trim((string) $this->request->getPost($key)));
        }

        $smtpPass = (string) $this->request->getPost('smtp_pass');
        if ($smtpPass !== '') {
            $model->setValue('smtp_pass', $smtpPass);
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

            if ($ext === 'png') {
                (new ImageTools())->trimTransparentPng(FCPATH . 'uploads/' . $newName);
            }

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

    public function testEmail()
    {
        $to = trim((string) $this->request->getPost('test_email'));

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('/admin/settings')->with('error', 'Ingresá un email válido para la prueba.');
        }

        $mailer = new Mailer();
        if (! $mailer->isConfigured()) {
            return redirect()->to('/admin/settings')->with('error', 'Configurá el host SMTP antes de probar.');
        }

        $ok = $mailer->send(
            $to,
            'Prueba SMTP · ' . setting('workshop_name', 'Taller'),
            '<p>Este es un email de prueba de <strong>' . esc(setting('workshop_name', 'Taller')) . '</strong>.</p>'
        );

        return redirect()->to('/admin/settings')->with(
            $ok ? 'success' : 'error',
            $ok ? 'Email de prueba enviado.' : 'No se pudo enviar. Revisá el host, puerto, cifrado y credenciales (en Gmail usá App Password).'
        );
    }
}
