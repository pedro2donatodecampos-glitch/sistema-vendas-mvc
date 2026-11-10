<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Models\Customer;
use App\Repositories\CustomerRepository;

/**
 * Controller da tela "Cadastro de clientes" (CRUD completo).
 */
final class CustomerController
{
    private CustomerRepository $customers;

    public function __construct()
    {
        $this->customers = new CustomerRepository(Database::connection());
    }

    public function index(): void
    {
        View::render('customers/index', [
            'title' => 'Clientes',
            'customers' => $this->customers->all(),
        ]);
    }

    public function create(): void
    {
        View::render('customers/form', ['title' => 'Novo cliente', 'customer' => null]);
    }

    public function edit(string $id): void
    {
        $customer = $this->customers->find((int) $id);
        if (!$customer) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Cliente não encontrado']);
            return;
        }

        View::render('customers/form', ['title' => 'Editar cliente', 'customer' => $customer]);
    }

    public function store(): void
    {
        $this->save();
    }

    public function update(string $id): void
    {
        if (!$this->customers->find((int) $id)) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Cliente não encontrado']);
            return;
        }
        $this->save((int) $id);
    }

    public function delete(string $id): void
    {
        $this->customers->delete((int) $id);
        flash('success', 'Cliente removido com sucesso.');
        redirect('/customers');
    }

    private function save(?int $id = null): void
    {
        $customer = Customer::fromInput($_POST);
        $error = $customer->validate();
        if ($error !== null) {
            flash('error', $error);
            redirect($id === null ? '/customers/create' : '/customers/' . $id . '/edit');
        }

        $this->customers->save($customer, $id);

        flash('success', $id === null ? 'Cliente cadastrado com sucesso.' : 'Cliente atualizado com sucesso.');
        redirect('/customers');
    }
}
