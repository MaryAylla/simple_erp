<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/financeiro_functions.php'; 

verificar_login();

$redirecionarPara = 'index.php'; 

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipo       = trim(isset($_POST['tipo']) ? $_POST['tipo'] : '');
            $descricao  = trim(isset($_POST['descricao']) ? $_POST['descricao'] : '');
            $valor      = isset($_POST['valor']) ? (float)str_replace(',', '.', $_POST['valor']) : 0.00; 

            if (!($tipo == 'entrada' || $tipo == 'saida')) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Tipo de movimentação inválido.';
                $redirecionarPara = 'registrar_movimento.php';
            } elseif (empty($descricao)) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'A descrição da movimentação é obrigatória.';
                $redirecionarPara = 'registrar_movimento.php';
            } elseif ($valor <= 0) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'O valor da movimentação deve ser maior que zero.';
                $redirecionarPara = 'registrar_movimento.php';
            } else {
                $movimentoId = registrarMovimento($tipo, $descricao, $valor);

                if ($movimentoId) {
                    $_SESSION['status'] = 'success';
                    $_SESSION['msg'] = 'Movimentação (' . htmlspecialchars($tipo) . ') registrada com sucesso! ID: ' . $movimentoId;
                } else {
                    $_SESSION['status'] = 'danger';
                    $_SESSION['msg'] = 'Erro ao registrar movimentação financeira.';
                    $redirecionarPara = 'registrar_movimento.php';
                }
            }
        } else {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'Método de requisição inválido para registrar movimentação.';
        }
        break; 
    
    default:
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Ação inválida ou não especificada.';
        break;
}

header('Location: ' . $redirecionarPara);
exit();