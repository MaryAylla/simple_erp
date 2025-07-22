<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/database.php'; 
require_once 'includes/functions.php'; 

function buscarUsuarioPorEmailERP($email) {
    global $pdo;
    try {
        $sql = "SELECT id, nome, email, senha_hash, perfil, ativo FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar usuário por email ERP: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    if (empty($email) || empty($senha)) {
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'Por favor, preencha todos os campos.';
        header('Location: login.php');
        exit();
    }

    $usuarioAutenticado = buscarUsuarioPorEmailERP($email);

    if ($usuarioAutenticado && $usuarioAutenticado['ativo'] && password_verify($senha, $usuarioAutenticado['senha_hash'])) {
        $_SESSION['logado'] = true;
        $_SESSION['usuario_id'] = $usuarioAutenticado['id'];
        $_SESSION['usuario_nome'] = $usuarioAutenticado['nome'];
        $_SESSION['usuario_email'] = $usuarioAutenticado['email'];
        $_SESSION['usuario_perfil'] = $usuarioAutenticado['perfil']; 

        $_SESSION['status'] = 'success';
        $_SESSION['msg'] = 'Login realizado com sucesso! Bem-vindo(a), ' . htmlspecialchars($usuarioAutenticado['nome']) . '.';
        header('Location: dashboard.php'); 
        exit();
    } else {
        $_SESSION['status'] = 'danger';
        $_SESSION['msg'] = 'E-mail ou senha incorretos, ou sua conta está inativa.';
        header('Location: login.php');
        exit();
    }

} else {
    $_SESSION['status'] = 'danger';
    $_SESSION['msg'] = 'Método de requisição inválido.';
    header('Location: login.php');
    exit();
}
?>