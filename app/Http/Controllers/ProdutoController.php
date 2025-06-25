<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Importante para gerenciar arquivos
use App\Models\Produto;

// Importa as classes de produtos para o polimorfismo
use App\Models\Produtos\Racao;
use App\Models\Produtos\Agrotoxico;
use App\Models\Produtos\Semente;
use App\Models\Produtos\Ferramenta;
use App\Models\Produtos\ProdutoVeterinario;
use App\Models\Produtos\Animal;

class ProdutoController extends Controller
{
    /**
     * Mostra o formulário de gerenciamento de produtos.
     */
    public function showForm()
    {
        return view('produtos.index');
    }

    /**
     * Retorna a lista de produtos, com filtro opcional por tipo.
     * Usado pelo AJAX para popular a grade de produtos.
     */
    public function index(Request $request)
    {
        $query = Produto::query();

        // Se um 'tipo' foi enviado na requisição e não é 'todos', aplica o filtro.
        if ($request->has('tipo') && $request->input('tipo') !== 'todos') {
            $query->where('tipo', $request->input('tipo'));
        }

        $produtos = $query->latest()->get(); // 'latest()' ordena os mais recentes primeiro
        return response()->json($produtos);
    }

    /**
     * Salva um novo produto no banco de dados, incluindo a imagem se enviada.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|max:100',
            'preco' => 'required|numeric|min:0',
            'descricao' => 'nullable|string|max:1000',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048' // Validação da imagem
        ]);

        if ($request->hasFile('imagem')) {
            // Salva a imagem na pasta 'public/produtos' e armazena o caminho
            $validated['imagem'] = $request->file('imagem')->store('produtos', 'public');
        }

        $produto = Produto::create($validated);
        return response()->json($produto, 201);
    }

    /**
     * Atualiza um produto existente. Se uma nova imagem for enviada,
     * apaga a antiga e salva a nova.
     */
    public function update(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|max:100',
            'preco' => 'required|numeric|min:0',
            'descricao' => 'nullable|string|max:1000',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        if ($request->hasFile('imagem')) {
            // Se já existe uma imagem antiga, apaga ela do armazenamento
            if ($produto->imagem) {
                Storage::disk('public')->delete($produto->imagem);
            }
            // Salva a nova imagem e atualiza o caminho no array validado
            $validated['imagem'] = $request->file('imagem')->store('produtos', 'public');
        }

        $produto->update($validated);
        return response()->json($produto);
    }

    /**
     * Remove um produto e sua imagem associada do armazenamento.
     */
    public function destroy($id)
    {
        $produto = Produto::findOrFail($id);

        // Apaga a imagem do armazenamento se ela existir
        if ($produto->imagem) {
            Storage::disk('public')->delete($produto->imagem);
        }

        $produto->delete();
        return response()->json(null, 204);
    }
    
    /**
     * Calcula o desconto de um produto com base no seu tipo (polimorfismo).
     */
    public function aplicarDesconto($id)
    {
        $produto = Produto::find($id);
        if (!$produto) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        // Lógica de polimorfismo com as novas classes
        $classe = match($produto->tipo) {
            'agrotoxicos' => new Agrotoxico(),
            'racao' => new Racao(),
            'sementes' => new Semente(),
            'ferramentas' => new Ferramenta(),
            'produtos_veterinarios' => new ProdutoVeterinario(),
            'animais' => new Animal(),
            default => new Produto(),
        };

        $classe->fill($produto->toArray());
        $desconto = $classe->calcularDesconto();

        return response()->json([
            'produto' => $produto->nome,
            'preco' => $produto->preco,
            'desconto_valor' => $desconto,
            'preco_com_desconto' => $produto->preco - $desconto,
        ]);
    }
}