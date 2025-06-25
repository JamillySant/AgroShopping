@extends('layouts.app')

@section('title', 'Gerenciador de Produtos')

@section('styles')
<style>
    /* Estilos da Grade */
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; }
    .product-card { border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; transition: box-shadow 0.3s ease, transform 0.3s ease; background-color: #fff; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 6px 16px rgba(0,0,0,0.12); }
    .product-card .img-container { width: 100%; padding-top: 100%; position: relative; background-color: #f5f5f5; }
    .product-card .img-container img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
    .product-card .info { padding: 0.8rem; text-align: left; display: flex; flex-direction: column; flex-grow: 1; }
    .product-card .info h5 { font-size: 0.95rem; font-weight: 600; margin-bottom: 0.4rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .product-card .info .price { font-size: 1.1rem; font-weight: bold; color: #28a745; margin-bottom: 0.8rem; }
    .product-card .actions { margin-top: auto; display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;}
    .product-card .actions .btn { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
    
    /* Estilos para o filtro */
    .filter-buttons .btn { margin-right: 0.5rem; margin-bottom: 0.5rem; }
    .filter-buttons .btn.active { background-color: #198754; border-color: #198754; color: white; }

    /* Estilos para o Modal */
    #modalImagem { max-height: 400px; width: 100%; object-fit: contain; }
</style>
@endsection

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Gerenciar Produtos</h2>

    <form id="formProduto" class="row g-3 mb-5 p-4 border rounded bg-light" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" id="produtoId">
        <div class="col-md-6"><label for="nome" class="form-label"><strong>Nome do Produto</strong></label><input type="text" name="nome" id="nome" class="form-control" required></div>
        <div class="col-md-6"><label for="tipo" class="form-label"><strong>Tipo</strong></label><select name="tipo" id="tipo" class="form-select" required><option value="" disabled selected>Selecione um tipo</option><option value="agrotoxicos">Agrotóxicos</option><option value="racao">Ração</option><option value="sementes">Sementes</option><option value="ferramentas">Ferramentas</option><option value="produtos_veterinarios">Produtos Veterinários</option><option value="animais">Animais</option></select></div>
        <div class="col-md-6"><label for="preco" class="form-label"><strong>Preço (R$)</strong></label><input type="number" step="0.01" name="preco" id="preco" class="form-control" required></div>
        <div class="col-md-6"><label for="imagem" class="form-label"><strong>Imagem do Produto</strong></label><input type="file" name="imagem" id="imagem" class="form-control"></div>
        <div class="col-md-12"><label for="descricao" class="form-label"><strong>Descrição (Opcional)</strong></label><input type="text" name="descricao" id="descricao" class="form-control"></div>
        <div class="col-md-12 mt-3"><button type="submit" class="btn btn-success">Salvar Produto</button><button type="button" id="btnLimpar" class="btn btn-secondary">Limpar</button></div>
    </form>

    <h3 class="mt-5 border-bottom pb-2 mb-4">Catálogo de Produtos</h3>

    <div class="filter-buttons mb-4">
        <button class="btn btn-outline-success btn-filter active" data-tipo="todos">Todos</button>
        <button class="btn btn-outline-success btn-filter" data-tipo="agrotoxicos">Agrotóxicos</button>
        <button class="btn btn-outline-success btn-filter" data-tipo="racao">Ração</button>
        <button class="btn btn-outline-success btn-filter" data-tipo="sementes">Sementes</button>
        <button class="btn btn-outline-success btn-filter" data-tipo="ferramentas">Ferramentas</button>
        <button class="btn btn-outline-success btn-filter" data-tipo="produtos_veterinarios">Prod. Veterinários</button>
        <button class="btn btn-outline-success btn-filter" data-tipo="animais">Animais</button>
    </div>

    <div id="lista-produtos-grid" class="product-grid"></div>

    <div class="modal fade" id="produtoModal" tabindex="-1" aria-labelledby="produtoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0"><h5 class="modal-title" id="modalNome"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 text-center"><img id="modalImagem" src="" class="img-fluid rounded" alt="Imagem do Produto"></div>
                        <div class="col-md-6 d-flex flex-column"><h3 class="text-success fw-bold" id="modalPreco"></h3><p id="modalDescricao" class="mt-3 flex-grow-1"></p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function limparFormulario() {
        $('#formProduto')[0].reset();
        $('#produtoId').val('');
    }

    function listarProdutos(tipo = 'todos') {
        const url = new URL("{{ route('produtos.index') }}");
        url.searchParams.append('tipo', tipo);
        $.get(url.toString(), function (produtos) {
            const grid = $('#lista-produtos-grid');
            grid.empty();
            if (produtos.length === 0) {
                grid.html('<p class="col-12 text-center">Nenhum produto encontrado para esta categoria.</p>');
                return;
            }
            produtos.forEach(produto => {
                const imageUrl = produto.imagem ? `{{ asset('storage') }}/${produto.imagem}` : `https://placehold.co/400x400/eee/ccc?text=Sem+Imagem`;
                const cardHtml = `<div class="product-card"><div class="img-container"><img src="${imageUrl}" alt="${produto.nome}" onerror="this.onerror=null;this.src='https://placehold.co/400x400/eee/ccc?text=Erro';"></div><div class="info"><h5>${produto.nome}</h5><div class="price">R$ ${parseFloat(produto.preco).toFixed(2)}</div><div class="actions"><button class="btn btn-info btn-sm btn-ver-detalhes" data-produto='${JSON.stringify(produto)}'>Detalhes</button><button class="btn btn-warning btn-sm btn-editar" data-produto='${JSON.stringify(produto)}'>Editar</button><button class="btn btn-danger btn-sm" onclick="remover(${produto.id})">Excluir</button></div></div></div>`;
                grid.append(cardHtml);
            });
        });
    }

    function remover(id) {
        if (confirm('Tem certeza que deseja excluir este produto? A imagem também será removida.')) {
            $.ajax({
                url: `/produtos/${id}`,
                type: 'DELETE',
                success: () => listarProdutos($('.btn-filter.active').data('tipo')),
                error: () => alert('Erro ao excluir o produto.')
            });
        }
    }
    window.remover = remover;

    $(document).ready(function () {
        listarProdutos('todos');
        $('#btnLimpar').click(limparFormulario);

        // MUDANÇA 2: Adicionado log para depuração no início do evento
        $('#formProduto').submit(function (e) {
            e.preventDefault();
            console.log("Evento de 'submit' disparado. O JavaScript está tentando enviar o formulário...");

            const form = this;
            const submitButton = $(this).find('button[type="submit"]');
            let id = $('#produtoId').val();
            let url = id ? `/produtos/${id}` : "{{ route('produtos.store') }}";

            submitButton.prop('disabled', true).text('Salvando...');

            let formData = new FormData(form);
            if (id) {
                formData.append('_method', 'PUT');
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: () => {
                    console.log("Sucesso! Produto salvo.");
                    limparFormulario();
                    listarProdutos($('.btn-filter.active').data('tipo'));
                },
                error: (xhr) => {
                    console.error("FALHA no AJAX:", xhr); // Log detalhado do objeto de erro
                    let errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                    let errorMsg = 'Ocorreu um erro:\n';
                    if(errors) {
                        for (let key in errors) {
                            errorMsg += `- ${errors[key][0]}\n`;
                        }
                    } else {
                        errorMsg = 'Um erro inesperado ocorreu. Verifique o Console para detalhes técnicos.'
                    }
                    alert(errorMsg);
                },
                complete: () => submitButton.prop('disabled', false).text('Salvar Produto')
            });
        });

        $('.btn-filter').on('click', function() {
            $('.btn-filter').removeClass('active');
            $(this).addClass('active');
            const tipo = $(this).data('tipo');
            listarProdutos(tipo);
        });

        $(document).on('click', '.btn-ver-detalhes', function () {
            const produto = $(this).data('produto');
            const imageUrl = produto.imagem ? `{{ asset('storage') }}/${produto.imagem}` : `https://placehold.co/600x600/eee/ccc?text=Sem+Imagem`;
            $('#modalNome').text(produto.nome);
            $('#modalImagem').attr('src', imageUrl);
            $('#modalDescricao').text(produto.descricao || 'Produto sem descrição.');
            $('#modalPreco').text(`R$ ${parseFloat(produto.preco).toFixed(2)}`);
            const produtoModal = new bootstrap.Modal(document.getElementById('produtoModal'));
            produtoModal.show();
        });

        $(document).on('click', '.btn-editar', function () {
            const produto = $(this).data('produto');
            $('#produtoId').val(produto.id);
            $('#nome').val(produto.nome);
            $('#tipo').val(produto.tipo);
            $('#preco').val(produto.preco);
            $('#descricao').val(produto.descricao);
            window.scrollTo(0, 0);
        });
    });
</script>
@endsection