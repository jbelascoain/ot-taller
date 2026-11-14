<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'setting_key';
    protected $returnType    = 'array';
    protected $allowedFields = ['setting_key', 'setting_value'];

    public function getValue(string $key, string $default = ''): string
    {
        $row = $this->find($key);

        return $row ? (string) $row['setting_value'] : $default;
    }

    public function setValue(string $key, string $value): void
    {
        $this->upsert(['setting_key' => $key, 'setting_value' => $value]);
    }

    public function allKeyed(): array
    {
        $out = [];
        foreach ($this->findAll() as $row) {
            $out[$row['setting_key']] = (string) $row['setting_value'];
        }

        return $out;
    }
}
