<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';      
require_once __DIR__ . '/../../includes/functions.php';  
require_once __DIR__ . '/../../includes/auth.php';       
require_once __DIR__ . '/../../includes/usuario_functions.php'; 

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true || 
    !isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
    
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'Acesso negado. Apenas administradores podem gerenciar usuários.';
    header('Location: ' . (__DIR__ . '/../../dashboard.php'));
    exit();
}

$pageTitle = "Adicionar Usuário do Sistema"; 

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Adicionar Novo Usuário do Sistema</h1>

<form action="processa_usuario.php" method="POST">
    <input type="hidden" name="action" value="create"> 

    <div class="mb-3">
        <label for="nome" class="form-label">Nome Completo:</label>
        <input type="text" class="form-control" id="nome" name="nome" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">E-mail:</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="senha" class="form-label">Senha:</label>
        <input type="password" class="form-control" id="senha" name="senha" required>
    </div>
    <div class="mb-3">
        <label for="confirmar_senha" class="form-label">Confirmar Senha:</label>
        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
    </div>
    <div class="mb-3">
        <label for="perfil" class="form-label">Perfil:</label>
        <select class="form-select" id="perfil" name="perfil" required>
            <option value="vendedor">Vendedor</option>
            <option value="admin">Administrador</option>
            <option value="financeiro">Financeiro</option>
            </select>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="ativo" name="ativo" value="1" checked>
        <label class="form-check-label" for="ativo">Usuário Ativo</label>
    </div>
    
    <button type="submit" class="btn btn-success">Salvar Usuário</button>
    <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>