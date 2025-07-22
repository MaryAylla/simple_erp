<?php
// modules/produtos/index.php - Lista de Produtos (REVISADO E LIMPO)

// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclui os arquivos de funções e conexão necessários
require_once __DIR__ . '/../../includes/database.php';        // Conexão DB ($pdo)
require_once __DIR__ . '/../../includes/functions.php';   // Funções gerais (ex: exibirMensagem)
require_once __DIR__ . '/../../includes/auth.php';       // Funções de autenticação
require_once __DIR__ . '/../../includes/produto_functions.php'; // Funções CRUD de produtos

// Protege a página: apenas usuários logados podem acessar
verificar_login();

// --- Lógica de Busca, Filtro e Ordenação ---
$pageTitle = "Gestão de Produtos";

// 1. Coleta e sanitiza os parâmetros da URL. Estas variáveis devem ser definidas PRIMEIRO.
$termoBusca   = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoriaFiltro = isset($_GET['categoria_id']) ? (int) $_GET['categoria_id'] : 0;
$ordenarPor   = isset($_GET['sort']) ? $_GET['sort'] : 'nome';
$ordem        = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// 2. Busca todos os produtos com base nos filtros COLETADOS ACIMA.
$produtos = buscarTodosProdutos($termoBusca, $categoriaFiltro, $ordenarPor, $ordem);

// 3. Busca todas as categorias para o filtro dropdown (sempre necessário).
$categorias = buscarTodasCategorias();

// Inclui o cabeçalho HTML e a barra de navegação
include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Lista de Produtos</h1>

<div class="row mb-3 align-items-center">
    <div class="col-md-8">
        <form action="index.php" method="GET" class="d-flex">
            <input type="text" class="form-control me-2" name="search" placeholder="Buscar por nome ou descrição" value="<?php echo htmlspecialchars($termoBusca); ?>">
            <select class="form-select me-2" name="categoria_id">
                <option value="0">Todas as Categorias</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo htmlspecialchars(isset($cat['id']) ? $cat['id'] : ''); ?>" 
                        <?php echo ($categoriaFiltro == (isset($cat['id']) ? $cat['id'] : '')) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(isset($cat['nome']) ? $cat['nome'] : ''); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-outline-secondary" type="submit">Filtrar</button>
            <?php if (!empty($termoBusca) || $categoriaFiltro > 0): ?>
                <a href="index.php" class="btn btn-outline-danger ms-2">Limpar Filtros</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-md-4 text-end">
        <a href="adicionar.php" class="btn btn-primary">Adicionar Novo Produto</a>
    </div>
</div>

<?php if (empty($produtos)): ?>
    <div class="alert alert-info" role="alert">
        Nenhum produto encontrado<?php echo (!empty($termoBusca) || $categoriaFiltro > 0) ? " para os filtros aplicados" : ""; ?>.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><a href="?sort=nome&order=<?php echo (isset($ordenarPor) && $ordenarPor == 'nome' && isset($ordem) && $ordem == 'ASC') ? 'DESC' : 'ASC'; ?><?php echo !empty($termoBusca) ? '&search=' . urlencode($termoBusca) : ''; ?><?php echo ($categoriaFiltro > 0) ? '&categoria_id=' . $categoriaFiltro : ''; ?>">Nome <?php echo (isset($ordenarPor) && $ordenarPor == 'nome') ? ((isset($ordem) && $ordem == 'ASC') ? '&#9650;' : '&#9660;') : ''; ?></a></th>
                    <th>Descrição</th>
                    <th><a href="?sort=preco&order=<?php echo (isset($ordenarPor) && $ordenarPor == 'preco' && isset($ordem) && $ordem == 'ASC') ? 'DESC' : 'ASC'; ?><?php echo !empty($termoBusca) ? '&search=' . urlencode($termoBusca) : ''; ?><?php echo ($categoriaFiltro > 0) ? '&categoria_id=' . $categoriaFiltro : ''; ?>">Preço <?php echo (isset($ordenarPor) && $ordenarPor == 'preco') ? ((isset($ordem) && $ordem == 'ASC') ? '&#9650;' : '&#9660;') : ''; ?></a></th>
                    <th><a href="?sort=quantidade_estoque&order=<?php echo (isset($ordenarPor) && $ordenarPor == 'quantidade_estoque' && isset($ordem) && $ordem == 'ASC') ? 'DESC' : 'ASC'; ?><?php echo !empty($termoBusca) ? '&search=' . urlencode($termoBusca) : ''; ?><?php echo ($categoriaFiltro > 0) ? '&categoria_id=' . $categoriaFiltro : ''; ?>">Estoque <?php echo (isset($ordenarPor) && $ordenarPor == 'quantidade_estoque') ? ((isset($ordem) && $ordem == 'ASC') ? '&#9650;' : '&#9660;') : ''; ?></a></th>
                    <th>Categoria</th>
                    <th>Foto</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(isset($produto['id']) ? $produto['id'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($produto['nome']) ? $produto['nome'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($produto['descricao']) ? (strlen($produto['descricao']) > 50 ? substr($produto['descricao'], 0, 50) . '...' : $produto['descricao']) : ''); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format(isset($produto['preco']) ? $produto['preco'] : 0, 2, ',', '.')); ?></td>
                        <td><?php echo htmlspecialchars(isset($produto['quantidade_estoque']) ? $produto['quantidade_estoque'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($produto['categoria_nome']) ? $produto['categoria_nome'] : 'N/A'); ?></td>
                        <td>
                            <?php if (isset($produto['foto_url']) && !empty($produto['foto_url'])): ?>
                                <img src="<?php echo htmlspecialchars($produto['foto_url']); ?>" alt="Foto do Produto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                Sem Foto
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="editar.php?id=<?php echo htmlspecialchars(isset($produto['id']) ? $produto['id'] : ''); ?>" class="btn btn-sm btn-warning me-1">Editar</a>
                            <a href="processa_produto.php?action=delete&id=<?php echo htmlspecialchars(isset($produto['id']) ? $produto['id'] : ''); ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Tem certeza que deseja excluir o produto <?php echo htmlspecialchars(isset($produto['nome']) ? $produto['nome'] : ''); ?>?');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    $produtosEstoqueBaixo = array_filter($produtos, function($p) {
        return (isset($p['quantidade_estoque']) ? $p['quantidade_estoque'] : 0) < 5;
    });
    if (!empty($produtosEstoqueBaixo)): ?>
        <div class="alert alert-warning mt-4" role="alert">
            <strong>Aviso de Estoque Baixo:</strong>
            <ul>
                <?php foreach ($produtosEstoqueBaixo as $p): ?>
                    <li><?php echo htmlspecialchars(isset($p['nome']) ? $p['nome'] : ''); ?> (Estoque: <?php echo htmlspecialchars(isset($p['quantidade_estoque']) ? $p['quantidade_estoque'] : ''); ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>