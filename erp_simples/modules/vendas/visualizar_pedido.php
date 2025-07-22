<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';       
require_once __DIR__ . '/../../includes/functions.php';   
require_once __DIR__ . '/../../includes/auth.php';       
require_once __DIR__ . '/../../includes/venda_functions.php'; 

verificar_login();

$pageTitle = "Detalhes da Venda"; 

$venda = false; 

$vendaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($vendaId <= 0) {
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'ID da venda não fornecido para visualização.';
    header('Location: index.php');
    exit();
}

$venda = buscarVendaPorId($vendaId);

if (!$venda) {
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'Venda não encontrada para o ID: ' . $vendaId;
    header('Location: index.php');
    exit();
}

$pageTitle = "Detalhes da Venda #" . htmlspecialchars($venda['id']);

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Detalhes da Venda #<?php echo htmlspecialchars(isset($venda['id']) ? $venda['id'] : ''); ?></h1>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        Informações da Venda
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>ID da Venda:</strong> <?php echo htmlspecialchars(isset($venda['id']) ? $venda['id'] : ''); ?></p>
                <p><strong>Data da Venda:</strong> <?php echo htmlspecialchars(isset($venda['data_venda']) ? date('d/m/Y H:i', strtotime($venda['data_venda'])) : ''); ?></p>
                <p><strong>Valor Total:</strong> R$ <?php echo htmlspecialchars(number_format(isset($venda['valor_total']) ? $venda['valor_total'] : 0, 2, ',', '.')); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars(isset($venda['cliente_nome']) ? $venda['cliente_nome'] : ''); ?></p>
                <p><strong>CPF/CNPJ do Cliente:</strong> <?php echo htmlspecialchars(isset($venda['cliente_cpf_cnpj']) ? $venda['cliente_cpf_cnpj'] : 'Não informado'); ?></p>
            </div>
        </div>
        <div class="text-end mt-3">
            <a href="gerar_recibo_pdf.php?id=<?php echo htmlspecialchars(isset($venda['id']) ? $venda['id'] : ''); ?>" class="btn btn-info" target="_blank">Gerar Recibo PDF</a>
            <a href="index.php" class="btn btn-secondary ms-2">Voltar para Vendas</a>
        </div>
    </div>
</div>

<h2 class="mb-3">Itens da Venda</h2>
<?php if (empty($venda['itens'])): ?>
    <div class="alert alert-warning" role="alert">
        Nenhum item encontrado para esta venda.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>ID Item</th>
                    <th>Produto</th>
                    <th>Qtd</th>
                    <th>Preço Unitário</th>
                    <th>Subtotal</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($venda['itens'] as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(isset($item['id']) ? $item['id'] : ''); ?></td>
                        <td><?php echo htmlspecialchars(isset($item['produto_nome']) ? $item['produto_nome'] : 'Produto Desconhecido'); ?></td>
                        <td><?php echo htmlspecialchars(isset($item['quantidade']) ? $item['quantidade'] : ''); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format(isset($item['preco_unitario']) ? $item['preco_unitario'] : 0, 2, ',', '.')); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format(isset($item['subtotal']) ? $item['subtotal'] : 0, 2, ',', '.')); ?></td>
                        <td>
                            <?php if (isset($item['produto_foto_url']) && !empty($item['produto_foto_url'])): ?>
                                <img src="<?php echo htmlspecialchars($item['produto_foto_url']); ?>" alt="Foto do Produto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                Sem Foto
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>