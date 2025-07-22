<?php

$db_host = 'localhost';
$db_name = 'erp_simples'; 
$db_user = 'root'; 
$db_pass = '';     

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4"; 

    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados ERP: " . $e->getMessage());
}
?>