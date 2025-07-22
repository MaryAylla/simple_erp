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

$pageTitle = "Gestão de Usuários do Sistema";

$termoBusca = isset($_GET['search']) ? trim($_GET['search']) : '';
$paginaAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limitePorPagina = 10; 

$resultadoBusca = buscarUsuariosSistema($termoBusca, $paginaAtual, $limitePorPagina);
$usuariosSistema = $resultadoBusca['usuarios'];
$totalUsuarios = $resultadoBusca['total_usuarios'];
$totalPaginas = ceil($totalUsuarios / $limitePorPagina);

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Gestão de Usuários do Sistema</h1>

<div class="row mb-3">
    <div class="col-md-6">
        <form action="index.php" method="GET" class="d-flex">
            <input type="text" class="form-control me-2" name="search" placeholder="Buscar por nome ou e-mail" value="<?php echo htmlspecialchars($termoBusca); ?>">
            <button class="btn btn-outline-secondary" type="submit">Buscar</button>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <a href="adicionar.php" class="btn btn-primary">Adicionar Novo Usuário</a>
    </div>
</div>

<?php if (empty($usuariosSistema)): ?>
    <div class="alert alert-info" role="alert">
        Nenhum usuário do sistema encontrado<?php echo !empty($termoBusca) ? " para o termo '<strong>" . htmlspecialchars($termoBusca) . "</strong>'" : ""; ?>.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Ativo</th>
                    <th>Data Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuariosSistema as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(isset($usuario['id']) ? $usuario['id'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($usuario['nome']) ? $usuario['nome'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($usuario['email']) ? $usuario['email'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($usuario['perfil']) ? $usuario['perfil'] : 'N/A'); ?></td>
                        <td>
                            <?php if (isset($usuario['ativo'])): ?>
                                <?php echo ((bool)$usuario['ativo']) ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-danger">Não</span>'; ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars(isset($usuario['data_cadastro']) ? date('d/m/Y', strtotime($usuario['data_cadastro'])) : ''); ?></td>
                        <td>
                            <a href="editar.php?id=<?php echo htmlspecialchars(isset($usuario['id']) ? $usuario['id'] : ''); ?>" class="btn btn-sm btn-warning me-1">Editar</a>
                            <?php // Impedir que o próprio usuário logado se delete ?>
                            <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != (isset($usuario['id']) ? $usuario['id'] : 0)): ?>
                                <a href="processa_usuario.php?action=delete&id=<?php echo htmlspecialchars(isset($usuario['id']) ? $usuario['id'] : ''); ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Tem certeza que deseja excluir o usuário <?php echo htmlspecialchars(isset($usuario['nome']) ? $usuario['nome'] : ''); ?>?');">Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Navegação de Usuários">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($paginaAtual <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo (string)($paginaAtual - 1); ?><?php echo !empty($termoBusca) ? '&search=' . urlencode($termoBusca) : ''; ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?php echo ($paginaAtual == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo (string)$i; ?><?php echo !empty($termoBusca) ? '&search=' . urlencode($termoBusca) : ''; ?>"><?php echo (string)$i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($paginaAtual >= $totalPaginas) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo (string)($paginaAtual + 1); ?><?php echo !empty($termoBusca) ? '&search=' . urlencode($termoBusca) : ''; ?>">Próxima</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>