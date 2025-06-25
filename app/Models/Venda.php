<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    /**
     * Os campos que podem ser preenchidos em massa.
     * O erro anterior acontecia porque 'cliente_id' provavelmente não estava aqui.
     */
    protected $fillable = [
        'cliente_id',
        'data',
        'total'
    ];

    /**
     * Define o relacionamento: Uma Venda pertence a um Cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Define o relacionamento: Uma Venda tem muitos Itens.
     */
    public function itens()
    {
        return $this->hasMany(ItemVenda::class);
    }
}