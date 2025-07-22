<?php

require_once __DIR__ . '/includes/auth.php';

fazer_logout();


session_start(); 
$_SESSION['status'] = 'success';
$_SESSION['msg'] = 'Você foi desconectado com sucesso.';
header('Location: /erp_simples/login.php'); 
exit();
?>