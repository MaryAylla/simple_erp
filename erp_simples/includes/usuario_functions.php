<?php

require_once __DIR__ . '/database.php';

/**
 * Cria um novo usuário do sistema no banco de dados.
 *
 * @param array $dadosUsuario Array associativo com nome, email, senha_hash, perfil.
 * @return int|false Retorna o ID do usuário inserido ou false em caso de erro.
 */
function criarUsuarioSistema(array $dadosUsuario) {
    global $pdo;
    try {
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (:nome, :email, :senha_hash, :perfil, :ativo)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':nome'       => $dadosUsuario['nome'],
            ':email'      => $dadosUsuario['email'],
            ':senha_hash' => $dadosUsuario['senha_hash'],
            ':perfil'     => isset($dadosUsuario['perfil']) ? $dadosUsuario['perfil'] : 'vendedor',
            ':ativo'      => isset($dadosUsuario['ativo']) ? (bool)$dadosUsuario['ativo'] : true // true por padrão
        ));
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Erro PDO ao criar usuário do sistema: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca usuários do sistema com paginação e busca.
 * @param string $termoBusca Termo para buscar por nome ou e-mail.
 * @param int $pagina Número da página atual.
 * @param int $limite Clientes por página.
 * @return array Array associativo com 'usuarios' e 'total_usuarios'.
 */
function buscarUsuariosSistema($termoBusca = '', $pagina = 1, $limite = 10) {
    global $pdo;
    $offset = ($pagina - 1) * $limite;
    $usuarios = array();
    $totalUsuarios = 0;

    try {
        $sqlBase = "FROM usuarios";
        $params = array();
        
        if (!empty($termoBusca)) {
            $sqlBase .= " WHERE nome LIKE :termo OR email LIKE :termo";
            $params[':termo'] = '%' . $termoBusca . '%';
        }

        $stmtCount = $pdo->prepare("SELECT COUNT(id) " . $sqlBase);
        $stmtCount->execute($params);
        $totalUsuarios = $stmtCount->fetchColumn();

        $sqlUsuarios = "SELECT id, nome, email, perfil, ativo, data_cadastro " . $sqlBase;
        $sqlUsuarios .= " ORDER BY nome ASC LIMIT :limite OFFSET :offset";
        
        $stmt = $pdo->prepare($sqlUsuarios);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        if (!empty($termoBusca)) {
            $stmt->bindParam(':termo', $params[':termo'], PDO::PARAM_STR);
        }
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array('usuarios' => $usuarios, 'total_usuarios' => $totalUsuarios);

    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar usuários do sistema: " . $e->getMessage());
        return array('usuarios' => array(), 'total_usuarios' => 0);
    }
}

/**
 * Busca um usuário do sistema por ID.
 * @param int $id ID do usuário.
 * @return array|false Retorna um array associativo com os dados do usuário ou false se não encontrado.
 */
function buscarUsuarioSistemaPorId($id) {
    global $pdo;
    try {
        $sql = "SELECT id, nome, email, perfil, ativo FROM usuarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO ao buscar usuário do sistema por ID: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca um usuário do sistema por e-mail (para validação de duplicidade).
 * @param string $email E-mail do usuário.
 * @param int $excluirId Opcional. ID do usuário a ser excluído da busca.
 * @return array|false Retorna um array associativo com os dados do usuário ou false se não encontrado.
 */
function buscarUsuarioSistemaPorEmail($email, $excluirId = 0) {
    global $pdo;
    try {
        $sql = "SELECT id, nome, email FROM usuarios WHERE email = :email";
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
        error_log("Erro PDO ao buscar usuário do sistema por e-mail: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza os dados de um usuário do sistema.
 * @param int $id ID do usuário a ser atualizado.
 * @param array $dadosUsuario Array associativo com os dados a serem atualizados (nome, email, perfil, ativo, senha_hash).
 * @return bool Retorna true em caso de sucesso, false em caso de erro ou nenhuma alteração.
 */
function atualizarUsuarioSistema($id, array $dadosUsuario) {
    global $pdo;
    try {
        $sql = "UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, ativo = :ativo";
        
        if (isset($dadosUsuario['senha_hash']) && !empty($dadosUsuario['senha_hash'])) {
            $sql .= ", senha_hash = :senha_hash";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        
        $params = array(
            ':nome'   => isset($dadosUsuario['nome']) ? $dadosUsuario['nome'] : null,
            ':email'  => isset($dadosUsuario['email']) ? $dadosUsuario['email'] : null,
            ':perfil' => isset($dadosUsuario['perfil']) ? $dadosUsuario['perfil'] : 'vendedor',
            ':ativo'  => isset($dadosUsuario['ativo']) ? (bool)$dadosUsuario['ativo'] : true,
            ':id'     => $id
        );

        if (isset($dadosUsuario['senha_hash']) && !empty($dadosUsuario['senha_hash'])) {
            $params[':senha_hash'] = $dadosUsuario['senha_hash'];
        }

        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Erro PDO ao atualizar usuário do sistema (ID: $id): " . $e->getMessage());
        return false;
    }
}

/**
 * Deleta um usuário do sistema.
 * @param int $id ID do usuário a ser deletado.
 * @return bool Retorna true em caso de sucesso, false em caso de erro.
 */
function deletarUsuarioSistema($id) {
    global $pdo;
    try {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array(':id' => $id));
    } catch (PDOException $e) {
        error_log("Erro PDO ao deletar usuário do sistema (ID: $id): " . $e->getMessage());
        return false;
    }
}
?>