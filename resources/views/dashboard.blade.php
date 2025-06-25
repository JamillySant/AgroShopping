@extends('layouts.app')

@section('title', 'Bem-vindo')

@section('styles')
<style>
    /* Usa flexbox para centralizar o conteúdo do dashboard na vertical */
    main.py-4 {
        display: flex;
        align-items: center; /* Alinha verticalmente */
        justify-content: center; /* Alinha horizontalmente */
    }

    /* Estilo para o texto do dashboard */
    .dashboard-text-content h1, .dashboard-text-content p {
        color: white; /* Cor do texto branca */
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7); /* Sombra para legibilidade */
    }

    .dashboard-text-content h1 {
        font-size: 4.5rem;
        font-weight: bold;
    }

    .dashboard-text-content p {
        font-size: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="text-center dashboard-text-content">
    <h1>🌾 AgroShopping</h1>
    <p class="lead">Escolha um módulo para gerenciar</p>
</div>
@endsection