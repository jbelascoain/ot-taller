<?php

namespace App\Controllers;

use App\Models\MetricsModel;
use App\Models\WorkOrderModel;
use DateTimeImmutable;

class Metrics extends BaseController
{
    private const PRESETS = ['7d', '30d', 'month', 'prev_month', 'year', 'custom'];

    public function index()
    {
        if (! is_admin()) {
            return redirect()->to('/')->with('error', 'Acceso restringido.');
        }

        [$from, $to, $preset] = $this->resolvePeriod();
        [$prevFrom, $prevTo]   = $this->previousRange($from, $to);

        $model = new MetricsModel();

        $current  = $this->summary($model, $from, $to);
        $previous = $this->summary($model, $prevFrom, $prevTo);

        $trends = [
            'revenue'   => $this->trend($current['revenue'], $previous['revenue']),
            'delivered' => $this->trend($current['delivered'], $previous['delivered']),
            'ticket'    => $this->trend($current['ticket'], $previous['ticket']),
            'margin'    => $this->trend($current['margin'], $previous['margin']),
            'created'   => $this->trend($current['created'], $previous['created']),
            'approval'  => $this->trend($current['approval'], $previous['approval']),
            'avg_days'  => $this->trend($current['avg_days'], $previous['avg_days'], true),
        ];

        $statusCounts = $model->statusCounts();
        $statuses     = [];

        foreach (WorkOrderModel::STATUSES as $status) {
            $statuses[] = [
                'label' => estado_label($status),
                'count' => (int) ($statusCounts[$status] ?? 0),
                'class' => estado_class($status),
            ];
        }

        $pending      = $model->pendingQuotes();
        $pendingTotal = 0.0;

        foreach ($pending as &$row) {
            $days = (int) floor((time() - strtotime((string) $row['entry_date'])) / 86400);
            $row['days'] = max(0, $days);
            $row['uid']  = uid('OT', $row['id']);
            $row['total'] = (float) $row['total'];
            $pendingTotal += $row['total'];
        }
        unset($row);

        $lowStock       = $model->lowStock();
        $inventoryValue = $model->inventoryValue();

        $topParts = array_map(static function (array $part): array {
            $description = (string) $part['description'];

            return [
                'label'  => mb_strlen($description) > 38 ? mb_substr($description, 0, 37) . '…' : $description,
                'full'   => $description,
                'qty'    => (int) $part['qty'],
                'amount' => (float) $part['amount'],
            ];
        }, $model->topParts($from, $to, 10));

        $topCustomers = $model->topCustomers($from, $to, 5);

        return view('metrics/index', [
            'title'         => 'Métricas',
            'from'          => $from,
            'to'            => $to,
            'preset'        => $preset,
            'prevFrom'      => $prevFrom,
            'prevTo'        => $prevTo,
            'current'       => $current,
            'previous'      => $previous,
            'trends'        => $trends,
            'series'        => $this->buildSeries($model, $from, $to),
            'statuses'      => $statuses,
            'pending'       => $pending,
            'pendingTotal'  => $pendingTotal,
            'lowStock'      => $lowStock,
            'inventoryValue' => $inventoryValue,
            'topParts'      => $topParts,
            'topCustomers'  => $topCustomers,
            'newCustomers'  => $model->newCustomers($from, $to),
            'missingDates'  => $model->deliveredWithoutDate(),
        ]);
    }

    private function resolvePeriod(): array
    {
        $preset = (string) ($this->request->getGet('preset') ?: '30d');

        if (! in_array($preset, self::PRESETS, true)) {
            $preset = '30d';
        }

        $today = new DateTimeImmutable('today');

        switch ($preset) {
            case '7d':
                $from = $today->modify('-6 days');
                $to   = $today;
                break;

            case 'month':
                $from = $today->modify('first day of this month');
                $to   = $today;
                break;

            case 'prev_month':
                $from = $today->modify('first day of previous month');
                $to   = $today->modify('last day of previous month');
                break;

            case 'year':
                $from = $today->modify('first day of January');
                $to   = $today;
                break;

            case 'custom':
                $from = $this->parseDate((string) $this->request->getGet('from'));
                $to   = $this->parseDate((string) $this->request->getGet('to'));

                if (! $from || ! $to) {
                    $preset = '30d';
                    $from   = $today->modify('-29 days');
                    $to     = $today;
                } elseif ($from > $to) {
                    [$from, $to] = [$to, $from];
                }
                break;

            default:
                $preset = '30d';
                $from   = $today->modify('-29 days');
                $to     = $today;
        }

        return [$from->format('Y-m-d'), $to->format('Y-m-d'), $preset];
    }

    private function parseDate(string $value): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if (! $date instanceof DateTimeImmutable || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $date;
    }

    private function previousRange(string $from, string $to): array
    {
        $start = new DateTimeImmutable($from);
        $end   = new DateTimeImmutable($to);
        $days  = (int) $start->diff($end)->days + 1;

        $prevEnd   = $start->modify('-1 day');
        $prevStart = $prevEnd->modify('-' . ($days - 1) . ' days');

        return [$prevStart->format('Y-m-d'), $prevEnd->format('Y-m-d')];
    }

    private function summary(MetricsModel $model, string $from, string $to): array
    {
        $delivered = $model->deliveredSummary($from, $to);
        $approval  = $model->approvalTotals($from, $to);
        $decided   = $approval['approved'] + $approval['rejected'];

        return [
            'created'   => $model->createdCount($from, $to),
            'delivered' => $delivered['count'],
            'revenue'   => $delivered['revenue'],
            'labor'     => $delivered['labor'],
            'parts'     => $delivered['parts'],
            'ticket'    => $delivered['ticket'],
            'margin'    => $model->marginTotal($from, $to),
            'approval'  => $decided > 0 ? ($approval['approved'] / $decided) * 100 : null,
            'avg_days'  => $delivered['avg_days'],
        ];
    }

    private function trend(?float $current, ?float $previous, bool $lowerIsBetter = false): ?array
    {
        if ($current === null || $previous === null || abs($previous) < 0.00001) {
            return null;
        }

        $pct = (($current - $previous) / abs($previous)) * 100;
        $dir = $pct > 0.05 ? 'up' : ($pct < -0.05 ? 'down' : 'flat');

        return [
            'pct'  => $pct,
            'dir'  => $dir,
            'good' => $dir === 'flat' ? null : ($lowerIsBetter ? $dir === 'down' : $dir === 'up'),
        ];
    }

    private function buildSeries(MetricsModel $model, string $from, string $to): array
    {
        $start = new DateTimeImmutable($from);
        $end   = new DateTimeImmutable($to);
        $days  = (int) $start->diff($end)->days + 1;
        $gran  = $days <= 31 ? 'day' : ($days <= 120 ? 'week' : 'month');

        $keys   = [];
        $labels = [];

        if ($gran === 'day') {
            for ($date = $start; $date <= $end; $date = $date->modify('+1 day')) {
                $keys[]   = $date->format('Y-m-d');
                $labels[] = $date->format('d/m');
            }
        } elseif ($gran === 'week') {
            $date = $start->modify('monday this week');

            while ($date <= $end) {
                $keys[]   = $date->format('Y-m-d');
                $labels[] = 'Sem ' . $date->format('d/m');
                $date     = $date->modify('+7 days');
            }
        } else {
            $date = $start->modify('first day of this month');

            while ($date <= $end) {
                $keys[]   = $date->format('Y-m');
                $labels[] = $this->monthLabel($date);
                $date     = $date->modify('+1 month');
            }
        }

        $created   = array_fill_keys($keys, 0);
        $delivered = array_fill_keys($keys, 0);
        $labor     = array_fill_keys($keys, 0.0);
        $parts     = array_fill_keys($keys, 0.0);

        foreach ($model->dailyCreated($from, $to) as $row) {
            $key = $this->bucketKey((string) $row['d'], $gran);

            if (isset($created[$key])) {
                $created[$key] += (int) $row['n'];
            }
        }

        foreach ($model->deliveredRows($from, $to) as $row) {
            $key = $this->bucketKey((string) $row['delivered_on'], $gran);

            if (! isset($delivered[$key])) {
                continue;
            }

            $delivered[$key]++;
            $labor[$key] += (float) $row['labor'];
            $parts[$key] += (float) $row['parts_total'];
        }

        return [
            'granularity' => $gran,
            'labels'      => $labels,
            'created'     => array_values($created),
            'delivered'   => array_values($delivered),
            'labor'       => array_values($labor),
            'parts'       => array_values($parts),
        ];
    }

    private function bucketKey(string $date, string $granularity): string
    {
        if ($granularity === 'day') {
            return $date;
        }

        $parsed = new DateTimeImmutable($date);

        if ($granularity === 'week') {
            return $parsed->modify('monday this week')->format('Y-m-d');
        }

        return $parsed->format('Y-m');
    }

    private function monthLabel(DateTimeImmutable $date): string
    {
        $months = [
            1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun',
            7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic',
        ];

        return $months[(int) $date->format('n')] . ' ' . $date->format('Y');
    }
}
