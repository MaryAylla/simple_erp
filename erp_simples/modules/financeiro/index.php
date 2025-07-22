<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/financeiro_functions.php'; 

verificar_login();

$pageTitle = "Fluxo de Caixa";

$saldoAtual = calcularSaldoAtual(); 

$tipoFiltro = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$mesAnoFiltro = isset($_GET['mes_ano']) ? $_GET['mes_ano'] : '';

$movimentacoes = buscarMovimentacoes($tipoFiltro, $mesAnoFiltro);

$mesesAnosComMovimento = array();
$primeiroAno = 2023; 
$anoAtual = date('Y');
$mesAtual = date('m');
for ($y = $anoAtual; $y >= $primeiroAno; $y--) {
    for ($m = 12; $m >= 1; $m--) {
        $mes = str_pad($m, 2, '0', STR_PAD_LEFT); 
        $mesAno = $y . '-' . $mes;
        if ($y == $anoAtual && $m > $mesAtual) {
            continue;
        }
        $mesesAnosComMovimento[$mesAno] = date('F Y', strtotime($mesAno . '-01')); 
    }
}


include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mb-4">Fluxo de Caixa</h1>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center <?php echo ($saldoAtual >= 0) ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
            <div class="card-body">
                <h5 class="card-title">Saldo Atual</h5>
                <p class="card-text fs-2">R$ <?php echo htmlspecialchars(number_format($saldoAtual, 2, ',', '.')); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-8 text-end d-flex align-items-center justify-content-end">
        <a href="registrar_movimento.php" class="btn btn-primary me-2">Registrar Nova Movimentação</a>
        <a href="relatorios.php" class="btn btn-info">Ver Relatórios Financeiros</a>
    </div>
</div>

<h2 class="mb-3">Movimentações Recentes</h2>

<div class="row mb-3 align-items-center">
    <div class="col-md-12">
        <form action="index.php" method="GET" class="d-flex align-items-center">
            <select class="form-select me-2" name="tipo">
                <option value="">Todos os Tipos</option>
                <option value="entrada" <?php echo ($tipoFiltro == 'entrada') ? 'selected' : ''; ?>>Entradas</option>
                <option value="saida" <?php echo ($tipoFiltro == 'saida') ? 'selected' : ''; ?>>Saídas</option>
            </select>
            <select class="form-select me-2" name="mes_ano">
                <option value="">Todos os Meses/Anos</option>
                <?php foreach ($mesesAnosComMovimento as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>" 
                        <?php echo ($mesAnoFiltro == $value) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-outline-secondary" type="submit">Filtrar</button>
            <?php if (!empty($tipoFiltro) || !empty($mesAnoFiltro)): ?>
                <a href="index.php" class="btn btn-outline-danger ms-2">Limpar Filtros</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (empty($movimentacoes)): ?>
    <div class="alert alert-info" role="alert">
        Nenhuma movimentação encontrada<?php echo (!empty($tipoFiltro) || !empty($mesAnoFiltro)) ? " para os filtros aplicados" : ""; ?>.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimentacoes as $mov): ?>
                    <tr class="<?php echo (isset($mov['tipo']) && $mov['tipo'] == 'entrada') ? 'table-success' : 'table-danger'; ?>">
                        <td><?php echo htmlspecialchars(isset($mov['id']) ? $mov['id'] : ''); ?></td>
                        <td>
                            <?php if (isset($mov['tipo'])): ?>
                                <?php echo htmlspecialchars($mov['tipo'] == 'entrada' ? 'Entrada' : 'Saída'); ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars(isset($mov['descricao']) ? $mov['descricao'] : ''); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format(isset($mov['valor']) ? $mov['valor'] : 0, 2, ',', '.')); ?></td>
                        <td><?php echo htmlspecialchars(isset($mov['data_movimento']) ? date('d/m/Y H:i', strtotime($mov['data_movimento'])) : ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>