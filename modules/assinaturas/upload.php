<?php
/**
 * Sistema Ativus - Upload de Anexos para Assinaturas
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$assinatura_id = intval($_POST['assinatura_id'] ?? 0);
if ($assinatura_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID da assinatura inválido']);
    exit;
}

// Verificar se a assinatura existe
$assinatura = executeQuery("SELECT id FROM assinaturas WHERE id = ?", [$assinatura_id]);
if (empty($assinatura)) {
    http_response_code(404);
    echo json_encode(['error' => 'Assinatura não encontrada']);
    exit;
}

if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Erro no upload do arquivo']);
    exit;
}

$arquivo = $_FILES['arquivo'];

// Validar tipo de arquivo
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
if (!in_array($arquivo['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou PDF.']);
    exit;
}

// Validar tamanho (5MB máximo)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($arquivo['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'Arquivo muito grande. Máximo 5MB.']);
    exit;
}

try {
    // Criar diretório se não existir
    $uploadDir = '../../uploads/assinaturas/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Gerar nome único para o arquivo
    $extension = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $filename = 'assinatura_' . $assinatura_id . '_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Mover arquivo
    if (!move_uploaded_file($arquivo['tmp_name'], $filepath)) {
        throw new Exception('Erro ao salvar arquivo');
    }
    
    // Salvar no banco de dados
    $sql = "INSERT INTO anexos_assinaturas (assinatura_id, nome_original, nome_arquivo, tipo_arquivo, tamanho, created_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
    executeStatement($sql, [
        $assinatura_id,
        $arquivo['name'],
        $filename,
        $arquivo['type'],
        $arquivo['size']
    ]);
    
    $anexo_id = getLastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Arquivo enviado com sucesso',
        'anexo' => [
            'id' => $anexo_id,
            'nome_original' => $arquivo['name'],
            'nome_arquivo' => $filename,
            'tipo_arquivo' => $arquivo['type'],
            'tamanho' => formatBytes($arquivo['size']),
            'url' => '../../uploads/assinaturas/' . $filename
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno: ' . $e->getMessage()]);
}
?>

