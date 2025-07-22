<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';        
require_once __DIR__ . '/../../includes/functions.php';   
require_once __DIR__ . '/../../includes/auth.php';       
require_once __DIR__ . '/../../includes/produto_functions.php'; 

verificar_login();

$pageTitle = "Adicionar Produto"; 
$categorias = buscarTodasCategorias(); 

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Adicionar Novo Produto</h1>

<form action="processa_produto.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="create"> 

    <div class="mb-3">
        <label for="nome" class="form-label">Nome do Produto:</label>
        <input type="text" class="form-control" id="nome" name="nome" required>
    </div>
    <div class="mb-3">
        <label for="descricao" class="form-label">Descrição:</label>
        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label for="preco" class="form-label">Preço (R$):</label>
        <input type="number" class="form-control" id="preco" name="preco" step="0.01" min="0" required>
    </div>
    <div class="mb-3">
        <label for="quantidade_estoque" class="form-label">Quantidade em Estoque:</label>
        <input type="number" class="form-control" id="quantidade_estoque" name="quantidade_estoque" min="0" required>
    </div>
    <div class="mb-3">
        <label for="categoria_id" class="form-label">Categoria:</label>
        <select class="form-select" id="categoria_id" name="categoria_id">
            <option value="">Selecione uma Categoria (Opcional)</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?php echo htmlspecialchars($categoria['id']); ?>">
                    <?php echo htmlspecialchars(isset($categoria['nome']) ? $categoria['nome'] : ''); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="foto_produto" class="form-label">Foto do Produto:</label>
        <input type="file" class="form-control" id="foto_produto" name="foto_produto" accept="image/*">
        <small class="form-text text-muted">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB.</small>
    </div>
    
    <button type="submit" class="btn btn-success">Salvar Produto</button>
    <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>