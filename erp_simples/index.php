<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    }

if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header('Location: dashboard.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
?>