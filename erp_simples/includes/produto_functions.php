<?php
require_once __DIR__ . '/database.php';

/**
 * Cria um novo produto no banco de dados.
 *
 * @param array $dadosProduto Array associativo com os dados do produto.
 * Esperado: 'nome', 'descricao', 'preco', 'quantidade_estoque',
 * 'categoria_id' (opcional), 'foto_url' (opcional).
 * @return int|false O ID do produto inserido ou false em caso de erro.
 */
function criarProduto( $dadosProduto) { 
    global $pdo;
    try {
        $sql = "INSERT INTO produtos (nome, descricao, preco, quantidade_estoque, categoria_id, foto_url)
                VALUES (:nome, :descricao, :preco, :quantidade_estoque, :categoria_id, :foto_url)";
        $stmt = $pdo->prepare($sql);

        $params = array(
            ':nome'                 => isset($dadosProduto['nome']) ? $dadosProduto['nome'] : null,
            ':descricao'            => isset($dadosProduto['descricao']) ? $dadosProduto['descricao'] : null,
            ':preco'                => isset($dadosProduto['preco']) ? $dadosProduto['preco'] : 0, 
            ':quantidade_estoque'   => isset($dadosProduto['quantidade_estoque']) ? $dadosProduto['quantidade_estoque'] : 0, 
            ':categoria_id'         => isset($dadosProduto['categoria_id']) && $dadosProduto['categoria_id'] !== '' ? $dadosProduto['categoria_id'] : null,
            ':foto_url'             => isset($dadosProduto['foto_url']) ? $dadosProduto['foto_url'] : null
        );

        if ($stmt->execute($params)) {
            return $pdo->lastInsertId();
        }
        return false;
    } catch (PDOException $e) {
        error_log("Erro PDO ao criar produto: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca um produto pelo ID.
 *
 * @param int $id ID do produto.
 * @return array|false Retorna um array associativo com os dados do produto ou false se não encontrado.
 */
function buscarProdutoPorId( $id) {
    global $pdo;
    try {
        $sql = "SELECT p.*, c.nome AS categoria_nome
                FROM produtos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar produto por ID: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca todos os produtos (opcionalmente com filtros e ordenação).
 *
 * @param string $termoBusca Termo para buscar no nome ou descrição do produto.
 * @param int $categoriaId ID da categoria para filtrar.
 * @param string $ordenarPor Coluna para ordenar ('nome', 'preco', 'quantidade_estoque').
 * @param string $ordem Ordem ('ASC' ou 'DESC').
 * @return array Retorna um array de arrays associativos com os dados dos produtos.
 */
function buscarTodosProdutos(
    $termoBusca = '',
    $categoriaId = 0,
    $ordenarPor = 'nome',
    $ordem = 'ASC'
) {
    global $pdo;
    $sql = "SELECT p.*, c.nome AS categoria_nome
            FROM produtos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE 1=1";

    $params = array();

    if (!empty($termoBusca)) {
        $sql .= " AND (p.nome LIKE :termo_nome OR p.descricao LIKE :termo_descricao)";
        $params[':termo_nome'] = '%' . $termoBusca . '%';
        $params[':termo_descricao'] = '%' . $termoBusca . '%';
    }

    if ($categoriaId > 0) {
        $sql .= " AND p.categoria_id = :categoria_id";
        $params[':categoria_id'] = $categoriaId;
    }

    $colunasPermitidas = array('nome', 'preco', 'quantidade_estoque', 'data_cadastro');
    if (in_array($ordenarPor, $colunasPermitidas)) {
        $sql .= " ORDER BY p." . $ordenarPor;
        $sql .= ($ordem === 'DESC') ? " DESC" : " ASC";
    } else {
        $sql .= " ORDER BY p.nome ASC";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar todos os produtos: " . $e->getMessage());
        return array();
    }
}

/**
 * Atualiza os dados de um produto existente.
 *
 * @param int $id ID do produto a ser atualizado.
 * @param array $dadosProduto Array associativo com os novos dados do produto.
 * @return bool True se a atualização foi bem-sucedida, false caso contrário.
 */
function atualizarProduto( $id,  $dadosProduto) { 
    global $pdo;
    try {
        $sql = "UPDATE produtos SET
                    nome = :nome,
                    descricao = :descricao,
                    preco = :preco,
                    quantidade_estoque = :quantidade_estoque,
                    categoria_id = :categoria_id,
                    foto_url = :foto_url
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        $params = array(
            ':id'                   => $id,
            ':nome'                 => isset($dadosProduto['nome']) ? $dadosProduto['nome'] : null,
            ':descricao'            => isset($dadosProduto['descricao']) ? $dadosProduto['descricao'] : null,
            ':preco'                => isset($dadosProduto['preco']) ? $dadosProduto['preco'] : 0,
            ':quantidade_estoque'   => isset($dadosProduto['quantidade_estoque']) ? $dadosProduto['quantidade_estoque'] : 0,
            ':categoria_id'         => isset($dadosProduto['categoria_id']) && $dadosProduto['categoria_id'] !== '' ? $dadosProduto['categoria_id'] : null,
            ':foto_url'             => isset($dadosProduto['foto_url']) ? $dadosProduto['foto_url'] : null
        );

        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Erro PDO ao atualizar produto: " . $e->getMessage());
        return false;
    }
}

/**
 * Deleta um produto pelo ID.
 *
 * @param int $id ID do produto a ser deletado.
 * @return bool True se a deleção foi bem-sucedida, false caso contrário.
 */
function deletarProduto( $id) { 
    global $pdo;
    try {
        $sql = "DELETE FROM produtos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array(':id' => $id));
    } catch (PDOException $e) {
        error_log("Erro PDO ao deletar produto: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca todas as categorias disponíveis.
 * Usado para popular dropdowns em formulários de produto.
 *
 * @return array Retorna um array de arrays associativos com os dados das categorias.
 */
function buscarTodasCategorias() {
    global $pdo;
    try {
        $sql = "SELECT id, nome FROM categorias ORDER BY nome ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar todas as categorias: " . $e->getMessage());
        return array();
    }
}

/**
 * Busca um produto específico por nome (para validação de duplicidade).
 *
 * @param string $nome Nome do produto.
 * @param int $excluirId Opcional. ID do produto a ser excluído da busca.
 * @return array|false Retorna um array associativo com os dados do produto ou false se não encontrado.
 */
function buscarProdutoPorNome( $nome,  $excluirId = 0) { 
    global $pdo;
    try {
        $sql = "SELECT id, nome FROM produtos WHERE nome = :nome";
        if ($excluirId > 0) {
            $sql .= " AND id != :excluir_id";
        }
        $stmt = $pdo->prepare($sql);
        $params = array(':nome' => $nome);
        if ($excluirId > 0) {
            $params[':excluir_id'] = $excluirId;
        }
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar produto por nome (com exclusão de ID): " . $e->getMessage());
        return false;
    }
}

?>