<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ .'/../../includes/functions.php';
require_once __DIR__ .'/../../includes/auth.php';
require_once __DIR__ .'/../../includes/cliente_functions.php';

verificar_login();

$pageTitle = 'Editar Cliente';
$cliente = false;

if (isset($_GET['id'])) {
    $clienteId = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
    $cliente = buscarClientePorId($clienteId);

    if (!$cliente) {
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Cliente não encontrado para edição.';
        header('Location: index.php');
        exit();
    }
} else {
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'ID do cliente não fornecido par edição.';
    header('Location: index.php');
    exit();
}

include __DIR__ .'/../../includes/header.php';
?>
<h1 class="mb-4">Editar Cliente</h1>

<?php if ($cliente): ?>
    <form action="processa_cliente.php" method="POST">
        <input type="hidden" name="action" value="update"> 
        <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($cliente['id']) ? $cliente['id'] : ''); ?>">

        <div class="mb-3">
            <label for="nome" class="form-label">Nome Completo:</label>
            <input type="text" class="form-control" id="nome" name="nome" 
                   value="<?php echo htmlspecialchars(isset($cliente['nome']) ? $cliente['nome'] : ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="cpf_cnpj" class="form-label">CPF/CNPJ:</label>
            <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" 
                   value="<?php echo htmlspecialchars(isset($cliente['cpf_cnpj']) ? $cliente['cpf_cnpj'] : ''); ?>">
            <small class="form-text text-muted">Opcional, mas se preenchido, deve ser único.</small>
        </div>
        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone:</label>
            <input type="text" class="form-control" id="telefone" name="telefone" 
                   value="<?php echo htmlspecialchars(isset($cliente['telefone']) ? $cliente['telefone'] : ''); ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail:</label>
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?php echo htmlspecialchars(isset($cliente['email']) ? $cliente['email'] : ''); ?>">
            <small class="form-text text-muted">Opcional, mas se preenchido, deve ser único.</small>
        </div>
        <div class="mb-3">
            <label for="endereco" class="form-label">Endereço:</label>
            <textarea class="form-control" id="endereco" name="endereco" rows="3"><?php echo htmlspecialchars(isset($cliente['endereco']) ? $cliente['endereco'] : ''); ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-warning">Salvar Alterações</button>
        <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        Não foi possível carregar os dados do cliente para edição.
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>