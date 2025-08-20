<?php
/**
 * Sistema Ativus - Funções Auxiliares
 */

// Função para formatar data brasileira
function formatDateBR($date) {
    if (!$date) return '';
    return date('d/m/Y', strtotime($date));
}

// Função para formatar data para banco (Y-m-d)
function formatDateDB($date) {
    if (!$date) return null;
    // Se já está no formato correto
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }
    // Se está no formato brasileiro (dd/mm/yyyy)
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
        return date('Y-m-d', strtotime(str_replace('/', '-', $date)));
    }
    return null;
}

// Função para formatar valor monetário
function formatMoney($value) {
    if (!$value) return 'R$ 0,00';
    return 'R$ ' . number_format($value, 2, ',', '.');
}

// Função para converter valor monetário para float
function parseMoney($value) {
    if (!$value) return 0;
    // Remove R$, espaços e pontos, substitui vírgula por ponto
    $value = str_replace(['R$', ' ', '.'], '', $value);
    $value = str_replace(',', '.', $value);
    return floatval($value);
}

// Função para sanitizar entrada
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Função para validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função para gerar código interno único
function generateCode($prefix = '', $length = 6) {
    $code = $prefix . str_pad(rand(1, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    return $code;
}

// Função para calcular dias até vencimento
function daysUntilExpiry($date) {
    if (!$date) return null;
    $today = new DateTime();
    $expiry = new DateTime($date);
    $diff = $today->diff($expiry);
    
    if ($expiry < $today) {
        return -$diff->days; // Negativo se já venceu
    }
    return $diff->days;
}

// Função para obter status de vencimento
function getExpiryStatus($date) {
    $days = daysUntilExpiry($date);
    
    if ($days === null) return ['status' => 'sem-data', 'class' => 'secondary', 'text' => 'Sem data'];
    if ($days < 0) return ['status' => 'vencido', 'class' => 'danger', 'text' => 'Vencido há ' . abs($days) . ' dias'];
    if ($days == 0) return ['status' => 'vence-hoje', 'class' => 'warning', 'text' => 'Vence hoje'];
    if ($days <= 7) return ['status' => 'vence-semana', 'class' => 'warning', 'text' => 'Vence em ' . $days . ' dias'];
    if ($days <= 30) return ['status' => 'vence-mes', 'class' => 'info', 'text' => 'Vence em ' . $days . ' dias'];
    
    return ['status' => 'ok', 'class' => 'success', 'text' => 'Vence em ' . $days . ' dias'];
}

// Função para upload de arquivo
function uploadFile($file, $uploadDir = 'uploads/') {
    if (!isset($file['tmp_name']) || !$file['tmp_name']) {
        return null;
    }
    
    // Criar diretório se não existir
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    
    return false;
}

// Função para deletar arquivo
function deleteFile($fileName, $uploadDir = 'uploads/') {
    if (!$fileName) return true;
    
    $filePath = $uploadDir . $fileName;
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    
    return true;
}

// Função para exibir alertas Bootstrap
function showAlert($message, $type = 'info') {
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Função para paginar resultados
function paginate($totalRecords, $recordsPerPage = 10, $currentPage = 1) {
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $recordsPerPage;
    
    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'records_per_page' => $recordsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

// Função para gerar links de paginação
function generatePaginationLinks($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) return '';
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Botão Anterior
    if ($pagination['has_previous']) {
        $prevPage = $pagination['current_page'] - 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$baseUrl}?page={$prevPage}'>Anterior</a></li>";
    } else {
        $html .= "<li class='page-item disabled'><span class='page-link'>Anterior</span></li>";
    }
    
    // Números das páginas
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $pagination['current_page']) ? 'active' : '';
        $html .= "<li class='page-item {$active}'><a class='page-link' href='{$baseUrl}?page={$i}'>{$i}</a></li>";
    }
    
    // Botão Próximo
    if ($pagination['has_next']) {
        $nextPage = $pagination['current_page'] + 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$baseUrl}?page={$nextPage}'>Próximo</a></li>";
    } else {
        $html .= "<li class='page-item disabled'><span class='page-link'>Próximo</span></li>";
    }
    
    $html .= '</ul></nav>';
    return $html;
}

// Função para exportar dados para CSV
function exportToCSV($data, $filename, $headers = []) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalhos
    if (!empty($headers)) {
        fputcsv($output, $headers, ';');
    } elseif (!empty($data)) {
        fputcsv($output, array_keys($data[0]), ';');
    }
    
    // Dados
    foreach ($data as $row) {
        fputcsv($output, $row, ';');
    }
    
    fclose($output);
    exit;
}
?>

