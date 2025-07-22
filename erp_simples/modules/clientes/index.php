<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ .'/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';   
require_once __DIR__ . '/../../includes/auth.php';       
require_once __DIR__ . '/../../includes/cliente_functions.php';
verificar_login();

$pageTitle = "Gestão de Clientes";

$termoBusca = isset($_GET['search']) ? trim($_GET['search']) : '';
$paginaAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limitePorPagina = 10;

$resultadoBusca = buscarClientes($termoBusca, $paginaAtual, $limitePorPagina);
$clientes = $resultadoBusca['clientes'];
$totalClientes = $resultadoBusca['total_clientes'];
$totalPaginas = ceil($totalClientes / $limitePorPagina);

include __DIR__ .'/../../includes/header.php';
?>
<h1 class="mb-4">Lista de Clientes</h1>

<div class="row mb-3">
    <div class="col-md-6">
        <form action="index.php" method="GET" class="d-flex">
            <input type="text" class="form-control me-2" name="search" placeholder="Buscar por nome ou CPF/CNPJ" value="<?php echo htmlspecialchars($termoBusca); ?>">
            <button class="btn btn-outline-secondary" type="submit">Buscar</button>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <a href="adicionar.php" class="btn btn-primary">Adicionar Novo Cliente</a>
    </div>
</div>

<?php if (empty($clientes)): ?>
    <div class="alert alert-info" role="alert">
        Nenhum cliente encontrado<?php echo !empty($termoBusca) ? " para o termo '<strong>" . htmlspecialchars($termoBusca) . "</strong>'" : ""; ?>.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CPF/CNPJ</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Endereço</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(isset($cliente['id']) ? $cliente['id'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($cliente['nome']) ? $cliente['nome'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($cliente['cpf_cnpj']) ? $cliente['cpf_cnpj'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($cliente['telefone']) ? $cliente['telefone'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($cliente['email']) ? $cliente['email'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($cliente['endereco']) ? $cliente['endereco'] : ''); ?></td>
                        <td>
                            <a href="editar.php?id=<?php echo htmlspecialchars(isset($cliente['id']) ? $cliente['id'] : ''); ?>" class="btn btn-sm btn-warning me-1">Editar</a>
                            <a href="processa_cliente.php?action=delete&id=<?php echo htmlspecialchars(isset($cliente['id']) ? $cliente['id'] : ''); ?>" 
                                class="btn btn-sm btn-danger" 
                                onclick="return confirm('Tem certeza que deseja excluir o cliente <?php echo htmlspecialchars(isset($cliente['nome']) ? $cliente['nome'] : ''); ?>?');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Navegação de Clientes">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($paginaAtual <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $paginaAtual - 1; ?><?php echo !empty($termoBusca) ? '&search=' . urlencode($termoBusca) : ''; ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?php echo ($paginaAtual == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($termoBusca) ? '&search=' . urlencode($termoBusca) : ''; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($paginaAtual >= $totalPaginas) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $paginaAtual + 1; ?><?php echo !empty($termoBusca) ? '&search=' . urlencode($termoBusca) : ''; ?>">Próxima</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>