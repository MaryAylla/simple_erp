<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';        
require_once __DIR__ . '/../../includes/functions.php';       
require_once __DIR__ . '/../../includes/auth.php';           
require_once __DIR__ . '/../../includes/cliente_functions.php'; 

verificar_login();

$redirecionarPara = 'index.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome       = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
            $cpf_cnpj   = trim(isset($_POST['cpf_cnpj']) ? $_POST['cpf_cnpj'] : '');
            $telefone   = trim(isset($_POST['telefone']) ? $_POST['telefone'] : '');
            $email      = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $endereco   = trim(isset($_POST['endereco']) ? $_POST['endereco'] : '');

            if (empty($nome)) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'O nome do cliente é obrigatório.';
                $redirecionarPara = 'adicionar.php';
            } 
            else {
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['status'] = 'danger';
                    $_SESSION['msg'] = 'Formato de e-mail inválido.';
                    $redirecionarPara = 'adicionar.php';
                } 
                else {
                    $emailExiste = (!empty($email) && buscarClientePorEmail($email));
                    $cpfCnpjExiste = (!empty($cpf_cnpj) && buscarClientePorCpfCnpj($cpf_cnpj));

                    if ($emailExiste) {
                        $_SESSION['status'] = 'danger';
                        $_SESSION['msg'] = 'E-mail já cadastrado para outro cliente.';
                        $redirecionarPara = 'adicionar.php';
                    } elseif ($cpfCnpjExiste) {
                        $_SESSION['status'] = 'danger';
                        $_SESSION['msg'] = 'CPF/CNPJ já cadastrado para outro cliente.';
                        $redirecionarPara = 'adicionar.php';
                    } else {
                        $dadosCliente = array( 
                            'nome'      => $nome,
                            'cpf_cnpj'  => !empty($cpf_cnpj) ? $cpf_cnpj : null, 
                            'telefone'  => !empty($telefone) ? $telefone : null,
                            'email'     => !empty($email) ? $email : null,
                            'endereco'  => !empty($endereco) ? $endereco : null
                        );

                        $clienteId = criarCliente($dadosCliente);

                        if ($clienteId) {
                            $_SESSION['status'] = 'success';
                            $_SESSION['msg'] = 'Cliente "' . htmlspecialchars($nome) . '" cadastrado com sucesso! ID: ' . $clienteId;
                        } else {
                            $_SESSION['status'] = 'danger';
                            $_SESSION['msg'] = 'Erro ao cadastrar cliente. Tente novamente ou verifique os dados.';
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
            $id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $nome       = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
            $cpf_cnpj   = trim(isset($_POST['cpf_cnpj']) ? $_POST['cpf_cnpj'] : '');
            $telefone   = trim(isset($_POST['telefone']) ? $_POST['telefone'] : '');
            $email      = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $endereco   = trim(isset($_POST['endereco']) ? $_POST['endereco'] : '');

            // Validação básica
            if ($id <= 0 || empty($nome)) {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Dados inválidos para atualização.';
                $redirecionarPara = 'editar.php?id=' . (string)$id;
            } else {
                // Validação de formato de e-mail se não estiver vazio
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['status'] = 'danger';
                    $_SESSION['msg'] = 'Formato de e-mail inválido.';
                    $redirecionarPara = 'editar.php?id=' . (string)$id;
                } else {
                    // Verificar se E-mail ou CPF/CNPJ já existem para OUTROS clientes
                    // AGORA PASSAMOS O ID DO CLIENTE ATUAL PARA EXCLUÍ-LO DA BUSCA DE DUPLICIDADE
                    $emailExiste = (!empty($email) && buscarClientePorEmail($email, $id)); // << PASSANDO $id AQUI
                    
                    if ($emailExiste) {
                        $_SESSION['status'] = 'danger';
                        $_SESSION['msg'] = 'E-mail já cadastrado para outro cliente.';
                        $redirecionarPara = 'editar.php?id=' . (string)$id;
                    } else {
                        $cpfCnpjExiste = (!empty($cpf_cnpj) && buscarClientePorCpfCnpj($cpf_cnpj, $id)); // << PASSANDO $id AQUI
                        
                        if ($cpfCnpjExiste) {
                            $_SESSION['status'] = 'danger';
                            $_SESSION['msg'] = 'CPF/CNPJ já cadastrado para outro cliente.';
                            $redirecionarPara = 'editar.php?id=' . (string)$id;
                        } else {
                            // Prepara os dados para a função atualizarCliente
                            $dadosCliente = array(
                                'nome'      => $nome,
                                'cpf_cnpj'  => !empty($cpf_cnpj) ? $cpf_cnpj : null,
                                'telefone'  => !empty($telefone) ? $telefone : null,
                                'email'     => !empty($email) ? $email : null,
                                'endereco'  => !empty($endereco) ? $endereco : null
                            );

                            // Chama a função para atualizar o cliente
                            if (atualizarCliente($id, $dadosCliente)) {
                                $_SESSION['status'] = 'success';
                                $_SESSION['msg'] = 'Cliente "' . htmlspecialchars($nome) . '" (ID: ' . $id . ') atualizado com sucesso!';
                            } else {
                                $_SESSION['status'] = 'info';
                                $_SESSION['msg'] = 'Nenhuma alteração foi feita ou ocorreu um erro ao atualizar o cliente.';
                            }
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
            $clienteId = (int)(isset($_GET['id']) ? $_GET['id'] : 0); 
            
            $nomeClienteDeletado = 'cliente desconhecido';
            $clienteParaMsg = buscarClientePorId($clienteId);
            if ($clienteParaMsg) {
                $nomeClienteDeletado = htmlspecialchars(isset($clienteParaMsg['nome']) ? $clienteParaMsg['nome'] : 'cliente desconhecido');
            }

            if (deletarCliente($clienteId)) {
                $_SESSION['status'] = 'success';
                $_SESSION['msg'] = 'Cliente "' . $nomeClienteDeletado . '" (ID: ' . $clienteId . ') excluído com sucesso!';
            } else {
                $_SESSION['status'] = 'danger';
                $_SESSION['msg'] = 'Erro ao excluir cliente (ID: ' . $clienteId . '). Pode ser que ele não exista ou ocorreu um erro.';
            }
        } else {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'ID do cliente não fornecido para exclusão.';
        }
        break;

    default:
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Ação inválida ou não especificada.';
        break;
}

header('Location: ' . $redirecionarPara);
exit();