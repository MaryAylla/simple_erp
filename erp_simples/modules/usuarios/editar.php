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

$pageTitle = "Editar Usuário do Sistema";
$usuario = false;

if (isset($_GET['id'])) {
    $usuarioId = (int)(isset($_GET['id']) ? $_GET['id'] : 0);

    $usuario = buscarUsuarioSistemaPorId($usuarioId);

    if (!$usuario) {
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Usuário do sistema não encontrado para edição.';
        header('Location: index.php');
        exit();
    }
} else {
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'ID do usuário do sistema não fornecido para edição.';
    header('Location: index.php');
    exit();
}

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Editar Usuário do Sistema</h1>

<?php if ($usuario): ?>
    <form action="processa_usuario.php" method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($usuario['id']) ? $usuario['id'] : ''); ?>">

        <div class="mb-3">
            <label for="nome" class="form-label">Nome Completo:</label>
            <input type="text" class="form-control" id="nome" name="nome"
                   value="<?php echo htmlspecialchars(isset($usuario['nome']) ? $usuario['nome'] : ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail:</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?php echo htmlspecialchars(isset($usuario['email']) ? $usuario['email'] : ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="senha" class="form-label">Nova Senha:</label>
            <input type="password" class="form-control" id="senha" name="senha">
            <small class="form-text text-muted">Deixe em branco para não alterar a senha.</small>
        </div>
        <div class="mb-3">
            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha:</label>
            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
        </div>
        <div class="mb-3">
            <label for="perfil" class="form-label">Perfil:</label>
            <select class="form-select" id="perfil" name="perfil" required>
                <option value="vendedor" <?php echo (isset($usuario['perfil']) && $usuario['perfil'] == 'vendedor') ? 'selected' : ''; ?>>Vendedor</option>
                <option value="admin" <?php echo (isset($usuario['perfil']) && $usuario['perfil'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                <option value="financeiro" <?php echo (isset($usuario['perfil']) && $usuario['perfil'] == 'financeiro') ? 'selected' : ''; ?>>Financeiro</option>
            </select>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="ativo" name="ativo" value="1"
                   <?php echo (isset($usuario['ativo']) && (bool)$usuario['ativo']) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="ativo">Usuário Ativo</label>
        </div>

        <button type="submit" class="btn btn-warning">Salvar Alterações</button>
        <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        Não foi possível carregar os dados do usuário para edição.
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>