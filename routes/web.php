<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VendaController;

// Rota principal (Dashboard)
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Rotas para Clientes
    Route::prefix('clientes')->group(function () {
    Route::get('/view', [ClienteController::class, 'showForm'])->name('clientes.form');
    Route::get('/', [ClienteController::class, 'index'])->name('clientes.index');
    Route::post('/', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/{id}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::put('/{id}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::delete('/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
});

// Rotas para Produtos
    Route::prefix('produtos')->group(function () {
    Route::get('/view', [ProdutoController::class, 'showForm'])->name('produtos.form');
    Route::get('/', [ProdutoController::class, 'index'])->name('produtos.index');
    Route::post('/', [ProdutoController::class, 'store'])->name('produtos.store');
    Route::get('/{id}/desconto', [ProdutoController::class, 'aplicarDesconto'])->name('produtos.desconto');
    Route::put('/{id}', [ProdutoController::class, 'update'])->name('produtos.update');
    Route::delete('/{id}', [ProdutoController::class, 'destroy'])->name('produtos.destroy');
});

// Rotas para Vendas
    Route::prefix('vendas')->group(function () {
    Route::get('/view', function () {
        return view('vendas.index');
    })->name('vendas.form');
    Route::get('/', [VendaController::class, 'index'])->name('vendas.index');
    Route::post('/', [VendaController::class, 'store'])->name('vendas.store');
    Route::get('/{id}', [VendaController::class, 'show'])->name('vendas.show');
    Route::put('/{id}', [VendaController::class, 'update'])->name('vendas.update');
    Route::delete('/{id}', [VendaController::class, 'destroy'])->name('vendas.destroy');
});