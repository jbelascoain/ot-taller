<?php

namespace App\Controllers;

use App\Models\PartModel;
use App\Models\SupplierModel;

class Suppliers extends BaseController
{
    public function index()
    {
        $term = trim((string) $this->request->getGet('q'));

        return view('suppliers/index', [
            'title'     => 'Proveedores',
            'suppliers' => (new SupplierModel())->search($term),
            'term'      => $term,
        ]);
    }

    public function show(int $id)
    {
        $supplier = (new SupplierModel())->find($id);
        if (! $supplier) {
            return redirect()->to('/suppliers')->with('error', 'Proveedor no encontrado.');
        }

        $parts = db_connect()->table('parts')->where('supplier_id', $id)->orderBy('description', 'ASC')->get()->getResultArray();

        return view('suppliers/show', [
            'title'    => $supplier['name'],
            'supplier' => $supplier,
            'parts'    => $parts,
        ]);
    }

    public function new()
    {
        return view('suppliers/form', ['title' => 'Nuevo proveedor', 'supplier' => null]);
    }

    public function create()
    {
        if (! $this->validate(['name' => 'required|max_length[160]'])) {
            return redirect()->back()->withInput()->with('error', 'El nombre es obligatorio.');
        }

        $id = (int) (new SupplierModel())->insert($this->payload(), true);

        return redirect()->to('/suppliers/' . $id)->with('success', 'Proveedor creado.');
    }

    public function edit(int $id)
    {
        $supplier = (new SupplierModel())->find($id);
        if (! $supplier) {
            return redirect()->to('/suppliers')->with('error', 'Proveedor no encontrado.');
        }

        return view('suppliers/form', ['title' => 'Editar proveedor', 'supplier' => $supplier]);
    }

    public function update(int $id)
    {
        $supplier = (new SupplierModel())->find($id);
        if (! $supplier) {
            return redirect()->to('/suppliers')->with('error', 'Proveedor no encontrado.');
        }

        if (! $this->validate(['name' => 'required|max_length[160]'])) {
            return redirect()->back()->withInput()->with('error', 'El nombre es obligatorio.');
        }

        (new SupplierModel())->update($id, $this->payload());

        return redirect()->to('/suppliers/' . $id)->with('success', 'Proveedor actualizado.');
    }

    public function delete(int $id)
    {
        $parts = (new PartModel())->where('supplier_id', $id)->countAllResults();

        if ($parts > 0) {
            return redirect()->to('/suppliers/' . $id)->with('error', 'No se puede borrar: tiene repuestos asociados.');
        }

        (new SupplierModel())->delete($id);

        return redirect()->to('/suppliers')->with('success', 'Proveedor eliminado.');
    }

    private function payload(): array
    {
        return [
            'name'    => trim((string) $this->request->getPost('name')),
            'contact' => trim((string) $this->request->getPost('contact')),
            'phone'   => trim((string) $this->request->getPost('phone')),
            'email'   => trim((string) $this->request->getPost('email')),
            'notes'   => trim((string) $this->request->getPost('notes')),
        ];
    }
}
