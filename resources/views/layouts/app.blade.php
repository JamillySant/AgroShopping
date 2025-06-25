<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Agropecuária - @yield('title', 'Início')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    @yield('styles')

    @if(Route::is('dashboard'))
    <style>
        /* MUDANÇA 1: Aplicando o fundo diretamente no corpo da página */
        body {
            background-image: url("{{ asset('images/dashboard.jpg') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed; /* Deixa o fundo fixo ao rolar */
        }

        /* MUDANÇA 2: Deixando a área de conteúdo <main> transparente para o fundo aparecer */
        main.py-4 {
            background-color: transparent; 
        }
    </style>
    @endif

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">🌾 Produtos Agropecuários</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">🏛️​​ Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('clientes.form') }}">👥 Funcionários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('produtos.form') }}">🌽 Produtos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('vendas.form') }}">🛒 Vendas</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    @yield('scripts')
</body>
</html>