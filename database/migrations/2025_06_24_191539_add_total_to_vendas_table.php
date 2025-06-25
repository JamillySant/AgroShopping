<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            // Adiciona a coluna 'total' depois da coluna 'data'
            $table->decimal('total', 10, 2)->after('data');
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            // Remove a coluna caso precise reverter a migração
            $table->dropColumn('total');
        });
    }
};