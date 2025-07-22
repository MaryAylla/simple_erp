<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/venda_functions.php';
require_once __DIR__ . '/../../includes/produto_functions.php';

verificar_login();

$redirecionarPara = 'index.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clienteId = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : 0;
            $valorTotal = isset($_POST['valor_total']) ? (float)str_replace(',', '.', $_POST['valor_total']) : 0.00;
            $produtosItens = isset($_POST['produtos']) ? $_POST['produtos'] : array();

            if ($clienteId <= 0) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Selecione um cliente para a venda.';
                $redirecionarPara = 'registrar_pedido.php';
            } elseif (empty($produtosItens)) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'A venda deve ter pelo menos um item.';
                $redirecionarPara = 'registrar_pedido.php';
            } elseif ($valorTotal <= 0) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'O valor total da venda deve ser maior que zero.';
                $redirecionarPara = 'registrar_pedido.php';
            }
            else {
                $errosItens = array();
                $produtosParaVenda = array();

                foreach ($produtosItens as $key => $item) {
                    $produtoId = isset($item['id']) ? (int)$item['id'] : 0;
                    $quantidade = isset($item['quantidade']) ? (int)$item['quantidade'] : 0;
                    $precoUnitario = isset($item['preco_unitario']) ? (float)str_replace(',', '.', $item['preco_unitario']) : 0.00;

                    if ($produtoId <= 0 || $quantidade <= 0 || $precoUnitario <= 0) {
                        $errosItens[] = "Item " . ((string)$key + 1) . ": Dados inválidos (produto, quantidade ou preço).";
                        continue;
                    }

                    $produtoDB = buscarProdutoPorId($produtoId);

                    if (!$produtoDB || (isset($produtoDB['quantidade_estoque']) ? $produtoDB['quantidade_estoque'] : 0) < $quantidade) {
                        $errosItens[] = "Item " . ((string)$key + 1) . ": Produto '" . (isset($produtoDB['nome']) ? $produtoDB['nome'] : 'Desconhecido') . "' sem estoque suficiente ou não encontrado. (Estoque: " . (isset($produtoDB['quantidade_estoque']) ? $produtoDB['quantidade_estoque'] : '0') . ", Pedido: " . $quantidade . ")";
                        continue;
                    }

                    $produtosParaVenda[] = array(
                        'produto_id'     => $produtoId,
                        'quantidade'     => $quantidade,
                        'preco_unitario' => $precoUnitario
                    );
                }

                if (!empty($errosItens)) {
                    $_SESSION['status'] = 'danger';
                    $_SESSION['msg'] = 'Erros nos itens do pedido: <br>' . implode('<br>', $errosItens);
                    $redirecionarPara = 'registrar_pedido.php';
                } elseif (empty($produtosParaVenda)) {
                    $_SESSION['status'] = 'danger';
                    $_SESSION['msg'] = 'Nenhum item válido foi adicionado ao pedido.';
                    $redirecionarPara = 'registrar_pedido.php';
                }
                else {
                    $vendaId = criarVenda($clienteId, $valorTotal);

                    if ($vendaId) {
                        $sucessoItens = true;
                        foreach ($produtosParaVenda as $item) {
                            if (!adicionarItemVenda($vendaId, $item['produto_id'], $item['quantidade'], $item['preco_unitario'])) {
                                $sucessoItens = false;
                                break;
                            }
                        }

                        if ($sucessoItens) {
                            $_SESSION['status'] = 'success';
                            $_SESSION['msg'] = 'Venda registrada com sucesso! ID da Venda: ' . $vendaId;
                        } else {
                            $_SESSION['status'] = 'danger';
                            $_SESSION['msg'] = 'Erro ao adicionar itens da venda. Venda não registrada completamente. Estoque não alterado.';
                        }

                    } else {
                        $_SESSION['status'] = 'danger';
                        $_SESSION['msg'] = 'Erro ao registrar a venda principal.';
                    }
                }
            }
        } else {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'Método de requisição inválido para registrar venda.';
        }
        break;

    case 'delete':
        if (isset($_GET['id'])) {
            $vendaId = (int)(isset($_GET['id']) ? $_GET['id'] : 0);

            $vendaParaMsg = buscarVendaPorId($vendaId);
            $clienteNome = 'Venda Desconhecida';
            if ($vendaParaMsg && isset($vendaParaMsg['cliente_nome'])) {
                $clienteNome = htmlspecialchars($vendaParaMsg['cliente_nome']);
            }

            if (deletarVenda($vendaId)) {
                $_SESSION['status'] = 'success';
                $_SESSION['msg'] = 'Venda do cliente "' . $clienteNome . '" (ID: ' . $vendaId . ') excluída com sucesso! Estoque reabastecido.';
            } else {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Erro ao excluir venda (ID: ' . $vendaId . '). Pode ser que ela não exista ou ocorreu um erro.';
            }
        } else {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'ID da venda não fornecido para exclusão.';
        }
        break;

    default:
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Ação inválida ou não especificada.';
        break;
}

header('Location: ' . $redirecionarPara);
exit();