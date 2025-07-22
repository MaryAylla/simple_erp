<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/database.php'; 
require_once 'includes/functions.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $confirmar_senha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $_SESSION['status'] = 'danger'; 
        $_SESSION['msg'] = 'Por favor, preencha todos os campos.';
        header('Location: register.php');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Formato de e-mail inválido.';
        header('Location: register.php');
        exit();
    }

    if ($senha !== $confirmar_senha) {
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'As senhas não coincidem.';
        header('Location: register.php');
        exit();
    }

    error_reporting(E_ALL); 
    ini_set('display_errors', 1); 
    
    echo '<pre>';
    echo 'DEBUG_REGISTRO: Tentando verificar se o e-mail existe no BD...<br>';
    echo 'DEBUG_REGISTRO: E-mail a ser verificado: ' . htmlspecialchars($email) . '<br>';
    echo 'DEBUG_REGISTRO: Variável $pdo é um objeto PDO? ' . (is_object($pdo) && $pdo instanceof PDO ? 'Sim' : 'Não') . '<br>';
    echo 'DEBUG_REGISTRO: Status da conexão PDO: ' . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . '<br>'; 
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
        echo 'DEBUG_REGISTRO: Statement preparado com sucesso.<br>';
        $stmt->execute([$email]);
        echo 'DEBUG_REGISTRO: Statement executado com sucesso.<br>';
        $count = $stmt->fetchColumn();
        echo 'DEBUG_REGISTRO: Contagem de e-mails encontrados: ' . $count . '<br>';

        if ($count > 0) {
            $_SESSION['status'] = 'danger';
            $_SESSION['msg'] = 'Este e-mail já está cadastrado.';
            header('Location: register.php');
            exit();
        }
    } catch (PDOException $e) {
        die("DEBUG_REGISTRO: Erro FATAL na verificação de email: " . $e->getMessage() . "<br>SQLState: " . $e->getCode());
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $senha_hash, 'admin', true]); 

        $_SESSION['status'] = 'success'; 
        $_SESSION['msg'] = 'Registro realizado com sucesso! Faça login.';
        header('Location: login.php');
        exit();

    } catch (PDOException $e) {
        error_log("Erro ao registrar usuário: " . $e->getMessage());
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Ocorreu um erro ao registrar. Tente novamente.';
        header('Location: register.php');
        exit();
    }

} else {
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'Método de requisição inválido.';
    header('Location: register.php');
    exit();
}
?>