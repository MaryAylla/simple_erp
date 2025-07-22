<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';        
require_once __DIR__ . '/../../includes/functions.php';   
require_once __DIR__ . '/../../includes/auth.php';      
require_once __DIR__ . '/../../includes/produto_functions.php'; 

verificar_login();

$redirecionarPara = 'index.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome               = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
            $descricao          = trim(isset($_POST['descricao']) ? $_POST['descricao'] : '');
            $preco              = isset($_POST['preco']) ? (float)str_replace(',', '.', $_POST['preco']) : 0.00; 
            $quantidade_estoque = isset($_POST['quantidade_estoque']) ? (int)$_POST['quantidade_estoque'] : 0;
            $categoria_id       = isset($_POST['categoria_id']) && $_POST['categoria_id'] !== '' ? (int)$_POST['categoria_id'] : null;
            $foto_url           = null;

            if (empty($nome) || $preco <= 0 || $quantidade_estoque < 0) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Nome, preço (maior que 0) e quantidade em estoque (não negativa) são obrigatórios.';
                $redirecionarPara = 'adicionar.php';
            } else {
                $produtoExistente = buscarProdutoPorNome($nome);
                if ($produtoExistente) {
                    $_SESSION['status'] = 'danger';
                    $_SESSION['msg'] = 'Já existe um produto com este nome.';
                    $redirecionarPara = 'adicionar.php';
                } else {
                    if (isset($_FILES['foto_produto']) && $_FILES['foto_produto']['error'] === UPLOAD_ERR_OK) {
                        $target_dir = __DIR__ . "/../../assets/img/produtos/"; 
                        if (!is_dir($target_dir)) { 
                            mkdir($target_dir, 0777, true);
                        }
                        
                        $imageFileType = strtolower(pathinfo($_FILES['foto_produto']['name'], PATHINFO_EXTENSION));
                        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
                        $max_size = 2 * 1024 * 1024; 

                        if (!in_array($imageFileType, $allowed_types)) {
                            $_SESSION['status'] = 'danger';
                            $_SESSION['msg'] = 'Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.';
                            $redirecionarPara = 'adicionar.php';
                        } elseif ($_FILES['foto_produto']['size'] > $max_size) {
                            $_SESSION['status'] = 'danger';
                            $_SESSION['msg'] = 'O arquivo é muito grande. Máximo 2MB.';
                            $redirecionarPara = 'adicionar.php';
                        } else {
                            $new_file_name = uniqid('prod_') . '.' . $imageFileType;
                            $target_file = $target_dir . $new_file_name;

                            if (move_uploaded_file($_FILES['foto_produto']['tmp_name'], $target_file)) {
                                $foto_url = '/erp_simples/assets/img/produtos/' . $new_file_name; 
                            } else {
                                $_SESSION['status'] = 'warning';
                                $_SESSION['msg'] = 'A foto não pôde ser carregada. Produto salvo sem foto.';
                            }
                        }
                    }

                    if (isset($_SESSION['status']) && $_SESSION['status'] === 'danger' && $redirecionarPara === 'adicionar.php') {
                    } else {
                        $dadosProduto = array(
                            'nome'                  => $nome,
                            'descricao'             => !empty($descricao) ? $descricao : null,
                            'preco'                 => $preco,
                            'quantidade_estoque'    => $quantidade_estoque,
                            'categoria_id'          => $categoria_id,
                            'foto_url'              => $foto_url
                        );

                        $produtoId = criarProduto($dadosProduto);

                        if ($produtoId) {
                            $_SESSION['status'] = 'success';
                            $_SESSION['msg'] = 'Produto "' . htmlspecialchars($nome) . '" cadastrado com sucesso! ID: ' . $produtoId;
                        } else {
                            $_SESSION['status'] = 'danger';
                            $_SESSION['msg'] = 'Erro ao cadastrar produto no banco de dados.';
                            $redirecionarPara = 'adicionar.php';
                        }
                    }
                }
            }
        } else {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'Método de requisição inválido para criação.';
        }
        break; 

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id                 = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $nome               = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
            $descricao          = trim(isset($_POST['descricao']) ? $_POST['descricao'] : '');
            $preco              = isset($_POST['preco']) ? (float)str_replace(',', '.', $_POST['preco']) : 0.00;
            $quantidade_estoque = isset($_POST['quantidade_estoque']) ? (int)$_POST['quantidade_estoque'] : 0;
            $categoria_id       = isset($_POST['categoria_id']) && $_POST['categoria_id'] !== '' ? (int)$_POST['categoria_id'] : null;
            $foto_url_existente = isset($_POST['foto_url_existente']) ? $_POST['foto_url_existente'] : null; 
            $manter_foto        = isset($_POST['manter_foto']) ? true : false;

            if ($id <= 0 || empty($nome) || $preco <= 0 || $quantidade_estoque < 0) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Dados inválidos para atualização.';
                $redirecionarPara = 'editar.php?id=' . (string)$id;
            } else {
                $produtoExistente = buscarProdutoPorNome($nome, $id);
                if ($produtoExistente) {
                    $_SESSION['status'] = 'danger';
                    $_SESSION['msg'] = 'Já existe outro produto com este nome.';
                    $redirecionarPara = 'editar.php?id=' . (string)$id;
                } else {
                    $foto_url_nova = null;
                    if (isset($_FILES['foto_produto']) && $_FILES['foto_produto']['error'] === UPLOAD_ERR_OK) {
                        $target_dir = __DIR__ . "/../../assets/img/produtos/";
                        if (!is_dir($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }
                        
                        $imageFileType = strtolower(pathinfo($_FILES['foto_produto']['name'], PATHINFO_EXTENSION));
                        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
                        $max_size = 2 * 1024 * 1024; 

                        if (!in_array($imageFileType, $allowed_types)) {
                            $_SESSION['status'] = 'danger';
                            $_SESSION['msg'] = 'Apenas arquivos JPG, JPEG, PNG e GIF são permitidos para a foto.';
                            $redirecionarPara = 'editar.php?id=' . (string)$id;
                        } elseif ($_FILES['foto_produto']['size'] > $max_size) {
                            $_SESSION['status'] = 'danger';
                            $_SESSION['msg'] = 'A foto é muito grande (máximo 2MB).';
                            $redirecionarPara = 'editar.php?id=' . (string)$id;
                        } else {
                            $new_file_name = uniqid('prod_') . '.' . $imageFileType;
                            $target_file = $target_dir . $new_file_name;
                            if (move_uploaded_file($_FILES['foto_produto']['tmp_name'], $target_file)) {
                                $foto_url_nova = '/erp_simples/assets/img/produtos/' . $new_file_name;
                                if (!empty($foto_url_existente) && file_exists(__DIR__ . '/../..' . $foto_url_existente)) {
                                    unlink(__DIR__ . '/../..' . $foto_url_existente);
                                }
                            } else {
                                $_SESSION['status'] = 'warning';
                                $_SESSION['msg'] = 'Problema no upload da nova foto. Produto atualizado sem nova foto.';
                            }
                        }
                    } elseif (!$manter_foto) { 
                        $foto_url_nova = null; 
                        if (!empty($foto_url_existente) && file_exists(__DIR__ . '/../..' . $foto_url_existente)) {
                            unlink(__DIR__ . '/../..' . $foto_url_existente);
                        }
                    } else { 
                        $foto_url_nova = $foto_url_existente; 
                    }

                    if (isset($_SESSION['status']) && $_SESSION['status'] === 'danger' && $redirecionarPara === 'editar.php?id=' . (string)$id) {
                    } else {
                        $dadosProduto = array(
                            'nome'                  => $nome,
                            'descricao'             => !empty($descricao) ? $descricao : null,
                            'preco'                 => $preco,
                            'quantidade_estoque'    => $quantidade_estoque,
                            'categoria_id'          => $categoria_id,
                            'foto_url'              => $foto_url_nova 
                        );

                        if (atualizarProduto($id, $dadosProduto)) {
                            $_SESSION['status'] = 'success';
                            $_SESSION['msg'] = 'Produto "' . htmlspecialchars($nome) . '" (ID: ' . $id . ') atualizado com sucesso!';
                        } else {
                            $_SESSION['status'] = 'info';
                            $_SESSION['msg'] = 'Nenhuma alteração foi feita ou ocorreu um erro ao atualizar o produto.';
                            $redirecionarPara = 'editar.php?id=' . (string)$id;
                        }
                    }
                }
            }
        } else {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'Método de requisição inválido para atualização.';
        }
        break; 

    case 'delete':
        if (isset($_GET['id'])) {
            $produtoId = (int)(isset($_GET['id']) ? $_GET['id'] : 0);

            $nomeProdutoDeletado = 'produto desconhecido';
            $produtoParaMsg = buscarProdutoPorId($produtoId);
            if ($produtoParaMsg) {
                $nomeProdutoDeletado = htmlspecialchars(isset($produtoParaMsg['nome']) ? $produtoParaMsg['nome'] : 'produto desconhecido');
                if (!empty($produtoParaMsg['foto_url']) && file_exists(__DIR__ . '/../..' . $produtoParaMsg['foto_url'])) {
                    unlink(__DIR__ . '/../..' . $produtoParaMsg['foto_url']);
                }
            }

            if (deletarProduto($produtoId)) {
                $_SESSION['status'] = 'success';
                $_SESSION['msg'] = 'Produto "' . $nomeProdutoDeletado . '" (ID: ' . $produtoId . ') excluído com sucesso!';
            } else {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Erro ao excluir produto (ID: ' . $produtoId . '). Pode ser que ele não exista ou ocorreu um erro.';
            }
        } else {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'ID do produto não fornecido para exclusão.';
        }
        break; 

    default:
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Ação inválida ou não especificada.';
        break;
}

header('Location: ' . $redirecionarPara);
exit();