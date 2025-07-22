<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/database.php';
require_once 'includes/functions.php';

$pageTitle = "Registrar Usuário";
include 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card mt-5">
            <div class="card-header text-center bg-dark text-white">
                <h3 class="mb-0">Registro de Novo Usuário ERP</h3>
            </div>
            <div class="card-body">
                <form action="processa_registro.php" method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo:</label>
                        <input type="text" class="form-control" id="nome" name="nome" required autocomplete="name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail:</label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha:</label>
                        <input type="password" class="form-control" id="senha" name="senha" required autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label for="confirmar_senha" class="form-label">Confirmar Senha:</label>
                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Registrar</button>
                </form>
                <p class="text-center mt-3">Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>