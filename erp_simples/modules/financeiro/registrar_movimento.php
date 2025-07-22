<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/financeiro_functions.php'; 

verificar_login();

$pageTitle = "Registrar Movimentação";
include __DIR__ . '/../../includes/header.php'; 
?>

<h1 class="mb-4">Registrar Nova Movimentação</h1>

<form action="processa_financeiro.php" method="POST">
    <input type="hidden" name="action" value="create"> 

    <div class="mb-3">
        <label for="tipo" class="form-label">Tipo de Movimentação:</label>
        <select class="form-select" id="tipo" name="tipo" required>
            <option value="">Selecione o Tipo</option>
            <option value="entrada">Entrada (Receita)</option>
            <option value="saida">Saída (Despesa)</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="descricao" class="form-label">Descrição:</label>
        <input type="text" class="form-control" id="descricao" name="descricao" required placeholder="Ex: Venda de produto X, Pagamento de aluguel">
    </div>
    <div class="mb-3">
        <label for="valor" class="form-label">Valor (R$):</label>
        <input type="number" class="form-control" id="valor" name="valor" step="0.01" min="0.01" required>
    </div>
    
    <button type="submit" class="btn btn-success">Registrar Movimentação</button>
    <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>