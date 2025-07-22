<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/financeiro_functions.php'; 

verificar_login();

$pageTitle = "Relatórios Financeiros"; 

$dadosGrafico = buscarEntradasSaidasPorMes();

$labels = array();        
$dataEntradas = array();  
$dataSaidas = array();    
$dataLucro = array();     

foreach ($dadosGrafico as $dado) {
    $mesAno       = isset($dado['mes_ano']) ? $dado['mes_ano'] : 'N/A';
    $entradas     = isset($dado['total_entradas']) ? (float)$dado['total_entradas'] : 0;
    $saidas       = isset($dado['total_saidas']) ? (float)$dado['total_saidas'] : 0;
    $lucro        = $entradas - $saidas;

    $labels[] = date('M/Y', strtotime($mesAno . '-01')); 
    $dataEntradas[] = $entradas;
    $dataSaidas[] = $saidas;
    $dataLucro[] = $lucro;
}


include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Relatórios Financeiros</h1>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Entradas e Saídas por Mês
            </div>
            <div class="card-body">
                <canvas id="fluxoCaixaChart"></canvas>
            </div>
        </div>
    </div>
</div>

<h2 class="mb-3">Detalhes por Mês</h2>
<?php if (empty($dadosGrafico)): ?>
    <div class="alert alert-info" role="alert">
        Nenhuma movimentação financeira para gerar relatórios.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Mês/Ano</th>
                    <th>Total Entradas</th>
                    <th>Total Saídas</th>
                    <th>Lucro Líquido</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dadosGrafico as $dado):
                    $mesAno       = isset($dado['mes_ano']) ? $dado['mes_ano'] : 'N/A';
                    $entradas     = isset($dado['total_entradas']) ? (float)$dado['total_entradas'] : 0;
                    $saidas       = isset($dado['total_saidas']) ? (float)$dado['total_saidas'] : 0;
                    $lucro        = $entradas - $saidas;
                ?>
                    <tr class="<?php echo ($lucro >= 0) ? 'table-success' : 'table-danger'; ?>">
                        <td><?php echo htmlspecialchars(date('F Y', strtotime($mesAno . '-01'))); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($entradas, 2, ',', '.')); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($saidas, 2, ',', '.')); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($lucro, 2, ',', '.')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('fluxoCaixaChart').getContext('2d');
        const fluxoCaixaChart = new Chart(ctx, {
            type: 'bar', 
            data: {
                labels: <?php echo json_encode($labels); ?>, 
                datasets: [
                    {
                        label: 'Entradas (R$)',
                        data: <?php echo json_encode($dataEntradas); ?>, 
                        backgroundColor: 'rgba(75, 192, 192, 0.6)', 
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Saídas (R$)',
                        data: <?php echo json_encode($dataSaidas); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)', 
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        type: 'line', 
                        label: 'Lucro Líquido (R$)',
                        data: <?php echo json_encode($dataLucro); ?>, 
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
                                let label = context.dataset.label || '';
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