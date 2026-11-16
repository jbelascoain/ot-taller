<?php
$bike = trim(implode(' ', array_filter([$order['brand'] ?? '', $order['model'] ?? ''])));
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 14mm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 10pt; color: #111; }
    h1, h2, h3, p { margin: 0; }
    .muted { color: #666; }
    .small { font-size: 8pt; }
    .right { text-align: right; }
    .header { width: 100%; border-bottom: 1px solid #bbb; padding-bottom: 8px; }
    .header td { vertical-align: top; }
    .logo { max-height: 70px; max-width: 200px; }
    .brand { font-size: 14pt; font-weight: bold; }
    .ot { font-size: 20pt; font-weight: bold; }
    .label { font-size: 7pt; text-transform: uppercase; letter-spacing: .08em; color: #777; }
    .two { width: 100%; margin-top: 10px; }
    .two td { width: 50%; vertical-align: top; padding-right: 12px; }
    .section { margin-top: 12px; }
    .items { width: 100%; border-collapse: collapse; margin-top: 8px; }
    .items th { background: #f2f2f2; color: #555; font-size: 7.5pt; text-transform: uppercase; text-align: left; padding: 5px; border-bottom: 1px solid #ccc; }
    .items td { padding: 5px; border-bottom: 1px solid #e5e5e5; }
    .items .num { text-align: right; white-space: nowrap; }
    .totals { width: 60mm; margin-left: auto; margin-top: 8px; }
    .totals td { padding: 3px 0; }
    .totals .total td { border-top: 1px solid #999; font-weight: bold; font-size: 12pt; }
    .footer { margin-top: 18px; text-align: center; font-size: 8pt; color: #777; }
</style>
</head>
<body>
<table class="header">
    <tr>
        <td>
            <?php if ($logo): ?>
                <img class="logo" src="<?= $logo ?>" alt="<?= esc($workshop) ?>">
            <?php else: ?>
                <div class="brand"><?= esc($workshop) ?></div>
            <?php endif; ?>
            <?php if ($contact !== ''): ?><div class="muted small"><?= esc($contact) ?></div><?php endif; ?>
        </td>
        <td class="right">
            <div class="label">Orden de trabajo</div>
            <div class="ot"><?= esc(uid('OT', $order['id'])) ?></div>
            <div class="small muted">Ingreso: <?= esc(fecha($order['entry_date'])) ?> · Estado: <?= esc(estado_label($order['status'])) ?></div>
        </td>
    </tr>
</table>

<table class="two">
    <tr>
        <td>
            <div class="label">Cliente</div>
            <div><strong><?= esc($order['customer_name'] ?? '—') ?></strong></div>
            <div class="small"><?= esc($order['customer_phone'] ?: 'Sin teléfono') ?></div>
        </td>
        <td>
            <div class="label">Moto</div>
            <div><strong><?= esc(uid('MOT', $order['motorcycle_id'])) ?><?= $bike ? ' · ' . esc($bike) : '' ?></strong></div>
            <div class="small">Patente: <?= esc($order['plate'] ?: '—') ?> · KM: <?= esc($order['km'] ?? '—') ?></div>
            <div class="small">Chasis: <?= esc($order['chassis'] ?: '—') ?> · Motor: <?= esc($order['engine'] ?: '—') ?></div>
        </td>
    </tr>
</table>

<div class="section">
    <table class="two">
        <tr>
            <td>
                <div class="label">Problema informado</div>
                <div class="small"><?= nl2br(esc($order['problem'])) ?></div>
            </td>
            <td>
                <div class="label">Diagnóstico</div>
                <div class="small"><?= nl2br(esc($order['diagnosis'] ?: '—')) ?></div>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <table class="items">
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="num">Cant.</th>
                <th class="num">P. unitario</th>
                <th class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($items) === 0): ?>
                <tr><td colspan="4" class="muted small">Sin ítems cargados.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $item): ?>
                <?php $subtotal = (float) $item['unit_price'] * (int) $item['quantity']; ?>
                <tr>
                    <td><?= esc($item['description']) ?></td>
                    <td class="num"><?= (int) $item['quantity'] ?></td>
                    <td class="num"><?= esc(money($item['unit_price'])) ?></td>
                    <td class="num"><?= esc(money($subtotal)) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="muted">Mano de obra</td><td class="right"><?= esc(money($totals['labor'])) ?></td></tr>
        <tr><td class="muted">Repuestos</td><td class="right"><?= esc(money($totals['parts'])) ?></td></tr>
        <tr class="total"><td>Total</td><td class="right"><?= esc(money($totals['total'])) ?></td></tr>
    </table>
</div>

<?php if (! empty($order['notes'])): ?>
    <div class="section">
        <div class="label">Observaciones</div>
        <div class="small"><?= nl2br(esc($order['notes'])) ?></div>
    </div>
<?php endif; ?>

<div class="footer"><?= esc($footer) ?></div>
</body>
</html>
