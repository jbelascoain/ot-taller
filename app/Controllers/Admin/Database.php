<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Database extends BaseController
{
    private const MAX_IMPORT_BYTES = 20 * 1024 * 1024;

    public function index()
    {
        return view('admin/database', [
            'title' => 'Base de datos',
        ]);
    }

    public function export()
    {
        $db     = db_connect();
        $tables = $db->listTables();
        sort($tables);

        $sql = "-- ot-taller · respaldo de base de datos\n"
            . '-- Generado: ' . date('Y-m-d H:i:s') . "\n"
            . '-- Base: ' . $db->getDatabase() . "\n"
            . "-- Importante: contiene usuarios y configuración sensible.\n\n"
            . "SET NAMES utf8mb4;\n"
            . "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $quoted = '`' . str_replace('`', '``', $table) . '`';
            $row    = $db->query('SHOW CREATE TABLE ' . $quoted)->getRowArray();
            $create = $row['Create Table'] ?? (array_values($row)[1] ?? '');

            if ($create === '') {
                continue;
            }

            $sql .= "-- Tabla: {$table}\n";
            $sql .= "DROP TABLE IF EXISTS {$quoted};\n";
            $sql .= $create . ";\n\n";

            $rows = $db->table($table)->get()->getResultArray();

            if ($rows === []) {
                continue;
            }

            $columns    = array_keys($rows[0]);
            $columnList = implode(', ', array_map(
                static fn (string $column): string => '`' . str_replace('`', '``', $column) . '`',
                $columns
            ));

            foreach (array_chunk($rows, 100) as $chunk) {
                $values = [];

                foreach ($chunk as $dataRow) {
                    $rowValues = [];

                    foreach ($columns as $column) {
                        $value = $dataRow[$column] ?? null;

                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } elseif (is_int($value) || is_float($value)) {
                            $rowValues[] = (string) $value;
                        } else {
                            $rowValues[] = $db->escape((string) $value);
                        }
                    }

                    $values[] = '(' . implode(', ', $rowValues) . ')';
                }

                $sql .= "INSERT INTO {$quoted} ({$columnList}) VALUES\n"
                    . implode(",\n", $values)
                    . ";\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $filename = 'ot-taller-backup-' . date('Ymd-His') . '.sql';

        return $this->response
            ->setHeader('Content-Type', 'application/sql; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($sql);
    }

    public function import()
    {
        $file = $this->request->getFile('sql_file');

        if (! $file || ! $file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
            return redirect()->to('/admin/database')->with('error', 'Elegí un archivo .sql válido.');
        }

        if ($file->getSize() > self::MAX_IMPORT_BYTES) {
            return redirect()->to('/admin/database')->with('error', 'El archivo supera el máximo de 20 MB.');
        }

        if (strtolower(pathinfo($file->getClientName(), PATHINFO_EXTENSION)) !== 'sql') {
            return redirect()->to('/admin/database')->with('error', 'El archivo debe tener extensión .sql.');
        }

        if ($this->request->getPost('confirm_import') !== '1') {
            return redirect()->to('/admin/database')->with('error', 'Confirmá que querés reemplazar la base actual.');
        }

        $sql = (string) file_get_contents($file->getTempName());
        $sql = ltrim($sql, "\xEF\xBB\xBF");

        if (trim($sql) === '') {
            return redirect()->to('/admin/database')->with('error', 'El archivo está vacío.');
        }

        if (stripos($sql, 'DELIMITER') !== false) {
            return redirect()->to('/admin/database')->with('error', 'El archivo usa DELIMITER, que no está soportado.');
        }

        $db         = db_connect();
        $connection = $db->getConnection();

        if (! $connection instanceof \mysqli) {
            return redirect()->to('/admin/database')->with('error', 'El driver de base de datos no permite restaurar archivos.');
        }

        @set_time_limit(300);

        $ok    = true;
        $error = '';

        try {
            mysqli_query($connection, 'SET FOREIGN_KEY_CHECKS = 0');

            if (! mysqli_multi_query($connection, $sql)) {
                $ok    = false;
                $error = mysqli_error($connection);
            } else {
                do {
                    if ($result = mysqli_store_result($connection)) {
                        mysqli_free_result($result);
                    }

                    if (! mysqli_more_results($connection)) {
                        break;
                    }

                    if (! mysqli_next_result($connection)) {
                        $ok    = false;
                        $error = mysqli_error($connection);
                        break;
                    }
                } while (true);
            }
        } catch (\Throwable $exception) {
            $ok    = false;
            $error = $exception->getMessage();
        }

        try {
            mysqli_query($connection, 'SET FOREIGN_KEY_CHECKS = 1');
        } catch (\Throwable $exception) {
            // La conexión ya no está disponible; no hay nada más que hacer.
        }

        if (! $ok) {
            return redirect()->to('/admin/database')->with(
                'error',
                'No se pudo importar: ' . ($error !== '' ? $error : 'error desconocido') . '. La base puede haber quedado parcialmente modificada.'
            );
        }

        return redirect()->to('/')->with('success', 'Base de datos restaurada correctamente.');
    }
}
