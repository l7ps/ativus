<?php
/**
 * Sistema Ativus - Conexão com Banco de Dados SQLite
 */

// Configuração do banco de dados
$dbPath = __DIR__ . '/../db/database.sqlite';

// Função para obter conexão PDO
function getConnection() {
    global $dbPath;
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Habilitar foreign keys no SQLite
        $pdo->exec('PRAGMA foreign_keys = ON');
        
        return $pdo;
    } catch (PDOException $e) {
        die("Erro na conexão com o banco de dados: " . $e->getMessage());
    }
}

// Função para executar queries SELECT
function executeQuery($sql, $params = []) {
    $pdo = getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Função para executar queries INSERT/UPDATE/DELETE
function executeStatement($sql, $params = []) {
    $pdo = getConnection();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// Função para obter o último ID inserido
function getLastInsertId() {
    $pdo = getConnection();
    return $pdo->lastInsertId();
}

// Função para contar registros
function countRecords($table, $where = '', $params = []) {
    $sql = "SELECT COUNT(*) as total FROM $table";
    if ($where) {
        $sql .= " WHERE $where";
    }
    
    $result = executeQuery($sql, $params);
    return $result[0]['total'] ?? 0;
}

// Função para verificar se o banco existe e está configurado
function isDatabaseReady() {
    global $dbPath;
    
    if (!file_exists($dbPath)) {
        return false;
    }
    
    try {
        $pdo = getConnection();
        // Verificar se as tabelas principais existem
        $tables = ['assinaturas', 'pagamentos', 'equipamentos', 'manutencoes'];
        
        foreach ($tables as $table) {
            $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
            if (!$result->fetch()) {
                return false;
            }
        }
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Verificar se o banco está pronto, senão redirecionar para setup
if (!isDatabaseReady() && basename($_SERVER['PHP_SELF']) !== 'setup.php') {
    header('Location: setup.php');
    exit;
}
?>

