<?php
/**
 * Sistema Ativus - Buscar Anexos de Assinaturas
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$assinatura_id = intval($_GET['assinatura_id'] ?? 0);
if ($assinatura_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID da assinatura inválido']);
    exit;
}

try {
    // Buscar anexos da assinatura
    $anexos = executeQuery("SELECT * FROM anexos_assinaturas WHERE assinatura_id = ? ORDER BY created_at DESC", [$assinatura_id]);
    
    $anexosFormatados = [];
    foreach ($anexos as $anexo) {
        $anexosFormatados[] = [
            'id' => $anexo['id'],
            'nome_original' => $anexo['nome_original'],
            'nome_arquivo' => $anexo['nome_arquivo'],
            'tipo_arquivo' => $anexo['tipo_arquivo'],
            'tamanho' => formatBytes($anexo['tamanho']),
            'url' => '../../uploads/assinaturas/' . $anexo['nome_arquivo'],
            'created_at' => $anexo['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'anexos' => $anexosFormatados
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno: ' . $e->getMessage()]);
}
?>

