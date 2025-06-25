<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('item_vendas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('venda_id')->constrained()->onDelete('cascade');
        $table->foreignId('produto_id')->constrained()->onDelete('cascade');
        $table->integer('quantidade');
        $table->decimal('preco_unitario', 10, 2);
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('item_vendas');
    }
};
