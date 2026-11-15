<?php

namespace App\Models;

use CodeIgniter\Model;

class MetricsModel extends Model
{
    protected $table = 'work_orders';

    /**
     * Órdenes entregadas en el período, con mano de obra y repuestos aprobados.
     */
    public function deliveredRows(string $from, string $to): array
    {
        $sql = "SELECT w.id,
                       w.entry_date,
                       w.labor,
                       COALESCE(w.delivery_date, DATE(w.updated_at), w.entry_date) AS delivered_on,
                       COALESCE(SUM(CASE WHEN i.status = 'APROBADO' THEN i.unit_price * i.quantity ELSE 0 END), 0) AS parts_total
                FROM work_orders w
                LEFT JOIN order_items i ON i.order_id = w.id
                WHERE w.status = 'ENTREGADO'
                  AND COALESCE(w.delivery_date, DATE(w.updated_at), w.entry_date) BETWEEN ? AND ?
                GROUP BY w.id, w.entry_date, w.labor, w.delivery_date, w.updated_at
                ORDER BY delivered_on ASC";

        return $this->db->query($sql, [$from, $to])->getResultArray();
    }

    public function deliveredSummary(string $from, string $to): array
    {
        $rows    = $this->deliveredRows($from, $to);
        $labor   = 0.0;
        $parts   = 0.0;
        $days    = [];

        foreach ($rows as $row) {
            $labor += (float) $row['labor'];
            $parts += (float) $row['parts_total'];

            $start = strtotime((string) $row['entry_date']);
            $end   = strtotime((string) $row['delivered_on']);

            if ($start !== false && $end !== false && $end >= $start) {
                $days[] = ($end - $start) / 86400;
            }
        }

        $count   = count($rows);
        $revenue = $labor + $parts;

        return [
            'count'    => $count,
            'labor'    => $labor,
            'parts'    => $parts,
            'revenue'  => $revenue,
            'ticket'   => $count > 0 ? $revenue / $count : 0.0,
            'avg_days' => $days !== [] ? array_sum($days) / count($days) : null,
        ];
    }

    public function createdCount(string $from, string $to): int
    {
        return (int) $this->db->query(
            'SELECT COUNT(*) AS n FROM work_orders WHERE entry_date BETWEEN ? AND ?',
            [$from, $to]
        )->getRow()->n;
    }

    public function dailyCreated(string $from, string $to): array
    {
        return $this->db->query(
            'SELECT entry_date AS d, COUNT(*) AS n
             FROM work_orders
             WHERE entry_date BETWEEN ? AND ?
             GROUP BY entry_date
             ORDER BY entry_date ASC',
            [$from, $to]
        )->getResultArray();
    }

    public function marginTotal(string $from, string $to): float
    {
        $sql = "SELECT COALESCE(SUM((i.unit_price - COALESCE(p.purchase_price, 0)) * i.quantity), 0) AS margin
                FROM work_orders w
                JOIN order_items i ON i.order_id = w.id AND i.status = 'APROBADO'
                LEFT JOIN parts p ON p.id = i.part_id
                WHERE w.status = 'ENTREGADO'
                  AND COALESCE(w.delivery_date, DATE(w.updated_at), w.entry_date) BETWEEN ? AND ?";

        return (float) $this->db->query($sql, [$from, $to])->getRow()->margin;
    }

    public function approvalTotals(string $from, string $to): array
    {
        $sql = "SELECT COALESCE(SUM(CASE WHEN i.status = 'APROBADO' THEN i.unit_price * i.quantity ELSE 0 END), 0) AS approved,
                       COALESCE(SUM(CASE WHEN i.status = 'RECHAZADO' THEN i.unit_price * i.quantity ELSE 0 END), 0) AS rejected
                FROM work_orders w
                JOIN order_items i ON i.order_id = w.id
                WHERE w.entry_date BETWEEN ? AND ?
                  AND w.status <> 'CANCELADO'
                  AND i.status IN ('APROBADO', 'RECHAZADO')";

        $row = $this->db->query($sql, [$from, $to])->getRow();

        return [
            'approved' => (float) $row->approved,
            'rejected' => (float) $row->rejected,
        ];
    }

    public function statusCounts(): array
    {
        $rows = $this->db->query(
            'SELECT status, COUNT(*) AS n FROM work_orders GROUP BY status'
        )->getResultArray();

        return array_column($rows, 'n', 'status');
    }

    public function pendingQuotes(): array
    {
        $sql = "SELECT w.id,
                       w.entry_date,
                       w.status,
                       c.name AS customer_name,
                       SUM(i.unit_price * i.quantity) AS total,
                       COUNT(*) AS item_count,
                       SUM(CASE WHEN i.status = 'PRESUPUESTAR' THEN 1 ELSE 0 END) AS to_quote
                FROM work_orders w
                JOIN order_items i ON i.order_id = w.id AND i.status IN ('PRESUPUESTADO', 'PRESUPUESTAR')
                JOIN customers c ON c.id = w.customer_id
                WHERE w.status NOT IN ('ENTREGADO', 'CANCELADO')
                GROUP BY w.id, w.entry_date, w.status, c.name
                ORDER BY w.entry_date ASC";

        return $this->db->query($sql)->getResultArray();
    }

    public function topParts(string $from, string $to, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));

        $sql = "SELECT COALESCE(p.id, 0) AS part_id,
                       COALESCE(p.description, i.description) AS description,
                       SUM(i.quantity) AS qty,
                       SUM(i.unit_price * i.quantity) AS amount
                FROM work_orders w
                JOIN order_items i ON i.order_id = w.id AND i.status = 'APROBADO'
                LEFT JOIN parts p ON p.id = i.part_id
                WHERE w.status = 'ENTREGADO'
                  AND COALESCE(w.delivery_date, DATE(w.updated_at), w.entry_date) BETWEEN ? AND ?
                GROUP BY COALESCE(p.id, 0), COALESCE(p.description, i.description)
                ORDER BY amount DESC
                LIMIT {$limit}";

        return $this->db->query($sql, [$from, $to])->getResultArray();
    }

    public function lowStock(): array
    {
        $sql = "SELECT p.id, p.description, p.stock, p.min_stock, p.purchase_price, s.name AS supplier_name
                FROM parts p
                LEFT JOIN suppliers s ON s.id = p.supplier_id
                WHERE p.active = 1 AND p.stock <= p.min_stock
                ORDER BY (p.stock - p.min_stock) ASC, p.description ASC";

        return $this->db->query($sql)->getResultArray();
    }

    public function inventoryValue(): float
    {
        return (float) $this->db->query(
            'SELECT COALESCE(SUM(stock * purchase_price), 0) AS value FROM parts WHERE active = 1'
        )->getRow()->value;
    }

    public function topCustomers(string $from, string $to, int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));

        $sql = "SELECT c.id,
                       c.name,
                       COUNT(DISTINCT w.id) AS orders,
                       SUM(w.labor + COALESCE(x.parts, 0)) AS total
                FROM work_orders w
                JOIN customers c ON c.id = w.customer_id
                LEFT JOIN (
                    SELECT order_id, SUM(unit_price * quantity) AS parts
                    FROM order_items
                    WHERE status = 'APROBADO'
                    GROUP BY order_id
                ) x ON x.order_id = w.id
                WHERE w.status = 'ENTREGADO'
                  AND COALESCE(w.delivery_date, DATE(w.updated_at), w.entry_date) BETWEEN ? AND ?
                GROUP BY c.id, c.name
                ORDER BY total DESC
                LIMIT {$limit}";

        return $this->db->query($sql, [$from, $to])->getResultArray();
    }

    public function newCustomers(string $from, string $to): int
    {
        return (int) $this->db->query(
            'SELECT COUNT(*) AS n FROM customers WHERE DATE(created_at) BETWEEN ? AND ?',
            [$from, $to]
        )->getRow()->n;
    }

    public function deliveredWithoutDate(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) AS n FROM work_orders WHERE status = 'ENTREGADO' AND delivery_date IS NULL"
        )->getRow()->n;
    }
}
