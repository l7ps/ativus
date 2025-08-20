<?php
/**
 * Sistema Ativus - Módulo de Pagamentos
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Pagamentos';
$basePath = '../../';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $id = intval($_POST['id']);
                if ($id > 0) {
                    try {
                        // Verificar se existe comprovante para deletar
                        $pagamento = executeQuery("SELECT comprovante FROM pagamentos WHERE id = ?", [$id]);
                        if (!empty($pagamento) && $pagamento[0]['comprovante']) {
                            deleteFile($pagamento[0]['comprovante'], '../../uploads/');
                        }
                        
                        executeStatement("DELETE FROM pagamentos WHERE id = ?", [$id]);
                        $success = "Pagamento excluído com sucesso!";
                    } catch (Exception $e) {
                        $error = "Erro ao excluir pagamento: " . $e->getMessage();
                    }
                }
                break;
                
            case 'export':
                try {
                    $sql = "SELECT p.*, a.nome_servico 
                            FROM pagamentos p 
                            LEFT JOIN assinaturas a ON p.assinatura_id = a.id 
                            ORDER BY p.data_pagamento DESC";
                    $pagamentos = executeQuery($sql);
                    
                    $headers = ['ID', 'Serviço', 'Data', 'Valor', 'Forma de Pagamento', 'Observações'];
                    
                    $data = [];
                    foreach ($pagamentos as $pagamento) {
                        $data[] = [
                            $pagamento['id'],
                            $pagamento['nome_servico'] ?? 'Serviço removido',
                            formatDateBR($pagamento['data_pagamento']),
                            formatMoney($pagamento['valor']),
                            ucfirst($pagamento['forma_pagamento']),
                            $pagamento['observacoes']
                        ];
                    }
                    
                    exportToCSV($data, 'pagamentos_' . date('Y-m-d') . '.csv', $headers);
                } catch (Exception $e) {
                    $error = "Erro ao exportar dados: " . $e->getMessage();
                }
                break;
        }
    }
}

// Parâmetros de busca e paginação
$search = $_GET['search'] ?? '';
$assinatura_id = intval($_GET['assinatura_id'] ?? 0);
$forma_pagamento = $_GET['forma_pagamento'] ?? '';
$mes = $_GET['mes'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$recordsPerPage = 10;

// Construir query de busca
$whereConditions = [];
$params = [];

if ($search) {
    $whereConditions[] = "(a.nome_servico LIKE ? OR p.observacoes LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($assinatura_id > 0) {
    $whereConditions[] = "p.assinatura_id = ?";
    $params[] = $assinatura_id;
}

if ($forma_pagamento) {
    $whereConditions[] = "p.forma_pagamento = ?";
    $params[] = $forma_pagamento;
}

if ($mes) {
    $whereConditions[] = "strftime('%Y-%m', p.data_pagamento) = ?";
    $params[] = $mes;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Contar total de registros
$countSql = "SELECT COUNT(*) as total FROM pagamentos p LEFT JOIN assinaturas a ON p.assinatura_id = a.id $whereClause";
$totalRecords = executeQuery($countSql, $params)[0]['total'];

// Calcular paginação
$pagination = paginate($totalRecords, $recordsPerPage, $page);

// Buscar pagamentos
$sql = "SELECT p.*, a.nome_servico 
        FROM pagamentos p 
        LEFT JOIN assinaturas a ON p.assinatura_id = a.id 
        $whereClause 
        ORDER BY p.data_pagamento DESC 
        LIMIT {$recordsPerPage} OFFSET {$pagination['offset']}";
$pagamentos = executeQuery($sql, $params);

// Calcular total dos pagamentos filtrados
$totalSql = "SELECT COALESCE(SUM(p.valor), 0) as total FROM pagamentos p LEFT JOIN assinaturas a ON p.assinatura_id = a.id $whereClause";
$totalValor = executeQuery($totalSql, $params)[0]['total'];

// Buscar assinaturas para filtro
$assinaturas = executeQuery("SELECT id, nome_servico FROM assinaturas ORDER BY nome_servico");

include '../../templates/header.php';
?>

<?php if (isset($success)): ?>
    <?php echo showAlert($success, 'success'); ?>
<?php endif; ?>

<?php if (isset($error)): ?>
    <?php echo showAlert($error, 'danger'); ?>
<?php endif; ?>

<!-- Cabeçalho da página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-cash-coin me-2"></i>
        Pagamentos e Recargas
    </h2>
    <a href="form.php<?php echo $assinatura_id ? '?assinatura_id=' . $assinatura_id : ''; ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Novo Pagamento
    </a>
</div>

<!-- Resumo financeiro -->
<?php if ($totalValor > 0): ?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-calculator display-6"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo formatMoney($totalValor); ?></h4>
                        <p class="mb-0">Total dos Pagamentos</p>
                        <?php if ($mes): ?>
                            <small>Mês: <?php echo date('m/Y', strtotime($mes . '-01')); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filtros e busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo sanitize($search); ?>" 
                       placeholder="Serviço ou observações">
            </div>
            
            <div class="col-md-3">
                <label for="assinatura_id" class="form-label">Serviço</label>
                <select class="form-select" id="assinatura_id" name="assinatura_id">
                    <option value="">Todos os serviços</option>
                    <?php foreach ($assinaturas as $assinatura): ?>
                        <option value="<?php echo $assinatura['id']; ?>" 
                                <?php echo $assinatura_id == $assinatura['id'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($assinatura['nome_servico']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="forma_pagamento" class="form-label">Forma</label>
                <select class="form-select" id="forma_pagamento" name="forma_pagamento">
                    <option value="">Todas</option>
                    <option value="cartao" <?php echo $forma_pagamento === 'cartao' ? 'selected' : ''; ?>>Cartão</option>
                    <option value="boleto" <?php echo $forma_pagamento === 'boleto' ? 'selected' : ''; ?>>Boleto</option>
                    <option value="pix" <?php echo $forma_pagamento === 'pix' ? 'selected' : ''; ?>>PIX</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="mes" class="form-label">Mês</label>
                <input type="month" class="form-control" id="mes" name="mes" value="<?php echo $mes; ?>">
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <div class="btn-group w-100" role="group">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>
        </form>
        
        <div class="row mt-3">
            <div class="col-12">
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="export">
                    <button type="submit" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-download me-1"></i>
                        Exportar CSV
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de pagamentos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>
            Lista de Pagamentos
            <span class="badge bg-primary ms-2"><?php echo $totalRecords; ?> registros</span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($pagamentos)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Nenhum pagamento encontrado</h4>
                <p class="text-muted">
                    <?php if ($search || $assinatura_id || $forma_pagamento || $mes): ?>
                        Tente ajustar os filtros de busca.
                    <?php else: ?>
                        Comece registrando um novo pagamento.
                    <?php endif; ?>
                </p>
                <a href="form.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Registrar Primeiro Pagamento
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="pagamentosTable">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Serviço</th>
                            <th>Valor</th>
                            <th>Forma</th>
                            <th>Comprovante</th>
                            <th>Observações</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagamentos as $pagamento): ?>
                            <tr>
                                <td>
                                    <strong><?php echo formatDateBR($pagamento['data_pagamento']); ?></strong>
                                    <br><small class="text-muted"><?php echo date('d/m/Y', strtotime($pagamento['created_at'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($pagamento['nome_servico']): ?>
                                        <a href="../assinaturas/form.php?id=<?php echo $pagamento['assinatura_id']; ?>" 
                                           class="text-decoration-none">
                                            <?php echo sanitize($pagamento['nome_servico']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Serviço removido</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-success"><?php echo formatMoney($pagamento['valor']); ?></strong>
                                </td>
                                <td>
                                    <?php
                                    $formaBadge = [
                                        'cartao' => 'primary',
                                        'boleto' => 'warning',
                                        'pix' => 'success'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $formaBadge[$pagamento['forma_pagamento']] ?? 'secondary'; ?>">
                                        <?php echo ucfirst($pagamento['forma_pagamento']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($pagamento['comprovante']): ?>
                                        <a href="../../uploads/<?php echo $pagamento['comprovante']; ?>" 
                                           target="_blank" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($pagamento['observacoes']): ?>
                                        <span data-bs-toggle="tooltip" 
                                              title="<?php echo sanitize($pagamento['observacoes']); ?>">
                                            <?php echo sanitize(substr($pagamento['observacoes'], 0, 30)) . (strlen($pagamento['observacoes']) > 30 ? '...' : ''); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="form.php?id=<?php echo $pagamento['id']; ?>" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirmDelete('Tem certeza que deseja excluir este pagamento?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $pagamento['id']; ?>">
                                            <button type="submit" 
                                                    class="btn btn-outline-danger" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="mt-4">
                    <?php 
                    $queryParams = array_filter([
                        'search' => $search,
                        'assinatura_id' => $assinatura_id,
                        'forma_pagamento' => $forma_pagamento,
                        'mes' => $mes
                    ]);
                    $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) . '&' : '?';
                    echo generatePaginationLinks($pagination, 'index.php' . $queryString);
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$pageScripts = "
<script>
// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle=\"tooltip\"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
";

include '../../templates/footer.php';
?>

