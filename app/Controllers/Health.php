<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Health extends BaseController
{
    public function index(): ResponseInterface
    {
        try {
            db_connect()->query('SELECT 1');
            $db = 'ok';
        } catch (\Throwable $e) {
            $db = 'error';
        }

        return $this->response->setJSON(['status' => 'ok', 'database' => $db, 'time' => date('c')]);
    }
}
