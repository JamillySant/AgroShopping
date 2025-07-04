<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $table = 'produtos';
    protected $fillable = ['nome', 'tipo', 'preco', 'descricao', 'imagem'];

    public function calcularDesconto()
    {
        return 0; // genérico sem desconto
    }
}
