<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\MotorcycleModel;
use App\Models\WorkOrderModel;

class Motorcycles extends BaseController
{
    public function index()
    {
        $term = mb_strtolower(trim((string) $this->request->getGet('q')));
        $motos = (new MotorcycleModel())->withCustomer();

        if ($term !== '') {
            $motos = array_values(array_filter($motos, static function (array $moto) use ($term): bool {
                $haystack = mb_strtolower(implode(' ', [
                    uid('MOT', $moto['id']),
                    $moto['plate'] ?? '',
                    $moto['brand'] ?? '',
                    $moto['model'] ?? '',
                    $moto['customer_name'] ?? '',
                ]));

                return str_contains($haystack, $term);
            }));
        }

        return view('motorcycles/index', [
            'title' => 'Motos',
            'motos' => $motos,
            'term'  => (string) $this->request->getGet('q'),
        ]);
    }

    public function search()
    {
        $term  = trim((string) $this->request->getGet('q'));
        $limit = (int) ($this->request->getGet('limit') ?? 20);

        $items = array_map(static function (array $moto): array {
            $bike = trim(implode(' ', array_filter([$moto['brand'] ?? '', $moto['model'] ?? ''])));

            return [
                'id'          => (int) $moto['id'],
                'label'       => uid('MOT', $moto['id']) . ' · ' . ($bike !== '' ? $bike : 'Moto'),
                'input_label' => uid('MOT', $moto['id']) . ' · ' . ($bike !== '' ? $bike : 'Moto'),
                'meta'        => implode(' · ', array_filter([
                    (string) ($moto['plate'] ?? ''),
                    (string) ($moto['customer_name'] ?? ''),
                ])),
            ];
        }, (new MotorcycleModel())->search($term, $limit));

        return $this->response->setJSON(['items' => $items]);
    }

    public function show(int $id)
    {
        $moto = (new MotorcycleModel())->findWithCustomer($id);
        if (! $moto) {
            return redirect()->to('/motorcycles')->with('error', 'Moto no encontrada.');
        }

        return view('motorcycles/show', [
            'title'  => uid('MOT', $id),
            'moto'   => $moto,
            'orders' => (new WorkOrderModel())->forMotorcycle($id),
        ]);
    }

    public function new()
    {
        return view('motorcycles/form', [
            'title' => 'Nueva moto',
            'moto'  => null,
        ]);
    }

    public function create()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', 'Completá cliente, marca y modelo.');
        }

        $id = (int) (new MotorcycleModel())->insert($this->payload(), true);

        return redirect()->to('/motorcycles/' . $id)->with('success', 'Moto creada.');
    }

    public function edit(int $id)
    {
        $moto = (new MotorcycleModel())->find($id);
        if (! $moto) {
            return redirect()->to('/motorcycles')->with('error', 'Moto no encontrada.');
        }

        $customer = (new CustomerModel())->find((int) $moto['customer_id']);
        $moto['customer_name'] = $customer['name'] ?? '';

        return view('motorcycles/form', [
            'title' => 'Editar moto',
            'moto'  => $moto,
        ]);
    }

    public function update(int $id)
    {
        $moto = (new MotorcycleModel())->find($id);
        if (! $moto) {
            return redirect()->to('/motorcycles')->with('error', 'Moto no encontrada.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', 'Completá cliente, marca y modelo.');
        }

        (new MotorcycleModel())->update($id, $this->payload());

        return redirect()->to('/motorcycles/' . $id)->with('success', 'Moto actualizada.');
    }

    public function delete(int $id)
    {
        $orders = (new WorkOrderModel())->where('motorcycle_id', $id)->countAllResults();

        if ($orders > 0) {
            return redirect()->to('/motorcycles/' . $id)->with('error', 'No se puede borrar: tiene órdenes asociadas.');
        }

        (new MotorcycleModel())->delete($id);

        return redirect()->to('/motorcycles')->with('success', 'Moto eliminada.');
    }

    private function rules(): array
    {
        return [
            'customer_id' => 'required|is_natural_no_zero',
            'brand'       => 'required|max_length[80]',
            'model'       => 'required|max_length[80]',
        ];
    }

    private function payload(): array
    {
        return [
            'customer_id' => (int) $this->request->getPost('customer_id'),
            'plate'       => trim((string) $this->request->getPost('plate')),
            'brand'       => trim((string) $this->request->getPost('brand')),
            'model'       => trim((string) $this->request->getPost('model')),
            'year'        => $this->request->getPost('year') !== '' ? (int) $this->request->getPost('year') : null,
            'chassis'     => trim((string) $this->request->getPost('chassis')),
            'engine'      => trim((string) $this->request->getPost('engine')),
            'notes'       => trim((string) $this->request->getPost('notes')),
        ];
    }
}
