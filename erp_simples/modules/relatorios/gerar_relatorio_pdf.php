<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/venda_functions.php';
require_once __DIR__ . '/../../includes/financeiro_functions.php';

require_once __DIR__ . '/../../includes/dompdf/autoload.inc.php';

verificar_login();

use Dompdf\Dompdf;
use Dompdf\Options;

$reportType = isset($_GET['type']) ? $_GET['type'] : '';
$htmlContent = '';
$filename = 'relatorio_';
$reportTitle = 'Relatório Geral';

switch ($reportType) {
    case 'top_clientes':
        $reportTitle = 'Relatório de Clientes que Mais Compram';
        $filename .= 'top_clientes';
        $data = getClientesQueMaisCompram(10);

        $htmlContent = '
            <h1>' . $reportTitle . '</h1>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Total Comprado</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($data as $item) {
            $htmlContent .= '
                    <tr>
                        <td>' . htmlspecialchars(isset($item['cliente_nome']) ? $item['cliente_nome'] : '') . '</td>
                        <td>R$ ' . htmlspecialchars(number_format(isset($item['total_comprado']) ? $item['total_comprado'] : 0, 2, ',', '.')) . '</td>
                    </tr>';
        }
        $htmlContent .= '
                </tbody>
            </table>';
        break;

    case 'top_produtos':
        $reportTitle = 'Relatório de Produtos Mais Vendidos';
        $filename .= 'top_produtos';
        $data = getProdutosMaisVendidos(10);

        $htmlContent = '
            <h1>' . $reportTitle . '</h1>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Qtd. Vendida</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($data as $item) {
            $htmlContent .= '
                    <tr>
                        <td>' . htmlspecialchars(isset($item['produto_nome']) ? $item['produto_nome'] : '') . '</td>
                        <td>' . htmlspecialchars(isset($item['total_vendido']) ? $item['total_vendido'] : '') . '</td>
                    </tr>';
        }
        $htmlContent .= '
                </tbody>
            </table>';
        break;

    case 'vendas_por_mes_produto':
        $reportTitle = 'Relatório de Vendas por Mês e por Produto';
        $filename .= 'vendas_mes_produto';
        $data = getRelatorioVendasPorMesEProduto();

        $htmlContent = '
            <h1>' . $reportTitle . '</h1>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Mês/Ano</th>
                        <th>Produto</th>
                        <th>Qtd. Vendida</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($data as $item) {
            $htmlContent .= '
                    <tr>
                        <td>' . htmlspecialchars(isset($item['mes_ano']) ? date('F Y', strtotime($item['mes_ano'] . '-01')) : '') . '</td>
                        <td>' . htmlspecialchars(isset($item['produto_nome']) ? $item['produto_nome'] : '') . '</td>
                        <td>' . htmlspecialchars(isset($item['total_vendido_quantidade']) ? $item['total_vendido_quantidade'] : '') . '</td>
                        <td>R$ ' . htmlspecialchars(number_format(isset($item['total_vendido_valor']) ? $item['total_vendido_valor'] : 0, 2, ',', '.')) . '</td>
                    </tr>';
        }
        $htmlContent .= '
                </tbody>
            </table>';
        break;

    case 'fluxo_caixa_mensal':
        $reportTitle = 'Relatório de Fluxo de Caixa Mensal';
        $filename .= 'fluxo_caixa_mensal';
        $data = buscarEntradasSaidasPorMes();

        $htmlContent = '
            <h1>' . $reportTitle . '</h1>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Mês/Ano</th>
                        <th>Total Entradas</th>
                        <th>Total Saídas</th>
                        <th>Lucro Líquido</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($data as $item) {
            $mesAno = isset($item['mes_ano']) ? $item['mes_ano'] : 'N/A';
            $entradas = isset($item['total_entradas']) ? (float)$item['total_entradas'] : 0;
            $saidas = isset($item['total_saidas']) ? (float)$item['total_saidas'] : 0;
            $lucro = $entradas - $saidas;

            $htmlContent .= '
                    <tr>
                        <td>' . htmlspecialchars(date('F Y', strtotime($mesAno . '-01'))) . '</td>
                        <td>R$ ' . htmlspecialchars(number_format($entradas, 2, ',', '.')) . '</td>
                        <td>R$ ' . htmlspecialchars(number_format($saidas, 2, ',', '.')) . '</td>
                        <td>R$ ' . htmlspecialchars(number_format($lucro, 2, ',', '.')) . '</td>
                    </tr>';
        }
        $htmlContent .= '
                </tbody>
            </table>';
        break;

    default:
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Tipo de relatório inválido.';
        header('Location: index.php');
        exit();
}

$finalHtml = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $reportTitle . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 10px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 20px;
            color: #333;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <p style="text-align: right; font-size: 10px;">Gerado em: ' . date('d/m/Y H:i:s') . '</p>
        ' . $htmlContent . '
        <div class="footer">
            ERP Simples - Seu Sistema de Gestão Empresarial.
        </div>
    </div>
</body>
</html>';

$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($finalHtml);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream($filename . "_" . date('Ymd') . ".pdf", array("Attachment" => false));

exit(0);
?>