<?php

namespace App\Controllers;

use App\Models\PartModel;
use App\Models\SupplierModel;

class Parts extends BaseController
{
    public function index()
    {
        $term  = mb_strtolower(trim((string) $this->request->getGet('q')));
        $parts = (new PartModel())->withSupplier();

        if ($term !== '') {
            $parts = array_values(array_filter($parts, static function (array $part) use ($term): bool {
                $haystack = mb_strtolower(implode(' ', [
                    uid('REP', $part['id']),
                    $part['description'] ?? '',
                    $part['sku'] ?? '',
                    $part['brand'] ?? '',
                    $part['supplier_name'] ?? '',
                ]));

                return str_contains($haystack, $term);
            }));
        }

        return view('parts/index', [
            'title' => 'Repuestos',
            'parts' => $parts,
            'term'  => (string) $this->request->getGet('q'),
        ]);
    }

    public function search()
    {
        $term  = trim((string) $this->request->getGet('q'));
        $limit = (int) ($this->request->getGet('limit') ?? 20);

        $items = array_map(static function (array $part): array {
            $meta = implode(' · ', array_filter([
                uid('REP', $part['id']),
                (string) ($part['brand'] ?? ''),
                (string) ($part['sku'] ?? ''),
            ]));

            return [
                'id'          => (int) $part['id'],
                'uid'         => uid('REP', $part['id']),
                'label'       => $part['description'],
                'input_label' => $part['description'] . (! empty($part['sku']) ? ' · ' . $part['sku'] : ''),
                'meta'        => $meta,
                'description' => $part['description'],
                'brand'       => (string) ($part['brand'] ?? ''),
                'sku'         => (string) ($part['sku'] ?? ''),
                'price'       => (float) $part['sale_price'],
                'price_label' => money($part['sale_price']),
                'stock'       => (int) $part['stock'],
            ];
        }, (new PartModel())->search($term, $limit));

        return $this->response->setJSON(['items' => $items]);
    }

    public function new()
    {
        return view('parts/form', [
            'title'     => 'Nuevo repuesto',
            'part'      => null,
            'suppliers' => (new SupplierModel())->options(),
        ]);
    }

    public function create()
    {
        if (! $this->validate(['description' => 'required|max_length[200]'])) {
            return redirect()->back()->withInput()->with('error', 'La descripción es obligatoria.');
        }

        $id = (int) (new PartModel())->insert($this->payload(), true);

        return redirect()->to('/parts')->with('success', 'Repuesto creado.');
    }

    public function edit(int $id)
    {
        $part = (new PartModel())->find($id);
        if (! $part) {
            return redirect()->to('/parts')->with('error', 'Repuesto no encontrado.');
        }

        return view('parts/form', [
            'title'     => 'Editar repuesto',
            'part'      => $part,
            'suppliers' => (new SupplierModel())->options(),
        ]);
    }

    public function update(int $id)
    {
        $part = (new PartModel())->find($id);
        if (! $part) {
            return redirect()->to('/parts')->with('error', 'Repuesto no encontrado.');
        }

        if (! $this->validate(['description' => 'required|max_length[200]'])) {
            return redirect()->back()->withInput()->with('error', 'La descripción es obligatoria.');
        }

        (new PartModel())->update($id, $this->payload());

        return redirect()->to('/parts')->with('success', 'Repuesto actualizado.');
    }

    public function toggle(int $id)
    {
        $part = (new PartModel())->find($id);
        if (! $part) {
            return redirect()->to('/parts')->with('error', 'Repuesto no encontrado.');
        }

        (new PartModel())->update($id, ['active' => (int) $part['active'] === 1 ? 0 : 1]);

        return redirect()->to('/parts')->with('success', 'Estado del repuesto actualizado.');
    }

    public function delete(int $id)
    {
        $used = db_connect()->table('order_items')->where('part_id', $id)->countAllResults();

        if ($used > 0) {
            return redirect()->to('/parts')->with('error', 'No se puede borrar: está usado en órdenes. Podés desactivarlo.');
        }

        (new PartModel())->delete($id);

        return redirect()->to('/parts')->with('success', 'Repuesto eliminado.');
    }

    private function payload(): array
    {
        $num = static function ($value): float {
            return is_numeric($value) ? (float) $value : 0;
        };

        return [
            'description'    => trim((string) $this->request->getPost('description')),
            'sku'            => trim((string) $this->request->getPost('sku')),
            'brand'          => trim((string) $this->request->getPost('brand')),
            'purchase_price' => $num($this->request->getPost('purchase_price')),
            'sale_price'     => $num($this->request->getPost('sale_price')),
            'stock'          => (int) $this->request->getPost('stock'),
            'min_stock'      => (int) $this->request->getPost('min_stock'),
            'supplier_id'    => $this->request->getPost('supplier_id') ?: null,
            'active'         => $this->request->getPost('active') === '1' ? 1 : 0,
            'notes'          => trim((string) $this->request->getPost('notes')),
        ];
    }
}
