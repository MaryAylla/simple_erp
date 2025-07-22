<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/venda_functions.php';
require_once __DIR__ . '/../../includes/financeiro_functions.php';

verificar_login();

$pageTitle = "Relatórios Avançados";

$relatorioVendasPorMesEProduto = getRelatorioVendasPorMesEProduto();
$clientesQueMaisCompram = getClientesQueMaisCompram(5);
$produtosMaisVendidos = getProdutosMaisVendidos(5);
$dadosGraficoFluxoCaixa = buscarEntradasSaidasPorMes();

$labelsFluxoCaixa = array();
$dataEntradasFluxoCaixa = array();
$dataSaidasFluxoCaixa = array();
$dataLucroFluxoCaixa = array();

foreach ($dadosGraficoFluxoCaixa as $dado) {
    $mesAno = isset($dado['mes_ano']) ? $dado['mes_ano'] : 'N/A';
    $entradas = isset($dado['total_entradas']) ? (float)$dado['total_entradas'] : 0;
    $saidas = isset($dado['total_saidas']) ? (float)$dado['total_saidas'] : 0;
    $lucro = $entradas - $saidas;

    $labelsFluxoCaixa[] = date('M/Y', strtotime($mesAno . '-01'));
    $dataEntradasFluxoCaixa[] = $entradas;
    $dataSaidasFluxoCaixa[] = $saidas;
    $dataLucroFluxoCaixa[] = $lucro;
}

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Relatórios Avançados</h1>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Entradas e Saídas por Mês (Gráfico Financeiro)
            </div>
            <div class="card-body">
                <canvas id="fluxoCaixaChart"></canvas>
                <div class="text-end mt-3">
                    <a href="gerar_relatorio_pdf.php?type=fluxo_caixa_mensal" class="btn btn-sm btn-outline-dark" target="_blank">Exportar PDF</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                Clientes que Mais Compram (Top 5)
            </div>
            <div class="card-body">
                <?php if (empty($clientesQueMaisCompram)): ?>
                    <p class="card-text">Nenhum dado de compra disponível ainda.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Total Comprado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientesQueMaisCompram as $cliente): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(isset($cliente['cliente_nome']) ? $cliente['cliente_nome'] : ''); ?></td>
                                        <td>R$ <?php echo htmlspecialchars(number_format(isset($cliente['total_comprado']) ? $cliente['total_comprado'] : 0, 2, ',', '.')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="gerar_relatorio_pdf.php?type=top_clientes" class="btn btn-sm btn-outline-dark" target="_blank">Exportar PDF</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                Produtos Mais Vendidos (Top 5)
            </div>
            <div class="card-body">
                <?php if (empty($produtosMaisVendidos)): ?>
                    <p class="card-text">Nenhum dado de venda de produtos disponível ainda.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd. Vendida</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtosMaisVendidos as $produto): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(isset($produto['produto_nome']) ? $produto['produto_nome'] : ''); ?></td>
                                        <td><?php echo htmlspecialchars(isset($produto['total_vendido']) ? $produto['total_vendido'] : ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="gerar_relatorio_pdf.php?type=top_produtos" class="btn btn-sm btn-outline-dark" target="_blank">Exportar PDF</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-white">
                Vendas por Mês e por Produto
            </div>
            <div class="card-body">
                <?php if (empty($relatorioVendasPorMesEProduto)): ?>
                    <p class="card-text">Nenhuma venda registrada para este relatório.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Mês/Ano</th>
                                    <th>Produto</th>
                                    <th>Qtd. Vendida</th>
                                    <th>Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($relatorioVendasPorMesEProduto as $itemRelatorio): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(isset($itemRelatorio['mes_ano']) ? date('F Y', strtotime($itemRelatorio['mes_ano'] . '-01')) : ''); ?></td>
                                        <td><?php echo htmlspecialchars(isset($itemRelatorio['produto_nome']) ? $itemRelatorio['produto_nome'] : ''); ?></td>
                                        <td><?php echo htmlspecialchars(isset($itemRelatorio['total_vendido_quantidade']) ? $itemRelatorio['total_vendido_quantidade'] : ''); ?></td>
                                        <td>R$ <?php echo htmlspecialchars(number_format(isset($itemRelatorio['total_vendido_valor']) ? $itemRelatorio['total_vendido_valor'] : 0, 2, ',', '.')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="gerar_relatorio_pdf.php?type=vendas_por_mes_produto" class="btn btn-sm btn-outline-dark" target="_blank">Exportar PDF</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="text-end mt-4">
    <a href="/erp_simples/dashboard.php" class="btn btn-secondary">Voltar para o Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('fluxoCaixaChart').getContext('2d');
        const fluxoCaixaChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labelsFluxoCaixa); ?>,
                datasets: [
                    {
                        label: 'Entradas (R$)',
                        data: <?php echo json_encode($dataEntradasFluxoCaixa); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Saídas (R$)',
                        data: <?php echo json_encode($dataSaidasFluxoCaixa); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        type: 'line',
                        label: 'Lucro Líquido (R$)',
                        data: <?php echo json_encode($dataLucroFluxoCaixa); ?>,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Valor (R$)'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'R$ ' + context.parsed.y.toFixed(2).replace('.', ',');
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>