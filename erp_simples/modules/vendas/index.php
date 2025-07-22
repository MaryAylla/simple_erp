<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/venda_functions.php';
require_once __DIR__ . '/../../includes/cliente_functions.php';

verificar_login();

$pageTitle = "Gestão de Vendas";

$clienteFiltro = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;
$ordenarPor    = isset($_GET['sort']) ? $_GET['sort'] : 'data_venda';
$ordem         = isset($_GET['order']) ? $_GET['order'] : 'DESC';

$vendas = buscarTodasVendas($clienteFiltro, $ordenarPor, $ordem);

$clientes = buscarClientes('', 1, 9999)['clientes'];

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Histórico de Vendas</h1>

<div class="row mb-3 align-items-center">
    <div class="col-md-8">
        <form action="index.php" method="GET" class="d-flex">
            <select class="form-select me-2" name="cliente_id">
                <option value="0">Todos os Clientes</option>
                <?php foreach ($clientes as $cli): ?>
                    <option value="<?php echo htmlspecialchars(isset($cli['id']) ? $cli['id'] : ''); ?>"
                        <?php echo ($clienteFiltro == (isset($cli['id']) ? $cli['id'] : '')) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(isset($cli['nome']) ? $cli['nome'] : ''); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-outline-secondary" type="submit">Filtrar</button>
            <?php if ($clienteFiltro > 0): ?>
                <a href="index.php" class="btn btn-outline-danger ms-2">Limpar Filtro</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-md-4 text-end">
        <a href="registrar_pedido.php" class="btn btn-primary">Registrar Nova Venda</a>
    </div>
</div>

<?php if (empty($vendas)): ?>
    <div class="alert alert-info" role="alert">
        Nenhuma venda encontrada<?php echo ($clienteFiltro > 0) ? " para o cliente selecionado" : ""; ?>.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>ID da Venda</th>
                    <th><a href="?sort=data_venda&order=<?php echo (isset($ordenarPor) && $ordenarPor == 'data_venda' && isset($ordem) && $ordem == 'ASC') ? 'DESC' : 'ASC'; ?><?php echo ($clienteFiltro > 0) ? '&cliente_id=' . $clienteFiltro : ''; ?>">Data da Venda <?php echo (isset($ordenarPor) && $ordenarPor == 'data_venda') ? ((isset($ordem) && $ordem == 'ASC') ? '&#9650;' : '&#9660;') : ''; ?></a></th>
                    <th>Cliente</th>
                    <th><a href="?sort=valor_total&order=<?php echo (isset($ordenarPor) && $ordenarPor == 'valor_total' && isset($ordem) && $ordem == 'ASC') ? 'DESC' : 'ASC'; ?><?php echo ($clienteFiltro > 0) ? '&cliente_id=' . $clienteFiltro : ''; ?>">Valor Total <?php echo (isset($ordenarPor) && $ordenarPor == 'valor_total') ? ((isset($ordem) && $ordem == 'ASC') ? '&#9650;' : '&#9660;') : ''; ?></a></th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vendas as $venda): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(isset($venda['id']) ? $venda['id'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($venda['data_venda']) ? date('d/m/Y H:i', strtotime($venda['data_venda'])) : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($venda['cliente_nome']) ? $venda['cliente_nome'] : 'Cliente Desconhecido'); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format(isset($venda['valor_total']) ? $venda['valor_total'] : 0, 2, ',', '.')); ?></td>
                        <td>
                            <a href="visualizar_pedido.php?id=<?php echo htmlspecialchars(isset($venda['id']) ? $venda['id'] : ''); ?>" class="btn btn-sm btn-info me-1">Ver Detalhes</a>
                            <a href="gerar_recibo_pdf.php?id=<?php echo htmlspecialchars(isset($venda['id']) ? $venda['id'] : ''); ?>" class="btn btn-sm btn-secondary me-1" target="_blank">Gerar Recibo PDF</a>
                            <a href="processa_venda.php?action=delete&id=<?php echo htmlspecialchars(isset($venda['id']) ? $venda['id'] : ''); ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Tem certeza que deseja excluir esta venda? O estoque dos produtos será reabastecido.');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>