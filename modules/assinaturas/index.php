<?php
/**
 * Sistema Ativus - Módulo de Assinaturas
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Assinaturas';
$basePath = '../../';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $id = intval($_POST['id']);
                if ($id > 0) {
                    try {
                        executeStatement("DELETE FROM assinaturas WHERE id = ?", [$id]);
                        $success = "Assinatura excluída com sucesso!";
                    } catch (Exception $e) {
                        $error = "Erro ao excluir assinatura: " . $e->getMessage();
                    }
                }
                break;
                
            case 'export':
                try {
                    $assinaturas = executeQuery("SELECT * FROM assinaturas ORDER BY nome_servico");
                    $headers = ['ID', 'Nome do Serviço', 'Tipo', 'Responsável', 'Valor Padrão', 'Data Início', 'Data Vencimento', 'Status'];
                    
                    $data = [];
                    foreach ($assinaturas as $assinatura) {
                        $data[] = [
                            $assinatura['id'],
                            $assinatura['nome_servico'],
                            ucfirst($assinatura['tipo']),
                            $assinatura['responsavel'],
                            formatMoney($assinatura['valor_padrao']),
                            formatDateBR($assinatura['data_inicio']),
                            formatDateBR($assinatura['data_vencimento']),
                            ucfirst($assinatura['status'])
                        ];
                    }
                    
                    exportToCSV($data, 'assinaturas_' . date('Y-m-d') . '.csv', $headers);
                } catch (Exception $e) {
                    $error = "Erro ao exportar dados: " . $e->getMessage();
                }
                break;
        }
    }
}

// Parâmetros de busca e paginação
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$recordsPerPage = 10;

// Construir query de busca
$whereConditions = [];
$params = [];

if ($search) {
    $whereConditions[] = "(nome_servico LIKE ? OR responsavel LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $whereConditions[] = "status = ?";
    $params[] = $status;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Contar total de registros
$totalRecords = countRecords('assinaturas', implode(' AND ', $whereConditions), $params);

// Calcular paginação
$pagination = paginate($totalRecords, $recordsPerPage, $page);

// Buscar assinaturas
$sql = "SELECT * FROM assinaturas $whereClause ORDER BY nome_servico LIMIT {$recordsPerPage} OFFSET {$pagination['offset']}";
$assinaturas = executeQuery($sql, $params);

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
        <i class="bi bi-calendar-check me-2"></i>
        Assinaturas e Serviços
    </h2>
    <a href="form.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Nova Assinatura
    </a>
</div>

<!-- Filtros e busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo sanitize($search); ?>" 
                       placeholder="Nome do serviço ou responsável">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="ativo" <?php echo $status === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                    <option value="cancelado" <?php echo $status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-5 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search me-1"></i>
                    Buscar
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    Limpar
                </a>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="export">
                    <button type="submit" class="btn btn-outline-success">
                        <i class="bi bi-download me-1"></i>
                        Exportar CSV
                    </button>
                </form>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de assinaturas -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>
            Lista de Assinaturas
            <span class="badge bg-primary ms-2"><?php echo $totalRecords; ?> registros</span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($assinaturas)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Nenhuma assinatura encontrada</h4>
                <p class="text-muted">
                    <?php if ($search || $status): ?>
                        Tente ajustar os filtros de busca.
                    <?php else: ?>
                        Comece adicionando uma nova assinatura.
                    <?php endif; ?>
                </p>
                <a href="form.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Adicionar Primeira Assinatura
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="assinaturasTable">
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Tipo</th>
                            <th>Responsável</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assinaturas as $assinatura): ?>
                            <?php $statusVencimento = getExpiryStatus($assinatura['data_vencimento']); ?>
                            <tr>
                                <td>
                                    <strong><?php echo sanitize($assinatura['nome_servico']); ?></strong>
                                    <?php if ($assinatura['data_inicio']): ?>
                                        <br><small class="text-muted">Desde <?php echo formatDateBR($assinatura['data_inicio']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo ucfirst($assinatura['tipo']); ?>
                                    </span>
                                </td>
                                <td><?php echo sanitize($assinatura['responsavel']); ?></td>
                                <td><?php echo formatMoney($assinatura['valor_padrao']); ?></td>
                                <td>
                                    <?php if ($assinatura['data_vencimento']): ?>
                                        <?php echo formatDateBR($assinatura['data_vencimento']); ?>
                                        <br>
                                        <span class="badge bg-<?php echo $statusVencimento['class']; ?> small">
                                            <?php echo $statusVencimento['text']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Não definido</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $assinatura['status']; ?>">
                                        <?php echo ucfirst($assinatura['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="form.php?id=<?php echo $assinatura['id']; ?>" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="../pagamentos/index.php?assinatura_id=<?php echo $assinatura['id']; ?>" 
                                           class="btn btn-outline-success" 
                                           data-bs-toggle="tooltip" 
                                           title="Ver Pagamentos">
                                            <i class="bi bi-cash-coin"></i>
                                        </a>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirmDelete('Tem certeza que deseja excluir esta assinatura?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $assinatura['id']; ?>">
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
                    <?php echo generatePaginationLinks($pagination, 'index.php' . ($search || $status ? '?' . http_build_query(['search' => $search, 'status' => $status]) . '&' : '?')); ?>
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

