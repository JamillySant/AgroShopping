<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    /**
     * Mostra o formulário de gerenciamento de clientes.
     */
    public function showForm()
    {
        // Certifique-se de que o nome do seu arquivo é 'form.blade.php'
        // ou altere aqui para o nome correto (ex: 'clientes.index').
        return view('clientes.form'); 
    }

    /**
     * Retorna todos os clientes em formato JSON para o AJAX.
     * Este é o método que estava faltando e causando o erro na tela de Vendas.
     */
    public function index()
    {
        return response()->json(Cliente::orderBy('nome')->get());
    }

    /**
     * Salva um novo cliente no banco de dados.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:clientes,email',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
        ]);

        $cliente = Cliente::create($validated);
        return response()->json($cliente, 201);
    }

    /**
     * Retorna os dados de um cliente específico para edição.
     */
    public function show($id)
    {
        return response()->json(Cliente::findOrFail($id));
    }

    /**
     * Atualiza um cliente existente.
     */
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => "required|email|unique:clientes,email,{$id}",
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
        ]);

        $cliente->update($validated);
        return response()->json($cliente);
    }

    /**
     * Exclui um cliente do banco de dados.
     */
    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();
        return response()->json(null, 204);
    }
}