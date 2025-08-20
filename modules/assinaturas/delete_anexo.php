<?php
/**
 * Sistema Ativus - Deletar Anexos de Assinaturas
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$anexo_id = intval($_POST['anexo_id'] ?? 0);
if ($anexo_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do anexo inválido']);
    exit;
}

try {
    // Buscar dados do anexo
    $anexo = executeQuery("SELECT * FROM anexos_assinaturas WHERE id = ?", [$anexo_id]);
    if (empty($anexo)) {
        http_response_code(404);
        echo json_encode(['error' => 'Anexo não encontrado']);
        exit;
    }
    
    $anexo = $anexo[0];
    
    // Deletar arquivo físico
    $filepath = '../../uploads/assinaturas/' . $anexo['nome_arquivo'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    
    // Deletar do banco
    executeStatement("DELETE FROM anexos_assinaturas WHERE id = ?", [$anexo_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Anexo removido com sucesso'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno: ' . $e->getMessage()]);
}
?>

