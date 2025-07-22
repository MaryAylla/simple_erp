<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';        
require_once __DIR__ . '/../../includes/functions.php';   
require_once __DIR__ . '/../../includes/auth.php';       
require_once __DIR__ . '/../../includes/usuario_functions.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true || 
    !isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
    
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'Acesso negado. Apenas administradores podem gerenciar usuários.';
    header('Location: ' . (__DIR__ . '/../../dashboard.php'));
    exit();
}

$redirecionarPara = 'index.php'; 

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome            = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
            $email           = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $senha           = isset($_POST['senha']) ? $_POST['senha'] : '';
            $confirmar_senha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';
            $perfil          = isset($_POST['perfil']) ? $_POST['perfil'] : 'vendedor';
            $ativo           = isset($_POST['ativo']) ? true : false; 

            if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Por favor, preencha todos os campos obrigatórios.';
                $redirecionarPara = 'adicionar.php';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Formato de e-mail inválido.';
                $redirecionarPara = 'adicionar.php';
            } elseif ($senha !== $confirmar_senha) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'As senhas não coincidem.';
                $redirecionarPara = 'adicionar.php';
            } else {
                $emailExiste = buscarUsuarioSistemaPorEmail($email);
                if ($emailExiste) {
                    $_SESSION['status'] = 'danger';
                    $_SESSION['msg'] = 'E-mail já cadastrado para outro usuário do sistema.';
                    $redirecionarPara = 'adicionar.php';
                } else {
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                    $dadosUsuario = array(
                        'nome'       => $nome,
                        'email'      => $email,
                        'senha_hash' => $senha_hash,
                        'perfil'     => $perfil,
                        'ativo'      => $ativo
                    );

                    $usuarioId = criarUsuarioSistema($dadosUsuario);

                    if ($usuarioId) {
                        $_SESSION['status'] = 'success';
                        $_SESSION['msg'] = 'Usuário "' . htmlspecialchars($nome) . '" cadastrado com sucesso! ID: ' . $usuarioId;
                    } else {
                        $_SESSION['status'] = 'danger';
                        $_SESSION['msg'] = 'Erro ao cadastrar usuário do sistema.';
                        $redirecionarPara = 'adicionar.php';
                    }
                }
            }
        } else {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'Método de requisição inválido para criação de usuário.';
        }
        break; 

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id              = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $nome            = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
            $email           = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $senha           = isset($_POST['senha']) ? $_POST['senha'] : ''; 
            $confirmar_senha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';
            $perfil          = isset($_POST['perfil']) ? $_POST['perfil'] : 'vendedor';
            $ativo           = isset($_POST['ativo']) ? true : false;

            if ($id <= 0 || empty($nome) || empty($email)) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Dados inválidos para atualização.';
                $redirecionarPara = 'editar.php?id=' . (string)$id;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Formato de e-mail inválido.';
                $redirecionarPara = 'editar.php?id=' . (string)$id;
            } elseif (!empty($senha) && $senha !== $confirmar_senha) { 
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'As senhas não coincidem.';
                $redirecionarPara = 'editar.php?id=' . (string)$id;
            } else {
                $emailExiste = buscarUsuarioSistemaPorEmail($email, $id);
                if ($emailExiste) {
                    $_SESSION['status'] = 'danger';
                    $_SESSION['msg'] = 'E-mail já cadastrado para outro usuário do sistema.';
                    $redirecionarPara = 'editar.php?id=' . (string)$id;
                } else {
                    $dadosUsuario = array(
                        'nome'   => $nome,
                        'email'  => $email,
                        'perfil' => $perfil,
                        'ativo'  => $ativo
                    );

                    if (!empty($senha)) {
                        $dadosUsuario['senha_hash'] = password_hash($senha, PASSWORD_DEFAULT);
                    }

                    if (atualizarUsuarioSistema($id, $dadosUsuario)) {
                        $_SESSION['status'] = 'success';
                        $_SESSION['msg'] = 'Usuário "' . htmlspecialchars($nome) . '" (ID: ' . $id . ') atualizado com sucesso!';
                    } else {
                        $_SESSION['status'] = 'info';
                        $_SESSION['msg'] = 'Nenhuma alteração foi feita ou ocorreu um erro ao atualizar o usuário.';
                        $redirecionarPara = 'editar.php?id=' . (string)$id;
                    }
                }
            }
        } else {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'Método de requisição inválido para atualização de usuário.';
        }
        break; 

    case 'delete':
        if (isset($_GET['id'])) {
            $usuarioId = (int)(isset($_GET['id']) ? $_GET['id'] : 0);

            if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $usuarioId) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Você não pode excluir sua própria conta enquanto está logado.';
            } else {
                $nomeUsuarioDeletado = 'usuário desconhecido';
                $usuarioParaMsg = buscarUsuarioSistemaPorId($usuarioId);
                if ($usuarioParaMsg) {
                    $nomeUsuarioDeletado = htmlspecialchars(isset($usuarioParaMsg['nome']) ? $usuarioParaMsg['nome'] : 'usuário desconhecido');
                }

                if (deletarUsuarioSistema($usuarioId)) {
                    $_SESSION['status'] = 'success';
                    $_SESSION['msg'] = 'Usuário "' . $nomeUsuarioDeletado . '" (ID: ' . $usuarioId . ') excluído com sucesso!';
                } else {
                    $_SESSION['status'] = 'danger';
                    $_SESSION['msg'] = 'Erro ao excluir usuário (ID: ' . $usuarioId . '). Pode ser que ele não exista ou ocorreu um erro.';
                }
            }
        } else {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'ID do usuário não fornecido para exclusão.';
        }
        break; 

    default:
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Ação inválida ou não especificada.';
        break;
}

header('Location: ' . $redirecionarPara);
exit();