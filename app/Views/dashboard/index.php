<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <h1>Tablero</h1>
        <p class="subtitle">Órdenes de trabajo del taller</p>
    </div>
    <div class="actions">
        <div class="flex gap wrap">
            <a class="btn btn-sm <?= $filter === 'active' ? 'btn-primary' : '' ?>" href="<?= base_url('/') ?>">Activas</a>
            <a class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : '' ?>" href="<?= base_url('/?filter=all') ?>">Todas</a>
            <a class="btn btn-sm <?= $filter === 'waiting' ? 'btn-primary' : '' ?>" href="<?= base_url('/?filter=waiting') ?>">Esperando cliente (<?= count($waiting) ?>)</a>
        </div>
        <a class="btn btn-primary" href="<?= base_url('orders/new') ?>">+ Nueva orden</a>
    </div>
</div>

<p class="muted mb-2">
    <?php if ($filter === 'waiting'): ?>
        <?= count($waiting) ?> <?= count($waiting) === 1 ? 'orden esperando' : 'órdenes esperando' ?> respuesta del cliente
    <?php elseif ($filter === 'active'): ?>
        <?= $openCount ?> <?= $openCount === 1 ? 'orden en taller' : 'órdenes en taller' ?>
    <?php else: ?>
        <?= count($orders) ?> <?= count($orders) === 1 ? 'orden en total' : 'órdenes en total' ?>
    <?php endif; ?>
</p>

<div class="board" data-board>
    <?php foreach ($columns as $status => $orders): ?>
        <section class="column" data-status="<?= esc($status) ?>">
            <div class="column-head">
                <span class="badge <?= esc(estado_class($status)) ?>"><?= esc(estado_label($status)) ?></span>
                <span class="count"><?= count($orders) ?></span>
            </div>
            <?php if (count($orders) === 0): ?>
                <p class="muted text-xs" style="padding:.4rem">Sin órdenes</p>
            <?php endif; ?>
            <?php foreach ($orders as $order): ?>
                <?php
                $days = dias_en_taller($order['entry_date']);
                $bike = trim(implode(' ', array_filter([$order['brand'] ?? '', $order['model'] ?? ''])));
                ?>
                <a class="job" draggable="true" data-id="<?= (int) $order['id'] ?>" href="<?= base_url('orders/' . $order['id']) ?>">
                    <div class="job-top">
                        <span class="ot"><?= esc(uid('OT', $order['id'])) ?></span>
                        <?php if ($days !== null): ?>
                            <span class="text-xs muted"><?= $days === 0 ? 'hoy' : $days . ' d' ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="bike"><?= esc($bike ?: uid('MOT', $order['motorcycle_id'])) ?><?= $order['plate'] ? ' · ' . esc($order['plate']) : '' ?></div>
                    <div class="cust"><?= esc($order['customer_name'] ?? 'Sin cliente') ?></div>
                    <div class="meta">
                        <span><?= esc(fecha($order['entry_date'])) ?></span>
                        <span class="total"><?= esc(money($order['total'])) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
