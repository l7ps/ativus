<?php
/**
 * Sistema Ativus - Módulo de Manutenções
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Manutenções';
$basePath = '../../';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $id = intval($_POST['id']);
                if ($id > 0) {
                    try {
                        executeStatement("DELETE FROM manutencoes WHERE id = ?", [$id]);
                        $success = "Manutenção excluída com sucesso!";
                    } catch (Exception $e) {
                        $error = "Erro ao excluir manutenção: " . $e->getMessage();
                    }
                }
                break;
                
            case 'export':
                try {
                    $sql = "SELECT m.*, e.codigo_interno, e.marca_modelo, e.tipo 
                            FROM manutencoes m 
                            INNER JOIN equipamentos e ON m.equipamento_id = e.id 
                            ORDER BY m.data_manutencao DESC";
                    $manutencoes = executeQuery($sql);
                    
                    $headers = ['ID', 'Equipamento', 'Código', 'Tipo Equipamento', 'Data', 'Tipo Manutenção', 'Custo', 'Observações'];
                    
                    $data = [];
                    foreach ($manutencoes as $manutencao) {
                        $data[] = [
                            $manutencao['id'],
                            $manutencao['marca_modelo'],
                            $manutencao['codigo_interno'],
                            ucfirst($manutencao['tipo']),
                            formatDateBR($manutencao['data_manutencao']),
                            ucfirst($manutencao['tipo']),
                            formatMoney($manutencao['custo']),
                            $manutencao['observacoes']
                        ];
                    }
                    
                    exportToCSV($data, 'manutencoes_' . date('Y-m-d') . '.csv', $headers);
                } catch (Exception $e) {
                    $error = "Erro ao exportar dados: " . $e->getMessage();
                }
                break;
        }
    }
}

// Parâmetros de busca e paginação
$search = $_GET['search'] ?? '';
$equipamento_id = intval($_GET['equipamento_id'] ?? 0);
$tipo = $_GET['tipo'] ?? '';
$mes = $_GET['mes'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$recordsPerPage = 10;

// Construir query de busca
$whereConditions = [];
$params = [];

if ($search) {
    $whereConditions[] = "(e.codigo_interno LIKE ? OR e.marca_modelo LIKE ? OR m.observacoes LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($equipamento_id > 0) {
    $whereConditions[] = "m.equipamento_id = ?";
    $params[] = $equipamento_id;
}

if ($tipo) {
    $whereConditions[] = "m.tipo = ?";
    $params[] = $tipo;
}

if ($mes) {
    $whereConditions[] = "strftime('%Y-%m', m.data_manutencao) = ?";
    $params[] = $mes;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Contar total de registros
$countSql = "SELECT COUNT(*) as total FROM manutencoes m INNER JOIN equipamentos e ON m.equipamento_id = e.id $whereClause";
$totalRecords = executeQuery($countSql, $params)[0]['total'];

// Calcular paginação
$pagination = paginate($totalRecords, $recordsPerPage, $page);

// Buscar manutenções
$sql = "SELECT m.*, e.codigo_interno, e.marca_modelo, e.tipo as tipo_equipamento, e.responsavel 
        FROM manutencoes m 
        INNER JOIN equipamentos e ON m.equipamento_id = e.id 
        LEFT JOIN fornecedores f ON m.fornecedor_id = f.id 
        $whereClause 
        ORDER BY m.data_manutencao DESC 
        LIMIT {$recordsPerPage} OFFSET {$pagination["offset"]}";
$manutencoes = executeQuery($sql, $params);

// Calcular total dos custos filtrados
$totalSql = "SELECT COALESCE(SUM(m.custo), 0) as total FROM manutencoes m INNER JOIN equipamentos e ON m.equipamento_id = e.id $whereClause";
$totalCusto = executeQuery($totalSql, $params)[0]['total'];

// Buscar equipamentos para filtro
$equipamentos = executeQuery("SELECT id, codigo_interno, marca_modelo FROM equipamentos ORDER BY codigo_interno");

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
        <i class="bi bi-tools me-2"></i>
        Manutenções
    </h2>
    <a href="form.php<?php echo $equipamento_id ? '?equipamento_id=' . $equipamento_id : ''; ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Nova Manutenção
    </a>
</div>

<!-- Estatísticas rápidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-wrench display-6"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo countRecords('manutencoes', 'tipo = ?', ['preventiva']); ?></h4>
                        <p class="mb-0">Preventivas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-exclamation-triangle display-6"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo countRecords('manutencoes', 'tipo = ?', ['corretiva']); ?></h4>
                        <p class="mb-0">Corretivas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-cash-coin display-6"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo formatMoney($totalCusto); ?></h4>
                        <p class="mb-0">Custo Total</p>
                        <?php if ($mes): ?>
                            <small>Mês: <?php echo date('m/Y', strtotime($mes . '-01')); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-calendar-month display-6"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo countRecords('manutencoes', 'strftime("%Y-%m", data_manutencao) = ?', [date('Y-m')]); ?></h4>
                        <p class="mb-0">Este Mês</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros e busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo sanitize($search); ?>" 
                       placeholder="Código, equipamento, observações...">
            </div>
            
            <div class="col-md-3">
                <label for="equipamento_id" class="form-label">Equipamento</label>
                <select class="form-select" id="equipamento_id" name="equipamento_id">
                    <option value="">Todos os equipamentos</option>
                    <?php foreach ($equipamentos as $equipamento): ?>
                        <option value="<?php echo $equipamento['id']; ?>" 
                                <?php echo $equipamento_id == $equipamento['id'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($equipamento['codigo_interno'] . ' - ' . $equipamento['marca_modelo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="preventiva" <?php echo $tipo === 'preventiva' ? 'selected' : ''; ?>>Preventiva</option>
                    <option value="corretiva" <?php echo $tipo === 'corretiva' ? 'selected' : ''; ?>>Corretiva</option>
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

<!-- Tabela de manutenções -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>
            Lista de Manutenções
            <span class="badge bg-primary ms-2"><?php echo $totalRecords; ?> registros</span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($manutencoes)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Nenhuma manutenção encontrada</h4>
                <p class="text-muted">
                    <?php if ($search || $equipamento_id || $tipo || $mes): ?>
                        Tente ajustar os filtros de busca.
                    <?php else: ?>
                        Comece registrando uma nova manutenção.
                    <?php endif; ?>
                </p>
                <a href="form.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Registrar Primeira Manutenção
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="manutencoesTable">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Equipamento</th>
                            <th>Responsável</th>
                            <th>Tipo</th>
                            <th>Custo</th>
                            <th>Observações</th>
                            <th>Fornecedor</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($manutencoes as $manutencao): ?>
                            <tr>
                                <td>
                                    <strong><?php echo formatDateBR($manutencao['data_manutencao']); ?></strong>
                                    <br><small class="text-muted"><?php echo date('d/m/Y', strtotime($manutencao['created_at'])); ?></small>
                                </td>
                                <td>
                                    <a href="../equipamentos/form.php?id=<?php echo $manutencao['equipamento_id']; ?>" 
                                       class="text-decoration-none">
                                        <strong><?php echo sanitize($manutencao['codigo_interno']); ?></strong>
                                    </a>
                                    <br>
                                    <small class="text-muted"><?php echo sanitize($manutencao['marca_modelo']); ?></small>
                                    <br>
                                    <span class="badge bg-secondary small"><?php echo ucfirst($manutencao['tipo_equipamento']); ?></span>
                                </td>
                                <td><?php echo sanitize($manutencao['responsavel']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $manutencao['tipo'] == 'preventiva' ? 'info' : 'warning'; ?>">
                                        <?php echo ucfirst($manutencao['tipo']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($manutencao['custo'] > 0): ?>
                                        <strong class="text-danger"><?php echo formatMoney($manutencao['custo']); ?></strong>
                                    <?php else: ?>
                                        <span class="text-success">Gratuita</span>
                                    <?php endif; ?>
                                </td>
                                    <?php if ($manutencao["observacoes"]):
                                        ?>
                                        <span data-bs-toggle="tooltip" 
                                              title="<?php echo sanitize($manutencao["observacoes"]); ?>">
                                            <?php echo sanitize(substr($manutencao["observacoes"], 0, 40)) . (strlen($manutencao["observacoes"]) > 40 ? "..." : ""); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($manutencao["fornecedor_nome"])): ?>
                                        <?php echo htmlspecialchars($manutencao["fornecedor_nome"]); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="form.php?id=<?php echo $manutencao['id']; ?>" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirmDelete('Tem certeza que deseja excluir esta manutenção?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $manutencao['id']; ?>">
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
                        'equipamento_id' => $equipamento_id,
                        'tipo' => $tipo,
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

