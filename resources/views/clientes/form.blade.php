@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Cadastro de Funcionários</h1>

    <!-- Formulário -->
    <form id="formCliente" class="mb-4">
        @csrf
        <input type="hidden" id="cliente_id">
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome" required>
            </div>
            <div class="col-md-3">
                <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="telefone" id="telefone" class="form-control" placeholder="Telefone">
            </div>
            <div class="col-md-3">
                <input type="text" name="endereco" id="endereco" class="form-control" placeholder="Endereço">
            </div>
        </div>
        <button type="submit" class="btn btn-success mt-3">Salvar</button>
    </form>

    <!-- Tabela -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Endereço</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="listaClientes"></tbody>
    </table>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
    function carregarClientes() {
        $.get("{{ route('clientes.index') }}", function(clientes) {
            let linhas = '';
            clientes.forEach(cliente => {
                linhas += `
                    <tr>
                        <td>${cliente.nome}</td>
                        <td>${cliente.email}</td>
                        <td>${cliente.telefone ?? ''}</td>
                        <td>${cliente.endereco ?? ''}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editarCliente(${cliente.id})">Editar</button>
                            <button class="btn btn-danger btn-sm" onclick="excluirCliente(${cliente.id})">Excluir</button>
                        </td>
                    </tr>
                `;
            });
            $("#listaClientes").html(linhas);
        });
    }

    $('#formCliente').submit(function(e) {
        e.preventDefault();

        const id = $('#cliente_id').val();
        const url = id ? `/clientes/${id}` : '/clientes';
        const metodo = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: metodo,
            data: {
                nome: $('#nome').val(),
                email: $('#email').val(),
                telefone: $('#telefone').val(),
                endereco: $('#endereco').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                carregarClientes();
                $('#formCliente')[0].reset();
                $('#cliente_id').val('');
            },
            error: function(xhr) {
                alert('Erro: ' + xhr.responseJSON.message);
            }
        });
    });

    function editarCliente(id) {
        $.get(`/clientes/${id}`, function(cliente) {
            $('#cliente_id').val(cliente.id);
            $('#nome').val(cliente.nome);
            $('#email').val(cliente.email);
            $('#telefone').val(cliente.telefone);
            $('#endereco').val(cliente.endereco);
        });
    }

    function excluirCliente(id) {
        if (confirm('Tem certeza que deseja excluir?')) {
            $.ajax({
                url: `/clientes/${id}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    carregarClientes();
                }
            });
        }
    }

    $(document).ready(function() {
        carregarClientes();
    });
</script>
@endsection
