<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    }
require_once 'includes/functions.php';

$pageTitle = "Login ERP";
include 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card mt-5">
            <div class="card-header text-center bg-dark text-white">
                <h3 class="mb-0">Acesso ao ERP</h3>
            </div>
            <div class="card-body">
                <form action="processar_login.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail:</label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha:</label>
                        <input type="password" class="form-control" id="senha" name="senha" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
                <p class="text-center mt-3">Não tem uma conta? <a href="register.php">Cadastre-se aqui</a></p>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>