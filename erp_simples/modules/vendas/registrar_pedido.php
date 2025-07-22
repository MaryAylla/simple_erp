<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/venda_functions.php';
require_once __DIR__ . '/../../includes/cliente_functions.php';
require_once __DIR__ . '/../../includes/produto_functions.php';

verificar_login();

$pageTitle = "Registrar Nova Venda";

$clientes = buscarClientes('', 1, 9999)['clientes'];
$produtosDisponiveis = buscarTodosProdutos();

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Registrar Nova Venda</h1>

<form id="formVenda" action="processa_venda.php" method="POST">
    <input type="hidden" name="action" value="create">

    <div class="mb-3">
        <label for="cliente_id" class="form-label">Cliente:</label>
        <select class="form-select" id="cliente_id" name="cliente_id" required>
            <option value="">Selecione um Cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo htmlspecialchars(isset($cliente['id']) ? $cliente['id'] : ''); ?>">
                    <?php echo htmlspecialchars(isset($cliente['nome']) ? $cliente['nome'] : '') . ' (' . htmlspecialchars(isset($cliente['email']) ? $cliente['email'] : '') . ')'; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <hr class="my-4">
    <h3>Itens do Pedido</h3>
    <div id="itens-pedido-container">
        <div class="row mb-3 item-pedido">
            <div class="col-md-5">
                <label for="produto_0" class="form-label">Produto:</label>
                <select class="form-select produto-select" id="produto_0" name="produtos[0][id]" required>
                    <option value="">Selecione um Produto</option>
                    <?php foreach ($produtosDisponiveis as $produto): ?>
                        <option value="<?php echo htmlspecialchars(isset($produto['id']) ? $produto['id'] : ''); ?>"
                                data-preco="<?php echo htmlspecialchars(isset($produto['preco']) ? $produto['preco'] : ''); ?>"
                                data-estoque="<?php echo htmlspecialchars(isset($produto['quantidade_estoque']) ? $produto['quantidade_estoque'] : ''); ?>">
                            <?php echo htmlspecialchars(isset($produto['nome']) ? $produto['nome'] : '') . ' (Estoque: ' . htmlspecialchars(isset($produto['quantidade_estoque']) ? $produto['quantidade_estoque'] : '') . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted estoque-disponivel">Estoque disponível: 0</small>
            </div>
            <div class="col-md-3">
                <label for="quantidade_0" class="form-label">Quantidade:</label>
                <input type="number" class="form-control quantidade-input" id="quantidade_0" name="produtos[0][quantidade]" min="1" required value="1">
            </div>
            <div class="col-md-3">
                <label for="preco_unitario_0" class="form-label">Preço Unitário:</label>
                <input type="number" class="form-control preco-unitario-input" id="preco_unitario_0" name="produtos[0][preco_unitario]" step="0.01" min="0" readonly>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger remover-item-btn" style="display: none;">X</button>
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-info btn-sm mb-3" id="adicionar-item-btn">Adicionar Outro Item</button>

    <div class="mb-3">
        <label for="valor_total" class="form-label">Valor Total:</label>
        <input type="text" class="form-control" id="valor_total" name="valor_total" readonly value="0.00">
    </div>

    <button type="submit" class="btn btn-success">Registrar Venda</button>
    <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let itemIndex = 0;
        const itensContainer = document.getElementById('itens-pedido-container');
        const adicionarItemBtn = document.getElementById('adicionar-item-btn');
        const valorTotalInput = document.getElementById('valor_total');

        function calcularTotal() {
            let total = 0;
            document.querySelectorAll('.item-pedido').forEach(itemDiv => {
                const quantidade = parseFloat(itemDiv.querySelector('.quantidade-input').value) || 0;
                const precoUnitario = parseFloat(itemDiv.querySelector('.preco-unitario-input').value) || 0;
                total += quantidade * precoUnitario;
            });
            valorTotalInput.value = total.toFixed(2);
        }

        function atualizarPrecoEstoque(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const itemDiv = selectElement.closest('.item-pedido');
            const precoInput = itemDiv.querySelector('.preco-unitario-input');
            const estoqueSpan = itemDiv.querySelector('.estoque-disponivel');
            const quantidadeInput = itemDiv.querySelector('.quantidade-input');

            if (selectedOption.value) {
                const preco = selectedOption.dataset.preco;
                const estoque = selectedOption.dataset.estoque;
                precoInput.value = parseFloat(preco).toFixed(2);
                estoqueSpan.textContent = `Estoque disponível: ${estoque}`;
                quantidadeInput.max = estoque;
            } else {
                precoInput.value = '0.00';
                estoqueSpan.textContent = 'Estoque disponível: 0';
                quantidadeInput.max = '';
            }
            calcularTotal();
        }

        adicionarItemBtn.addEventListener('click', function() {
            itemIndex++;
            const newItemHtml = `
                <div class="row mb-3 item-pedido">
                    <div class="col-md-5">
                        <label for="produto_${itemIndex}" class="form-label">Produto:</label>
                        <select class="form-select produto-select" id="produto_${itemIndex}" name="produtos[${itemIndex}][id]" required>
                            <option value="">Selecione um Produto</option>
                            <?php foreach ($produtosDisponiveis as $produto): ?>
                                <option value="<?php echo htmlspecialchars(isset($produto['id']) ? $produto['id'] : ''); ?>"
                                        data-preco="<?php echo htmlspecialchars(isset($produto['preco']) ? $produto['preco'] : ''); ?>"
                                        data-estoque="<?php echo htmlspecialchars(isset($produto['quantidade_estoque']) ? $produto['quantidade_estoque'] : ''); ?>">
                                    <?php echo htmlspecialchars(isset($produto['nome']) ? $produto['nome'] : '') . ' (Estoque: ' . htmlspecialchars(isset($produto['quantidade_estoque']) ? $produto['quantidade_estoque'] : '') . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted estoque-disponivel">Estoque disponível: 0</small>
                    </div>
                    <div class="col-md-3">
                        <label for="quantidade_${itemIndex}" class="form-label">Quantidade:</label>
                        <input type="number" class="form-control quantidade-input" id="quantidade_${itemIndex}" name="produtos[${itemIndex}][quantidade]" min="1" required value="1">
                    </div>
                    <div class="col-md-3">
                        <label for="preco_unitario_${itemIndex}" class="form-label">Preço Unitário:</label>
                        <input type="number" class="form-control preco-unitario-input" id="preco_unitario_${itemIndex}" name="produtos[${itemIndex}][preco_unitario]" step="0.01" min="0" readonly>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remover-item-btn">X</button>
                    </div>
                </div>
            `;
            itensContainer.insertAdjacentHTML('beforeend', newItemHtml);
            const newSelect = itensContainer.lastElementChild.querySelector('.produto-select');
            const newQuantidadeInput = itensContainer.lastElementChild.querySelector('.quantidade-input');
            const newRemoverBtn = itensContainer.lastElementChild.querySelector('.remover-item-btn');

            newSelect.addEventListener('change', () => atualizarPrecoEstoque(newSelect));
            newQuantidadeInput.addEventListener('input', calcularTotal);
            newRemoverBtn.addEventListener('click', function() {
                this.closest('.item-pedido').remove();
                calcularTotal();
                if (document.querySelectorAll('.item-pedido').length === 1) {
                    document.querySelector('.remover-item-btn').style.display = 'none';
                }
            });

            document.querySelectorAll('.remover-item-btn').forEach(btn => btn.style.display = 'block');
        });

        document.getElementById('produto_0').addEventListener('change', function() {
            atualizarPrecoEstoque(this);
        });
        document.getElementById('quantidade_0').addEventListener('input', calcularTotal);

        if (document.querySelectorAll('.item-pedido').length === 1) {
            document.querySelector('.remover-item-btn').style.display = 'none';
        }
    });
</script>