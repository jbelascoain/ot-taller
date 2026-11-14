<?php

if (! function_exists('setting')) {
    function setting(string $key, string $default = ''): string
    {
        static $cache = null;

        if ($cache === null) {
            $cache = [];
            $db = db_connect();
            foreach ($db->table('settings')->get()->getResultArray() as $row) {
                $cache[$row['setting_key']] = (string) $row['setting_value'];
            }
        }

        $value = $cache[$key] ?? $default;

        if ($key === 'pdf_footer' || $key === 'whatsapp_template') {
            $value = str_replace('{workshop}', setting('workshop_name', 'Taller'), $value);
        }

        return $value;
    }
}

if (! function_exists('money')) {
    function money($value): string
    {
        return setting('currency_symbol', '$') . ' ' . number_format((float) $value, 2, ',', '.');
    }
}

if (! function_exists('fecha')) {
    function fecha(?string $value): string
    {
        if (empty($value)) {
            return '—';
        }

        $time = strtotime($value);

        return $time ? date('d/m/Y', $time) : '—';
    }
}

if (! function_exists('uid')) {
    function uid(string $prefix, $id): string
    {
        return $prefix . '-' . (int) $id;
    }
}

if (! function_exists('current_user')) {
    function current_user(): ?array
    {
        return session('user') ?: null;
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return (current_user()['role'] ?? '') === 'ADMIN';
    }
}

if (! function_exists('estado_label')) {
    function estado_label(string $status): string
    {
        return [
            'INGRESO'        => 'Ingreso',
            'PRESUPUESTADO'  => 'Presupuestado',
            'EN_REPARACION'  => 'En reparación',
            'PARA_ENTREGAR'  => 'Para entregar',
            'ENTREGADO'      => 'Entregado',
            'CANCELADO'      => 'Cancelado',
        ][$status] ?? $status;
    }
}

if (! function_exists('estado_class')) {
    function estado_class(string $status): string
    {
        return [
            'INGRESO'       => 'ingreso',
            'PRESUPUESTADO' => 'presupuestado',
            'EN_REPARACION' => 'reparacion',
            'PARA_ENTREGAR' => 'para-entregar',
            'ENTREGADO'     => 'entregado',
            'CANCELADO'     => 'cancelado',
        ][$status] ?? 'cancelado';
    }
}

if (! function_exists('item_estado_label')) {
    function item_estado_label(string $status): string
    {
        return [
            'PRESUPUESTADO' => 'Presupuestado',
            'APROBADO'      => 'Aprobado',
            'RECHAZADO'     => 'Rechazado',
        ][$status] ?? $status;
    }
}

if (! function_exists('item_estado_class')) {
    function item_estado_class(string $status): string
    {
        return [
            'PRESUPUESTADO' => 'presupuestado',
            'APROBADO'      => 'aprobado',
            'RECHAZADO'     => 'rechazado',
        ][$status] ?? 'presupuestado';
    }
}

if (! function_exists('wa_link')) {
    function wa_link(?string $phone, string $text): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return '';
        }

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($text);
    }
}

if (! function_exists('whatsapp_message')) {
    function whatsapp_message(array $order, string $moto): string
    {
        $template = setting('whatsapp_template', 'Hola {cliente}, te envío el presupuesto de la orden {orden} ({moto}). Total: {total}.');

        return str_replace(
            ['{cliente}', '{orden}', '{moto}', '{total}'],
            [
                $order['customer_name'] ?? '',
                uid('OT', $order['id']),
                $moto,
                money($order['total'] ?? 0),
            ],
            $template
        );
    }
}

if (! function_exists('dias_en_taller')) {
    function dias_en_taller(string $entryDate): ?int
    {
        $start = strtotime($entryDate);

        if (! $start) {
            return null;
        }

        $diff = (int) floor((time() - $start) / 86400);

        return $diff >= 0 ? $diff : null;
    }
}
