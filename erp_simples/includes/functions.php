<?php

function exibirMensagem() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $mensagem = '';
    $tipoMensagem = '';

    if (isset($_SESSION['status'])) {
        $mensagem = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
        $tipoMensagem = $_SESSION['status'];

        unset($_SESSION['status']);
        unset($_SESSION['msg']);
    }
    if (!empty($mensagem)) {
        echo '<div class="alert alert-' . htmlspecialchars($tipoMensagem) . ' mt-3" role="alert">';
        echo htmlspecialchars($mensagem);
        echo '</div>';
    }
}
?>