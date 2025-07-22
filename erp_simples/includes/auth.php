<?php

function verificar_login() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        $_SESSION['status'] = 'danger'; 
        $_SESSION['msg'] = 'Você precisa fazer login para acessar esta página.';
        header('Location: /erp_simples/login.php');
        exit();
    }
}

function fazer_logout() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
?>