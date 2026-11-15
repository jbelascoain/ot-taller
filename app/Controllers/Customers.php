<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\MotorcycleModel;
use App\Models\WorkOrderModel;

class Customers extends BaseController
{
    public function index()
    {
        $term = trim((string) $this->request->getGet('q'));

        return view('customers/index', [
            'title'     => 'Clientes',
            'customers' => (new CustomerModel())->search($term),
            'term'      => $term,
        ]);
    }

    public function show(int $id)
    {
        $customer = (new CustomerModel())->find($id);
        if (! $customer) {
            return redirect()->to('/customers')->with('error', 'Cliente no encontrado.');
        }

        return view('customers/show', [
            'title'    => $customer['name'],
            'customer' => $customer,
            'motos'    => (new MotorcycleModel())->forCustomer($id),
            'orders'   => (new WorkOrderModel())->forCustomer($id),
        ]);
    }

    public function search()
    {
        $term  = trim((string) $this->request->getGet('q'));
        $limit = (int) ($this->request->getGet('limit') ?? 20);

        $items = array_map(static function (array $customer): array {
            return [
                'id'          => (int) $customer['id'],
                'label'       => $customer['name'],
                'input_label' => $customer['name'],
                'meta'        => implode(' · ', array_filter([
                    (string) ($customer['phone'] ?? ''),
                    (string) ($customer['email'] ?? ''),
                ])),
            ];
        }, (new CustomerModel())->searchLimited($term, $limit));

        return $this->response->setJSON(['items' => $items]);
    }

    public function new()
    {
        return view('customers/form', ['title' => 'Nuevo cliente', 'customer' => null]);
    }

    public function create()
    {
        $data = $this->payload();

        if (! $this->validate(['name' => 'required|max_length[160]'])) {
            return redirect()->back()->withInput()->with('error', 'El nombre es obligatorio.');
        }

        $id = (int) (new CustomerModel())->insert($data, true);

        return redirect()->to('/customers/' . $id)->with('success', 'Cliente creado.');
    }

    public function edit(int $id)
    {
        $customer = (new CustomerModel())->find($id);
        if (! $customer) {
            return redirect()->to('/customers')->with('error', 'Cliente no encontrado.');
        }

        return view('customers/form', ['title' => 'Editar cliente', 'customer' => $customer]);
    }

    public function update(int $id)
    {
        $customer = (new CustomerModel())->find($id);
        if (! $customer) {
            return redirect()->to('/customers')->with('error', 'Cliente no encontrado.');
        }

        if (! $this->validate(['name' => 'required|max_length[160]'])) {
            return redirect()->back()->withInput()->with('error', 'El nombre es obligatorio.');
        }

        (new CustomerModel())->update($id, $this->payload());

        return redirect()->to('/customers/' . $id)->with('success', 'Cliente actualizado.');
    }

    public function delete(int $id)
    {
        $orders = (new WorkOrderModel())->where('customer_id', $id)->countAllResults();
        $motos  = (new MotorcycleModel())->where('customer_id', $id)->countAllResults();

        if ($orders > 0 || $motos > 0) {
            return redirect()->to('/customers/' . $id)->with('error', 'No se puede borrar: tiene motos u órdenes asociadas.');
        }

        (new CustomerModel())->delete($id);

        return redirect()->to('/customers')->with('success', 'Cliente eliminado.');
    }

    private function payload(): array
    {
        return [
            'name'  => trim((string) $this->request->getPost('name')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'email' => trim((string) $this->request->getPost('email')),
            'notes' => trim((string) $this->request->getPost('notes')),
        ];
    }
}
