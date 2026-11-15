<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$renderTrend = static function (?array $trend): string {
    if ($trend === null || $trend['pct'] === null) {
        return '<span class="metric-trend">—</span>';
    }

    $arrow = ['up' => '↑', 'down' => '↓', 'flat' => '→'][$trend['dir']] ?? '→';
    $class = $trend['good'] === true ? 'good' : ($trend['good'] === false ? 'bad' : '');

    return '<span class="metric-trend ' . $class . '">' . $arrow . ' '
        . number_format(abs((float) $trend['pct']), 1, ',', '.') . '% vs período anterior</span>';
};

$presets = [
    '7d'         => 'Últimos 7 días',
    '30d'        => 'Últimos 30 días',
    'month'      => 'Este mes',
    'prev_month' => 'Mes anterior',
    'year'       => 'Este año',
    'custom'     => 'Personalizado',
];

$statusTotal = array_sum(array_column($statuses, 'count'));
?>
<div class="page-head">
    <div>
        <h1>Métricas</h1>
        <p class="subtitle">Facturación, actividad y estado del taller</p>
    </div>
</div>

<form method="get" action="<?= base_url('metricas') ?>" class="metrics-filter mb-1" data-metrics-form>
    <select name="preset" data-metrics-preset aria-label="Período">
        <?php foreach ($presets as $value => $label): ?>
            <option value="<?= esc($value) ?>" <?= $preset === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
        <?php endforeach; ?>
    </select>
    <div class="metrics-custom <?= $preset === 'custom' ? '' : 'hidden' ?>" data-metrics-custom>
        <input type="date" name="from" value="<?= esc($from) ?>" aria-label="Desde">
        <span class="muted text-sm">al</span>
        <input type="date" name="to" value="<?= esc($to) ?>" aria-label="Hasta">
    </div>
    <button class="btn btn-sm" type="submit">Aplicar</button>
</form>

<p class="metrics-period mb-2">
    Del <?= esc(fecha($from)) ?> al <?= esc(fecha($to)) ?>
    · Comparado con <?= esc(fecha($prevFrom)) ?> – <?= esc(fecha($prevTo)) ?>
</p>

<?php if ($missingDates > 0): ?>
    <div class="alert error">
        Hay <?= (int) $missingDates ?> orden<?= $missingDates === 1 ? '' : 'es' ?> entregada<?= $missingDates === 1 ? '' : 's' ?>
        sin fecha de entrega. Los reportes usan la fecha de última modificación como aproximación.
    </div>
<?php endif; ?>

<div class="metric-grid mb-2">
    <div class="stat metric-card" title="Mano de obra + repuestos aprobados de órdenes entregadas en el período.">
        <div class="lbl">Facturación</div>
        <div class="num"><?= esc(money($current['revenue'])) ?></div>
        <?= $renderTrend($trends['revenue']) ?>
    </div>
    <div class="stat metric-card" title="Cantidad de órdenes entregadas en el período.">
        <div class="lbl">Órdenes entregadas</div>
        <div class="num"><?= (int) $current['delivered'] ?></div>
        <?= $renderTrend($trends['delivered']) ?>
    </div>
    <div class="stat metric-card" title="Facturación dividida por la cantidad de órdenes entregadas.">
        <div class="lbl">Ticket promedio</div>
        <div class="num"><?= esc(money($current['ticket'])) ?></div>
        <?= $renderTrend($trends['ticket']) ?>
    </div>
    <div class="stat metric-card" title="Precio de venta menos costo actual del repuesto, en ítems aprobados entregados.">
        <div class="lbl">Margen estimado</div>
        <div class="num"><?= esc(money($current['margin'])) ?></div>
        <?= $renderTrend($trends['margin']) ?>
        <div class="metric-note">Con costo actual del repuesto</div>
    </div>
    <div class="stat metric-card" title="Órdenes con fecha de ingreso dentro del período.">
        <div class="lbl">Órdenes ingresadas</div>
        <div class="num"><?= (int) $current['created'] ?></div>
        <?= $renderTrend($trends['created']) ?>
    </div>
    <div class="stat metric-card" title="Órdenes activas con ítems pendientes de aprobación. Es una foto del momento actual.">
        <div class="lbl">Presupuestos pendientes</div>
        <div class="num"><?= esc(money($pendingTotal)) ?></div>
        <div class="metric-trend"><?= count($pending) ?> <?= count($pending) === 1 ? 'orden' : 'órdenes' ?> por aprobar hoy</div>
    </div>
    <div class="stat metric-card" title="Valor aprobado / (aprobado + rechazado) de órdenes ingresadas en el período.">
        <div class="lbl">Tasa de aprobación</div>
        <div class="num"><?= $current['approval'] === null ? '—' : number_format((float) $current['approval'], 1, ',', '.') . '%' ?></div>
        <?= $renderTrend($trends['approval']) ?>
    </div>
    <div class="stat metric-card" title="Promedio de días entre el ingreso y la entrega de las órdenes entregadas en el período.">
        <div class="lbl">Tiempo medio de entrega</div>
        <div class="num"><?= $current['avg_days'] === null ? '—' : number_format((float) $current['avg_days'], 1, ',', '.') . ' días' ?></div>
        <?= $renderTrend($trends['avg_days']) ?>
    </div>
</div>

<div class="metrics-charts mb-2">
    <div class="card chart-card">
        <div class="card-head">
            <span class="card-title">Actividad</span>
            <span class="text-xs muted">Ingresadas vs entregadas</span>
        </div>
        <div class="chart-wrap"><canvas id="metrics-activity"></canvas></div>
    </div>
    <div class="card chart-card">
        <div class="card-head">
            <span class="card-title">Facturación</span>
            <span class="text-xs muted">Mano de obra + repuestos</span>
        </div>
        <div class="chart-wrap"><canvas id="metrics-revenue"></canvas></div>
    </div>
    <div class="card chart-card">
        <div class="card-head">
            <span class="card-title">Órdenes por estado</span>
            <span class="text-xs muted">Situación actual</span>
        </div>
        <?php if ($statusTotal === 0): ?>
            <div class="empty">Sin órdenes todavía.</div>
        <?php else: ?>
            <div class="chart-wrap chart-wrap-sm"><canvas id="metrics-status"></canvas></div>
        <?php endif; ?>
    </div>
    <div class="card chart-card">
        <div class="card-head">
            <span class="card-title">Top repuestos</span>
            <span class="text-xs muted">Por facturación</span>
        </div>
        <?php if (count($topParts) === 0): ?>
            <div class="empty">Sin repuestos aprobados en el período.</div>
        <?php else: ?>
            <div class="chart-wrap"><canvas id="metrics-top-parts"></canvas></div>
        <?php endif; ?>
    </div>
</div>

<div class="metrics-tables mb-2">
    <div class="card">
        <div class="card-head">
            <span class="card-title">Stock crítico</span>
            <span class="text-xs muted"><?= count($lowStock) ?> crítico<?= count($lowStock) === 1 ? '' : 's' ?> · Inventario total <?= esc(money($inventoryValue)) ?></span>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Repuesto</th>
                        <th class="num">Stock</th>
                        <th class="num">Mínimo</th>
                        <th>Proveedor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($lowStock) === 0): ?>
                        <tr><td colspan="4" class="empty">Sin stock crítico.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($lowStock as $part): ?>
                        <tr>
                            <td data-label="Repuesto"><a href="<?= base_url('parts/' . $part['id'] . '/edit') ?>"><?= esc($part['description']) ?></a></td>
                            <td class="num" data-label="Stock"><?= (int) $part['stock'] ?></td>
                            <td class="num" data-label="Mínimo"><?= (int) $part['min_stock'] ?></td>
                            <td data-label="Proveedor"><?= esc($part['supplier_name'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <span class="card-title">Presupuestos pendientes</span>
            <span class="text-xs muted"><?= count($pending) ?> · <?= esc(money($pendingTotal)) ?></span>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>OT</th>
                        <th>Cliente</th>
                        <th class="num">Total</th>
                        <th class="num">Días</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pending) === 0): ?>
                        <tr><td colspan="4" class="empty">Sin presupuestos pendientes.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($pending as $row): ?>
                        <tr>
                            <td data-label="OT"><a href="<?= base_url('orders/' . $row['id']) ?>"><strong><?= esc($row['uid']) ?></strong></a></td>
                            <td data-label="Cliente"><?= esc($row['customer_name']) ?></td>
                            <td class="num" data-label="Total"><?= esc(money($row['total'])) ?></td>
                            <td class="num" data-label="Días"><?= (int) $row['days'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Top clientes</span>
        <span class="text-xs muted">Nuevos en el período: <?= (int) $newCustomers ?></span>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th class="num">Órdenes</th>
                    <th class="num">Facturación</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($topCustomers) === 0): ?>
                    <tr><td colspan="3" class="empty">Sin entregas en el período.</td></tr>
                <?php endif; ?>
                <?php foreach ($topCustomers as $customer): ?>
                    <tr>
                        <td data-label="Cliente"><a href="<?= base_url('customers/' . $customer['id']) ?>"><?= esc($customer['name']) ?></a></td>
                        <td class="num" data-label="Órdenes"><?= (int) $customer['orders'] ?></td>
                        <td class="num" data-label="Facturación"><?= esc(money($customer['total'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<p class="text-xs muted mt-2">
    Facturación = mano de obra + repuestos aprobados de órdenes entregadas.
    El margen usa el costo actual del repuesto. No incluye cobros ni gastos.
</p>

<?php
$chartData = [
    'currency' => setting('currency_symbol', '$'),
    'series'   => $series,
    'statuses' => array_map(static fn (array $status): array => [
        'label' => $status['label'],
        'count' => $status['count'],
        'class' => $status['class'],
    ], $statuses),
    'topParts' => $topParts,
];
?>
<script type="application/json" id="metrics-data"><?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
<script src="<?= asset_url('assets/vendor/chart.umd.min.js') ?>"></script>
<script src="<?= asset_url('assets/metrics.js') ?>"></script>
<?= $this->endSection() ?>
