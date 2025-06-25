<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendaController extends Controller
{
    public function showForm()
    {
        return view('vendas.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'data' => 'required|date',
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1',
        ]);

        // DB::transaction garante que se algo der errado, nada é salvo.
        $venda = DB::transaction(function () use ($validated) {
            // Pega todos os produtos de uma vez para calcular o total
            $produtos = Produto::find(array_column($validated['itens'], 'produto_id'));
            $total = 0;

            foreach ($validated['itens'] as $item) {
                $produto = $produtos->firstWhere('id', $item['produto_id']);
                if ($produto) {
                    $total += $produto->preco * $item['quantidade'];
                }
            }

            // Cria a venda principal
            $venda = Venda::create([
                'cliente_id' => $validated['cliente_id'],
                'data' => $validated['data'],
                'total' => $total,
            ]);

            // Cria os itens da venda, um por um
            foreach ($validated['itens'] as $item) {
                $produto = $produtos->firstWhere('id', $item['produto_id']);
                $venda->itens()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $produto->preco,
                ]);
            }
            return $venda;
        });

        // Retorna a venda completa com os relacionamentos para a nota fiscal
        return response()->json($venda->load(['cliente', 'itens.produto']), 201);
    }

    // Este método busca uma única venda e era um dos que faltava
    public function show($id)
    {
        return Venda::with(['cliente', 'itens.produto'])->findOrFail($id);
    }
}