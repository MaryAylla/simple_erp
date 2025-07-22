<?php

require_once __DIR__ . '/database.php';

function registrarMovimento( /*string*/ $tipo, /*string*/ $descricao, /*float*/ $valor) {
    global $pdo;
    try {
        $sql = "INSERT INTO fluxo_caixa (tipo, descricao, valor) VALUES (:tipo, :descricao, :valor)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':tipo'      => $tipo,
            ':descricao' => $descricao,
            ':valor'     => $valor
        ));
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Erro PDO ao registrar movimento financeiro: " . $e->getMessage());
        return false;
    }
}

function calcularSaldoAtual() {
    global $pdo;
    try {
        $sqlEntradas = "SELECT COALESCE(SUM(valor), 0) FROM fluxo_caixa WHERE tipo = 'entrada'";
        $stmtEntradas = $pdo->query($sqlEntradas);
        $totalEntradas = $stmtEntradas->fetchColumn();

        $sqlSaidas = "SELECT COALESCE(SUM(valor), 0) FROM fluxo_caixa WHERE tipo = 'saida'";
        $stmtSaidas = $pdo->query($sqlSaidas);
        $totalSaidas = $stmtSaidas->fetchColumn();

        return (float) $totalEntradas - (float) $totalSaidas;

    } catch (PDOException $e) {
        error_log("Erro PDO ao calcular saldo atual: " . $e->getMessage());
        return 0.00;
    }
}

function buscarMovimentacoes( /*string*/ $tipoFiltro = '', /*string*/ $mesAnoFiltro = '') {
    global $pdo;
    $sql = "SELECT id, tipo, descricao, valor, data_movimento FROM fluxo_caixa WHERE 1=1";
    $params = array();

    if (!empty($tipoFiltro) && ($tipoFiltro === 'entrada' || $tipoFiltro === 'saida')) {
        $sql .= " AND tipo = :tipo";
        $params[':tipo'] = $tipoFiltro;
    }

    if (!empty($mesAnoFiltro)) {
        $sql .= " AND DATE_FORMAT(data_movimento, '%Y-%m') = :mesAno";
        $params[':mesAno'] = $mesAnoFiltro;
    }

    $sql .= " ORDER BY data_movimento DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar movimentações financeiras: " . $e->getMessage());
        return array();
    }
}

function buscarEntradasSaidasPorMes() {
    global $pdo;
    try {
        $sql = "SELECT
                    DATE_FORMAT(data_movimento, '%Y-%m') AS mes_ano,
                    COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END), 0) AS total_entradas,
                    COALESCE(SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END), 0) AS total_saidas
                FROM fluxo_caixa
                GROUP BY mes_ano
                ORDER BY mes_ano ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar entradas e saídas por mês: " . $e->getMessage());
        return array();
    }
}

?>