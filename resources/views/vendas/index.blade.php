@extends('layouts.app')

@section('title', 'Cadastro de Venda')

@section('styles')
<style>
    .nota-fiscal-wrapper { border: 2px solid #6c757d; padding: 2rem; border-radius: 5px; background-color: #f8f9fa; }
    .nota-fiscal-header { text-align: center; border-bottom: 2px dashed #6c757d; padding-bottom: 1rem; margin-bottom: 1.5rem; }
    .nota-fiscal-header h2 { font-weight: bold; font-family: 'Courier New', Courier, monospace; }
    .nota-fiscal table { width: 100%; }
    .nota-fiscal .total-final { font-size: 1.5rem; font-weight: bold; text-align: right; margin-top: 1rem; border-top: 2px solid #6c757d; padding-top: 1rem; }
</style>
@endsection

@section('content')
<div class="container mt-4">

    <div id="form-venda-container">
        <h1 class="mb-4">Cadastro de Venda</h1>
        <form id="vendaForm" class="p-4 border rounded bg-light">
            <div class="row">
                <div class="col-md-8"><label for="cliente_id" class="form-label"><strong>Funcionário</strong></label><select name="cliente_id" id="cliente_id" class="form-select" required></select></div>
                <div class="col-md-4"><label for="data" class="form-label"><strong>Data da Venda</strong></label><input type="date" name="data" id="data" class="form-control" required></div>
            </div>
            <hr class="my-4">
            <h4 class="mb-3">Itens da Venda</h4>
            <div id="itens-container">
                <div class="row item-venda mb-3 align-items-center"><div class="col-md-6"><label class="form-label d-none d-md-block">Produto</label><select name="itens[0][produto_id]" class="form-select produto-select" required></select></div><div class="col-md-4"><label class="form-label d-none d-md-block">Quantidade</label><input type="number" name="itens[0][quantidade]" class="form-control quantidade-input" value="1" min="1" required></div><div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-danger btn-remover-item w-100">Remover</button></div></div>
            </div>
            <button type="button" id="adicionarItem" class="btn btn-primary mt-2">Adicionar Item</button>
            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center"><h2 class="m-0">Total: <span id="totalVenda" class="text-success fw-bold">R$ 0,00</span></h2><button type="submit" class="btn btn-success btn-lg px-5">Finalizar e Salvar Venda</button></div>
        </form>
    </div>

    <div id="nota-fiscal-container" class="d-none"></div>

</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let produtosCache = [];
    let itemIndex = 0;

    function carregarDadosIniciais() {
        $.when(
            $.get('{{ route("clientes.index") }}'),
            $.get('{{ route("produtos.index") }}')
        ).done(function(clientesRes, produtosRes) {
            const clientes = clientesRes[0];
            const clienteSelect = $('#cliente_id');
            clienteSelect.empty().append('<option value="" disabled selected>Selecione um funcionário</option>');
            clientes.forEach(cliente => clienteSelect.append(`<option value="${cliente.id}">${cliente.nome}</option>`));
            
            produtosCache = produtosRes[0];
            preencherProdutos(0);
            $('#data').val(new Date().toISOString().slice(0, 10));
        });
    }

    function preencherProdutos(index) {
        const produtoSelect = $(`select[name="itens[${index}][produto_id]"]`);
        produtoSelect.empty().append('<option value="" disabled selected>Selecione um produto</option>');
        produtosCache.forEach(produto => {
            const precoFmt = parseFloat(produto.preco).toFixed(2).replace('.', ',');
            produtoSelect.append(`<option value="${produto.id}" data-preco="${produto.preco}">${produto.nome} (R$ ${precoFmt})</option>`);
        });
    }

    function calcularTotal() {
        let total = 0;
        $('.item-venda').each(function() {
            const produtoSelect = $(this).find('.produto-select');
            const quantidade = $(this).find('.quantidade-input').val();
            const preco = produtoSelect.find('option:selected').data('preco');
            if (preco && quantidade > 0) {
                total += parseFloat(preco) * parseInt(quantidade);
            }
        });
        $('#totalVenda').text(`R$ ${total.toFixed(2).replace('.', ',')}`);
    }

    $('#adicionarItem').click(function() {
        itemIndex++;
        const novoItemHtml = `<div class="row item-venda mb-3 align-items-center"><div class="col-md-6"><select name="itens[${itemIndex}][produto_id]" class="form-select produto-select" required></select></div><div class="col-md-4"><input type="number" name="itens[${itemIndex}][quantidade]" class="form-control quantidade-input" value="1" min="1" required></div><div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-danger btn-remover-item w-100">Remover</button></div></div>`;
        $('#itens-container').append(novoItemHtml);
        preencherProdutos(itemIndex);
    });

    $(document).on('click', '.btn-remover-item', function() {
        if ($('.item-venda').length > 1) {
            $(this).closest('.item-venda').remove();
            calcularTotal();
        } else { alert('A venda deve ter pelo menos um item.'); }
    });

    $(document).on('change', '.produto-select, .quantidade-input', calcularTotal);

    $('#vendaForm').submit(function(e) {
        e.preventDefault();
        const submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true).text('Salvando...');

        $.ajax({
            url: "{{ route('vendas.store') }}",
            type: "POST",
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                gerarNotaFiscal(response);
            },
            error: function(xhr) {
                alert('Ocorreu um erro inesperado. Tente novamente.');
                console.error(xhr.responseText);
            },
            complete: function() {
                submitButton.prop('disabled', false).text('Finalizar e Salvar Venda');
            }
        });
    });

    function gerarNotaFiscal(venda) {
        let itensHtml = '';
        venda.itens.forEach(item => {
            let nomeProduto = item.produto ? item.produto.nome : 'Produto não encontrado';
            let subtotal = (item.quantidade * item.preco_unitario).toFixed(2).replace('.', ',');
            let precoUnit = parseFloat(item.preco_unitario).toFixed(2).replace('.', ',');
            itensHtml += `<tr><td>${item.quantidade}x</td><td>${nomeProduto}</td><td class="text-end">R$ ${precoUnit}</td><td class="text-end">R$ ${subtotal}</td></tr>`;
        });
        
        const partesData = venda.data.split('-'); 
        const dataFormatada = `${partesData[2]}/${partesData[1]}/${partesData[0]}`; 
        const horaFormatada = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

        let nomeCliente = venda.cliente ? venda.cliente.nome : 'Cliente não informado';

        const notaHtml = `
            <div class="nota-fiscal-wrapper">
                <div class="nota-fiscal-header"><h2>AGROSHOPPING</h2><p class="mb-0">Rua das Oliveiras, 123 - Centro<br>CNPJ: 89.752.564/0001-00</p></div>
                <p class="mt-3"><strong>Recibo de Venda N.º:</strong> ${venda.id}</p>
                <p><strong>Data:</strong> ${dataFormatada} às ${horaFormatada}</p>
                <p><strong>Funcionário:</strong> ${nomeCliente}</p>
                <hr>
                <table class="table table-sm">
                    <thead><tr><th>Qtd</th><th>Descrição</th><th class="text-end">Vl. Unit.</th><th class="text-end">Vl. Total</th></tr></thead>
                    <tbody>${itensHtml}</tbody>
                </table>
                <div class="total-final">
                    TOTAL: R$ ${parseFloat(venda.total).toFixed(2).replace('.', ',')}
                </div>
                <hr>
                <div class="text-center mt-4 d-print-none">
                    <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
                    <button class="btn btn-secondary" onclick="location.reload()">Realizar Nova Venda</button>
                </div>
            </div>`;
        $('#form-venda-container').hide();
        $('#nota-fiscal-container').html(notaHtml).removeClass('d-none');
    }

    carregarDadosIniciais();
});
</script>
@endsection