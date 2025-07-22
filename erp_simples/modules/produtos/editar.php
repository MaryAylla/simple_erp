<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';        
require_once __DIR__ . '/../../includes/functions.php';   
require_once __DIR__ . '/../../includes/auth.php';       
require_once __DIR__ . '/../../includes/produto_functions.php'; 

verificar_login();

$pageTitle = "Editar Produto"; 
$produto = false; 
$categorias = buscarTodasCategorias(); 

if (isset($_GET['id'])) {
    $produtoId = (int)(isset($_GET['id']) ? $_GET['id'] : 0); 

    $produto = buscarProdutoPorId($produtoId);

    if (!$produto) {
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Produto não encontrado para edição.';
        header('Location: index.php');
        exit();
    }
} else {
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'ID do produto não fornecido para edição.';
    header('Location: index.php');
    exit();
}

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Editar Produto</h1>

<?php if ($produto): ?>
    <form action="processa_produto.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update"> 
        <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($produto['id']) ? $produto['id'] : ''); ?>">
        
        <input type="hidden" name="foto_url_existente" value="<?php echo htmlspecialchars(isset($produto['foto_url']) ? $produto['foto_url'] : ''); ?>">

        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Produto:</label>
            <input type="text" class="form-control" id="nome" name="nome" 
                   value="<?php echo htmlspecialchars(isset($produto['nome']) ? $produto['nome'] : ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição:</label>
            <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo htmlspecialchars(isset($produto['descricao']) ? $produto['descricao'] : ''); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="preco" class="form-label">Preço (R$):</label>
            <input type="number" class="form-control" id="preco" name="preco" step="0.01" min="0" 
                   value="<?php echo htmlspecialchars(isset($produto['preco']) ? $produto['preco'] : ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="quantidade_estoque" class="form-label">Quantidade em Estoque:</label>
            <input type="number" class="form-control" id="quantidade_estoque" name="quantidade_estoque" min="0" 
                   value="<?php echo htmlspecialchars(isset($produto['quantidade_estoque']) ? $produto['quantidade_estoque'] : ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="categoria_id" class="form-label">Categoria:</label>
            <select class="form-select" id="categoria_id" name="categoria_id">
                <option value="">Selecione uma Categoria (Opcional)</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo htmlspecialchars(isset($categoria['id']) ? $categoria['id'] : ''); ?>" 
                        <?php echo ((isset($produto['categoria_id']) ? $produto['categoria_id'] : '') == (isset($categoria['id']) ? $categoria['id'] : '')) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(isset($categoria['nome']) ? $categoria['nome'] : ''); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="foto_produto" class="form-label">Nova Foto do Produto:</label>
            <?php if (isset($produto['foto_url']) && !empty($produto['foto_url'])): ?>
                <div class="mb-2">
                    <img src="<?php echo htmlspecialchars($produto['foto_url']); ?>" alt="Foto Atual" style="max-width: 150px; border-radius: 5px;">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="manter_foto" name="manter_foto" value="1" checked>
                        <label class="form-check-label" for="manter_foto">
                            Manter foto atual
                        </label>
                    </div>
                </div>
            <?php endif; ?>
            <input type="file" class="form-control" id="foto_produto" name="foto_produto" accept="image/*">
            <small class="form-text text-muted">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB. Se enviar nova foto, a antiga será substituída.</small>
        </div>
        
        <button type="submit" class="btn btn-warning">Salvar Alterações</button>
        <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        Não foi possível carregar os dados do produto para edição.
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>