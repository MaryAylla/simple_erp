<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ .'/../../includes/database.php';
require_once __DIR__ .'/../../includes/functions.php';
require_once __DIR__ .'/../../includes/auth.php';
require_once __DIR__ .'/../../includes/cliente_functions.php';

verificar_login();

$pageTitle = "Adicionar Cliente";
include __DIR__ .'/../../includes/header.php';
?>

<h1 class="mb-4">Adicionar Novo Cliente</h1>

<form action="processa_cliente.php" method="POST">
    <input type="hidden" name="action" value="create"> 

    <div class="mb-3">
        <label for="nome" class="form-label">Nome Completo:</label>
        <input type="text" class="form-control" id="nome" name="nome" required>
    </div>
    <div class="mb-3">
        <label for="cpf_cnpj" class="form-label">CPF/CNPJ:</label>
        <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj">
        <small class="form-text text-muted">Opcional, mas se preenchido, deve ser único.</small>
    </div>
    <div class="mb-3">
        <label for="telefone" class="form-label">Telefone:</label>
        <input type="text" class="form-control" id="telefone" name="telefone">
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">E-mail:</label>
        <input type="email" class="form-control" id="email" name="email">
        <small class="form-text text-muted">Opcional, mas se preenchido, deve ser único.</small>
    </div>
    <div class="mb-3">
        <label for="endereco" class="form-label">Endereço:</label>
        <textarea class="form-control" id="endereco" name="endereco" rows="3"></textarea>
    </div>
    
    <button type="submit" class="btn btn-success">Salvar Cliente</button>
    <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>