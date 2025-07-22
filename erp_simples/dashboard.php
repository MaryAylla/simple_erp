<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/database.php'; 
require_once 'includes/functions.php'; 
require_once 'includes/auth.php';     

verificar_login();

$pageTitle = "Dashboard";
include 'includes/header.php';
?>
<h1>Bem-vindo ao Dashboard do ERP, <?php echo htmlspecialchars(isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Usuário'); ?>!</h1>
<p>Aqui você verá os principais indicadores e acessará os módulos.</p>

<div class="row mt-4">
    <div class="col-md-4 mb-4">
        <div class="card text-center bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Clientes Cadastrados</h5>
                <p class="card-text fs-1">
                    <?php
                        try {
                            $stmt = $pdo->query("SELECT COUNT(*) FROM clientes");
                            echo $stmt->fetchColumn();
                        } catch (PDOException $e) {
                            echo "Erro";
                        }
                    ?>
                </p>
                <a href="/erp_simples/modules/clientes/" class="btn btn-light">Ver Clientes</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Produtos em Estoque</h5>
                <p class="card-text fs-1">
                    <?php
                        try {
                            $stmt = $pdo->query("SELECT SUM(quantidade_estoque) FROM produtos");
                            $count = $stmt->fetchColumn();
                            echo ($count !== false) ? $count : 0;
                        } catch (PDOException $e) {
                            echo "Erro";
                        }
                    ?>
                </p>
                <a href="/erp_simples/modules/produtos/" class="btn btn-light">Ver Produtos</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-center bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Vendas do Mês</h5>
                <p class="card-text fs-1">R$ 
                    <?php
                        try {
                            $stmt = $pdo->query("SELECT SUM(total) FROM vendas WHERE DATE_FORMAT(data, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')");
                            $sum = $stmt->fetchColumn();
                            echo number_format(($sum !== false) ? $sum : 0, 2, ',', '.');
                        } catch (PDOException $e) {
                            echo "0,00";
                        }
                    ?>
                </p>
                <a href="/erp_simples/modules/vendas/" class="btn btn-light">Ver Vendas</a>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>