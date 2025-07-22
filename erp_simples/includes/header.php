<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? $pageTitle : 'ERP Simples'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/erp_simples/assets/css/style.css">
    </head>
    <body>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/erp_simples/dashboard.php">ERP Simples</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (isset($_SESSION['logado']) && $_SESSION['logado'] === true): ?>
            <li class="nav-item">
                <a class="nav-link" href="/erp_simples/modules/clientes/">Clientes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/erp_simples/modules/produtos/">Produtos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/erp_simples/modules/vendas/">Vendas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/erp_simples/modules/financeiro/">Financeiro</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/erp_simples/modules/relatorios/">Relatórios</a>
            </li>
            
            <?php ?>
            <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link text-info" href="/erp_simples/modules/usuarios/">Gestão de Usuários</a>
                </li>
            <?php endif; ?>
            <?php ?>

        <?php endif; ?>
    </ul>
    </div>
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['logado']) && $_SESSION['logado'] === true): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Olá, <?php echo htmlspecialchars(isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Usuário'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-danger btn-sm text-white" href="/erp_simples/logout.php">Sair</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/erp_simples/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php exibirMensagem();  ?>
    </body>
</html>