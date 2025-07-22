<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/venda_functions.php';

require_once __DIR__ . '/../../includes/dompdf/autoload.inc.php';

verificar_login();

use Dompdf\Dompdf;
use Dompdf\Options;

$vendaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($vendaId <= 0) {
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'ID da venda não fornecido para gerar o recibo.';
    header('Location: index.php');
    exit();
}

$venda = buscarVendaPorId($vendaId);

error_log("DEBUG: Conteúdo de \$venda em gerar_recibo_pdf.php: " . print_r($venda, true));

if (!$venda) {
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'Venda não encontrada para o ID: ' . $vendaId;
    header('Location: index.php');
    exit();
}

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo de Venda #' . htmlspecialchars($venda['id']) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .details, .items {
            margin-bottom: 20px;
        }
        .details p {
            margin: 5px 0;
        }
        .items table {
            width: 100%;
            border-collapse: collapse;
        }
        .items th, .items td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
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
        <div class="header">
            <h1>RECIBO DE VENDA</h1>
            <p><strong>ERP Simples</strong></p>
            <p>Data de Emissão: ' . date('d/m/Y H:i:s') . '</p>
        </div>

        <div class="details">
            <p><strong>Venda ID:</strong> ' . htmlspecialchars($venda['id']) . '</p>
            <p><strong>Data da Venda:</strong> ' . htmlspecialchars(date('d/m/Y H:i', strtotime($venda['data_venda']))) . '</p>
            <p><strong>Cliente:</strong> ' . htmlspecialchars($venda['cliente_nome']) . ' (CPF/CNPJ: ' . htmlspecialchars($venda['cliente_cpf_cnpj']) . ')</p>
        </div>

        <div class="items">
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Qtd</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>';

foreach ($venda['itens'] as $item) {
    $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item['produto_nome']) . '</td>
                        <td>' . htmlspecialchars($item['quantidade']) . '</td>
                        <td>R$ ' . htmlspecialchars(number_format($item['preco_unitario'], 2, ',', '.')) . '</td>
                        <td>R$ ' . htmlspecialchars(number_format($item['subtotal'], 2, ',', '.')) . '</td>
                    </tr>';
}

$html .= '
                </tbody>
            </table>
        </div>

        <div class="total">
            Valor Total: R$ ' . htmlspecialchars(number_format($venda['valor_total'], 2, ',', '.')) . '
        </div>

        <div class="footer">
            ERP Simples - Seu Sistema de Gestão Empresarial.
            <br>
            Este é um recibo gerado automaticamente.
        </div>
    </div>
</body>
</html>';

$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream("recibo_venda_" . $venda['id'] . ".pdf", array("Attachment" => false));

exit(0);
?>