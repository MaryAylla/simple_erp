<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/produto_functions.php'; 

/**
 * Cria uma nova venda (cabeçalho) no banco de dados.
 *
 * @param int $clienteId O ID do cliente associado à venda.
 * @param float $valorTotal O valor total da venda.
 * @return int|false O ID da venda inserida ou false em caso de erro.
 */
function criarVenda( $clienteId, $valorTotal) {
    global $pdo;
    try {
        $pdo->beginTransaction(); 

        $sql = "INSERT INTO vendas (cliente_id, valor_total) VALUES (:cliente_id, :valor_total)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':cliente_id' => $clienteId,
            ':valor_total' => $valorTotal
        ));

        $vendaId = $pdo->lastInsertId();
        
        $pdo->commit(); 
        return $vendaId;
    } catch (PDOException $e) {
        $pdo->rollBack(); 
        error_log("Erro PDO ao criar venda: " . $e->getMessage());
        return false;
    }
}

/**
 * Adiciona um item a uma venda existente e atualiza o estoque do produto.
 *
 * @param int $vendaId O ID da venda à qual o item será adicionado.
 * @param int $produtoId O ID do produto.
 * @param int $quantidade A quantidade do produto vendida.
 * @param float $precoUnitario O preço unitário do produto no momento da venda.
 * @return bool True se o item foi adicionado e o estoque atualizado, false caso contrário.
 */
function adicionarItemVenda( $vendaId, $produtoId, $quantidade, $precoUnitario) {
    global $pdo;
    try {
        $pdo->beginTransaction(); 

        $subtotal = $quantidade * $precoUnitario;
        $sqlItem = "INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario, subtotal)
                    VALUES (:venda_id, :produto_id, :quantidade, :preco_unitario, :subtotal)";
        $stmtItem = $pdo->prepare($sqlItem);
        $stmtItem->execute(array(
            ':venda_id' => $vendaId,
            ':produto_id' => $produtoId,
            ':quantidade' => $quantidade,
            ':preco_unitario' => $precoUnitario,
            ':subtotal' => $subtotal
        ));

        $sqlEstoque = "UPDATE produtos SET quantidade_estoque = quantidade_estoque - :quantidade WHERE id = :produto_id";
        $stmtEstoque = $pdo->prepare($sqlEstoque);
        $stmtEstoque->execute(array(
            ':quantidade' => $quantidade,
            ':produto_id' => $produtoId
        ));

        if ($stmtEstoque->rowCount() === 0) {
            throw new Exception("Produto não encontrado ou estoque não atualizado para o produto ID: " . $produtoId);
        }

        $pdo->commit(); 
        return true;
    } catch (Exception $e) {
        $pdo->rollBack(); 
        error_log("Erro ao adicionar item de venda ou atualizar estoque: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca uma venda pelo ID, incluindo seus itens e detalhes do cliente/produtos.
 *
 * @param int $vendaId ID da venda.
 * @return array|false Retorna um array associativo com os dados da venda e seus itens, ou false se não encontrada.
 */
function buscarVendaPorId( $vendaId) { 
    global $pdo;
    try {
        $sqlVenda = "SELECT v.*, c.nome AS cliente_nome, c.cpf_cnpj AS cliente_cpf_cnpj
                     FROM vendas v
                     JOIN clientes c ON v.cliente_id = c.id
                     WHERE v.id = :venda_id";
        $stmtVenda = $pdo->prepare($sqlVenda);
        $stmtVenda->execute(array(':venda_id' => $vendaId));
        $venda = $stmtVenda->fetch(PDO::FETCH_ASSOC);

        if (!$venda) {
            error_log("DEBUG: Venda ID " . $vendaId . " não encontrada em buscarVendaPorId.");
            return false;
        }

        error_log("DEBUG: Venda ID " . $vendaId . " encontrada. Buscando itens...");

        $sqlItens = "SELECT iv.*, p.nome AS produto_nome, p.foto_url AS produto_foto_url
                     FROM itens_venda iv
                     JOIN produtos p ON iv.produto_id = p.id
                     WHERE iv.venda_id = :venda_id";
        $stmtItens = $pdo->prepare($sqlItens);
        
        error_log("DEBUG: SQL para buscar itens: " . $sqlItens);
        error_log("DEBUG: Parâmetros para itens: [':venda_id' => " . $vendaId . "]");

        $stmtItens->execute(array(':venda_id' => $vendaId));
        $venda['itens'] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

        error_log("DEBUG: Itens encontrados para a venda ID " . $vendaId . ": " . print_r($venda['itens'], true));

        return $venda;
    } catch (PDOException $e) {
        error_log("Erro PDO em buscarVendaPorId: " . $e->getMessage());
        return false;
    }
}


/**
 * Busca todas as vendas, com opções de filtro por cliente e ordenação.
 *
 * @param int $clienteId Opcional. ID do cliente para filtrar as vendas.
 * @param string $ordenarPor Coluna para ordenar ('data_venda', 'valor_total').
 * @param string $ordem Ordem ('ASC' ou 'DESC').
 * @return array Retorna um array de arrays associativos com os dados das vendas.
 */
function buscarTodasVendas(
    $clienteId = 0,
    $ordenarPor = 'data_venda',
    $ordem = 'DESC'
) {
    global $pdo;
    $sql = "SELECT v.*, c.nome AS cliente_nome
            FROM vendas v
            JOIN clientes c ON v.cliente_id = c.id
            WHERE 1=1";
    $params = array();

    if ($clienteId > 0) {
        $sql .= " AND v.cliente_id = :cliente_id";
        $params[':cliente_id'] = $clienteId;
    }

    $colunasPermitidas = array('data_venda', 'valor_total');
    if (in_array($ordenarPor, $colunasPermitidas)) {
        $sql .= " ORDER BY v." . $ordenarPor;
        $sql .= ($ordem === 'DESC') ? " DESC" : " ASC";
    } else {
        $sql .= " ORDER BY v.data_venda DESC"; 
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar todas as vendas: " . $e->getMessage());
        return array();
    }
}

/**
 * Deleta uma venda e seus itens associados.
 *
 * @param int $vendaId ID da venda a ser deletada.
 * @return bool True se a deleção foi bem-sucedida, false caso contrário.
 */
function deletarVenda( $vendaId) {
    global $pdo;
    try {
        $pdo->beginTransaction(); 

        $itensVenda = buscarVendaPorId($vendaId);
        if ($itensVenda && isset($itensVenda['itens'])) {
            foreach ($itensVenda['itens'] as $item) {
                $sqlRetornarEstoque = "UPDATE produtos SET quantidade_estoque = quantidade_estoque + :quantidade WHERE id = :produto_id";
                $stmtRetornarEstoque = $pdo->prepare($sqlRetornarEstoque);
                $stmtRetornarEstoque->execute(array(
                    ':quantidade' => $item['quantidade'],
                    ':produto_id' => $item['produto_id']
                ));
            }
        }

        $sql = "DELETE FROM vendas WHERE id = :venda_id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute(array(':venda_id' => $vendaId));

        $pdo->commit(); 
        return $result;
    } catch (PDOException $e) {
        $pdo->rollBack(); 
        error_log("Erro PDO ao deletar venda: " . $e->getMessage());
        return false;
    }
}


/**
 * Retorna o relatório de vendas por mês e por produto.
 *
 * @return array Array de arrays associativos com 'mes_ano', 'produto_nome', 'total_vendido_quantidade', 'total_vendido_valor'.
 */
function getRelatorioVendasPorMesEProduto() {
    global $pdo;
    try {
        $sql = "SELECT
                    DATE_FORMAT(v.data_venda, '%Y-%m') AS mes_ano,
                    p.nome AS produto_nome,
                    SUM(iv.quantidade) AS total_vendido_quantidade,
                    SUM(iv.subtotal) AS total_vendido_valor
                FROM vendas v
                JOIN itens_venda iv ON v.id = iv.venda_id
                JOIN produtos p ON iv.produto_id = p.id
                GROUP BY mes_ano, p.nome
                ORDER BY mes_ano DESC, total_vendido_valor DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao obter relatório de vendas por mês e produto: " . $e->getMessage());
        return array();
    }
}

/**
 * Retorna os clientes que mais compram (com base no valor total das vendas).
 *
 * @param int $limite Opcional. Número máximo de clientes a retornar.
 * @return array Array de arrays associativos com 'cliente_nome', 'total_comprado'.
 */
function getClientesQueMaisCompram(/*int*/ $limite = 10) { // Removido type hint
    global $pdo;
    try {
        $sql = "SELECT
                    c.nome AS cliente_nome,
                    SUM(v.valor_total) AS total_comprado
                FROM vendas v
                JOIN clientes c ON v.cliente_id = c.id
                GROUP BY c.id, c.nome
                ORDER BY total_comprado DESC
                LIMIT :limite";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT); // Usar bindParam para LIMIT
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao obter clientes que mais compram: " . $e->getMessage());
        return array();
    }
}

/**
 * Retorna os produtos mais vendidos (com base na quantidade total vendida).
 *
 * @param int $limite Opcional. Número máximo de produtos a retornar.
 * @return array Array de arrays associativos com 'produto_nome', 'total_vendido'.
 */
function getProdutosMaisVendidos(/*int*/ $limite = 10) { 
    global $pdo;
    try {
        $sql = "SELECT
                    p.nome AS produto_nome,
                    SUM(iv.quantidade) AS total_vendido
                FROM itens_venda iv
                JOIN produtos p ON iv.produto_id = p.id
                GROUP BY p.id, p.nome
                ORDER BY total_vendido DESC
                LIMIT :limite";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao obter produtos mais vendidos: " . $e->getMessage());
        return array();
    }
}
?>