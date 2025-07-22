<?php

require_once __DIR__ . '/database.php'; 

/**
 * Cria um novo cliente no banco de dados.
 * @param array $dadosCliente Array associativo com nome, cpf_cnpj, telefone, email, endereco.
 * @return int|false Retorna o ID do cliente inserido ou false em caso de erro.
 */
function criarCliente($dadosCliente) {
    global $pdo; 
    try {
        $sql = "INSERT INTO clientes (nome, cpf_cnpj, telefone, email, endereco) 
                VALUES (:nome, :cpf_cnpj, :telefone, :email, :endereco)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome'     => isset($dadosCliente['nome']) ? $dadosCliente['nome'] : null,
            ':cpf_cnpj' => isset($dadosCliente['cpf_cnpj']) ? $dadosCliente['cpf_cnpj'] : null,
            ':telefone' => isset($dadosCliente['telefone']) ? $dadosCliente['telefone'] : null,
            ':email'    => isset($dadosCliente['email']) ? $dadosCliente['email'] : null,
            ':endereco' => isset($dadosCliente['endereco']) ? $dadosCliente['endereco'] : null
        ]);
        return $pdo->lastInsertId(); 
    } catch (PDOException $e) {
        error_log("Erro PDO ao criar cliente: " . $e->getMessage());
        return false; 
    }
}

/**
 * Busca clientes no banco de dados com paginação e busca.
 * @param string $termoBusca Termo para buscar por nome ou documento.
 * @param int $pagina Número da página atual.
 * @param int $limite Clientes por página.
 * @return array Array associativo com 'clientes' e 'total_clientes'.
 */
function buscarClientes($termoBusca = '', $pagina = 1, $limite = 10) {
    global $pdo;
    $offset = ($pagina - 1) * $limite;
    $clientes = array(); 
    $totalClientes = 0;

    try {
        $sqlBase = "FROM clientes";
        $params = array();
        
        if (!empty($termoBusca)) {
            $sqlBase .= " WHERE nome LIKE :termo OR cpf_cnpj LIKE :termo";
            $params[':termo'] = '%' . $termoBusca . '%';
        }

        $stmtCount = $pdo->prepare("SELECT COUNT(id) " . $sqlBase);
        $stmtCount->execute($params);
        $totalClientes = $stmtCount->fetchColumn();

        $sqlClientes = "SELECT id, nome, cpf_cnpj, telefone, email, endereco, data_cadastro " . $sqlBase;
        $sqlClientes .= " ORDER BY nome ASC LIMIT :limite OFFSET :offset";
        
        $stmt = $pdo->prepare($sqlClientes);
        
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        if (!empty($termoBusca)) {
            $stmt->bindParam(':termo', $params[':termo'], PDO::PARAM_STR);
        }
        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array('clientes' => $clientes, 'total_clientes' => $totalClientes);

    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar clientes com paginação: " . $e->getMessage());
        return array('clientes' => array(), 'total_clientes' => 0);
    }
}

/**
 * Busca um cliente específico por ID.
 * @param int $id ID do cliente.
 * @return array|false Retorna um array associativo com os dados do cliente ou false se não encontrado.
 */
function buscarClientePorId($id) {
    global $pdo;
    try {
        $sql = "SELECT id, nome, cpf_cnpj, telefone, email, endereco FROM clientes WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar cliente por ID ($id): " . $e->getMessage());
        return false;
    }
}

/**
 * Busca um cliente específico por e-mail.
 * Usado para verificar duplicidade ou outros fins.
 * @param string $email E-mail do cliente.
 * @return array|false Retorna um array associativo com os dados do cliente ou false se não encontrado.
 */
function buscarClientePorEmail($email, $excluirId = 0) { 
    global $pdo;
    try {
        $sql = "SELECT id, nome, email FROM clientes WHERE email = :email";
        if ($excluirId > 0) { 
            $sql .= " AND id != :excluir_id";
        }
        $stmt = $pdo->prepare($sql);
        $params = array(':email' => $email);
        if ($excluirId > 0) {
            $params[':excluir_id'] = $excluirId;
        }
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar cliente por e-mail (com exclusão de ID): " . $e->getMessage());
        return false;
    }
}

/**
 * Busca um cliente específico por CPF/CNPJ, excluindo um ID opcional.
 * @param string $cpf_cnpj CPF ou CNPJ do cliente.
 * @param int $excluirId Opcional. ID do cliente a ser excluído da busca de duplicidade.
 * @return array|false Retorna um array associativo com os dados do cliente ou false se não encontrado.
 */
function buscarClientePorCpfCnpj($cpf_cnpj, $excluirId = 0) { 
    global $pdo;
    try {
        $sql = "SELECT id, nome, cpf_cnpj FROM clientes WHERE cpf_cnpj = :cpf_cnpj";
        if ($excluirId > 0) { 
            $sql .= " AND id != :excluir_id";
        }
        $stmt = $pdo->prepare($sql);
        $params = array(':cpf_cnpj' => $cpf_cnpj);
        if ($excluirId > 0) {
            $params[':excluir_id'] = $excluirId;
        }
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar cliente por CPF/CNPJ (com exclusão de ID): " . $e->getMessage());
        return false;
    }
}
/**
 * Atualiza os dados de um cliente.
 * @param int $id ID do cliente a ser atualizado.
 * @param array $dadosCliente Array associativo com os dados a serem atualizados.
 * @return bool Retorna true em caso de sucesso, false em caso de erro ou nenhuma alteração.
 */
function atualizarCliente($id, $dadosCliente) {
    global $pdo;
    try {
        $sql = "UPDATE clientes SET nome = :nome, cpf_cnpj = :cpf_cnpj, telefone = :telefone, email = :email, endereco = :endereco WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':nome'     => isset($dadosCliente['nome']) ? $dadosCliente['nome'] : null,
            ':cpf_cnpj' => isset($dadosCliente['cpf_cnpj']) ? $dadosCliente['cpf_cnpj'] : null,
            ':telefone' => isset($dadosCliente['telefone']) ? $dadosCliente['telefone'] : null,
            ':email'    => isset($dadosCliente['email']) ? $dadosCliente['email'] : null,
            ':endereco' => isset($dadosCliente['endereco']) ? $dadosCliente['endereco'] : null,
            ':id'       => $id
        ));
        return true; 
    } catch (PDOException $e) {
        error_log("Erro PDO ao atualizar cliente (ID: $id): " . $e->getMessage());
        return false;
    }
}

/**
 * Deleta um cliente do banco de dados.
 * @param int $id ID do cliente a ser deletado.
 * @return bool Retorna true em caso de sucesso, false em caso de erro.
 */
function deletarCliente($id) {
    global $pdo;
    try {
        $sql = "DELETE FROM clientes WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));
        return $stmt->rowCount() > 0; 
    } catch (PDOException $e) {
        error_log("Erro PDO ao deletar cliente (ID: $id): " . $e->getMessage());
        return false;
    }
}

?>