<?php
/**
 * Sistema Ativus - Módulo de Equipamentos
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Equipamentos';
$basePath = '../../';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $id = intval($_POST['id']);
                if ($id > 0) {
                    try {
                        executeStatement("DELETE FROM equipamentos WHERE id = ?", [$id]);
                        $success = "Equipamento excluído com sucesso!";
                    } catch (Exception $e) {
                        $error = "Erro ao excluir equipamento: " . $e->getMessage();
                    }
                }
                break;
                
            case 'export':
                try {
                    $equipamentos = executeQuery("SELECT * FROM equipamentos ORDER BY codigo_interno");
                    $headers = ['ID', 'Código', 'Tipo', 'Marca/Modelo', 'Nº Série', 'Responsável', 'Setor/Sala', 'Data Aquisição', 'Garantia Até', 'Valor Aquisição', 'Status'];
                    
                    $data = [];
                    foreach ($equipamentos as $equipamento) {
                        $data[] = [
                            $equipamento['id'],
                            $equipamento['codigo_interno'],
                            ucfirst($equipamento['tipo']),
                            $equipamento['marca_modelo'],
                            $equipamento['numero_serie'],
                            $equipamento['responsavel'],
                            $equipamento['setor_sala'],
                            formatDateBR($equipamento['data_aquisicao']),
                            formatDateBR($equipamento['garantia_ate']),
                            formatMoney($equipamento['valor_aquisicao']),
                            ucfirst($equipamento['status'])
                        ];
                    }
                    
                    exportToCSV($data, 'equipamentos_' . date('Y-m-d') . '.csv', $headers);
                } catch (Exception $e) {
                    $error = "Erro ao exportar dados: " . $e->getMessage();
                }
                break;
        }
    }
}

// Parâmetros de busca e paginação
$search = $_GET['search'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$status = $_GET['status'] ?? '';
$setor = $_GET['setor'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$recordsPerPage = 10;

// Construir query de busca
$whereConditions = [];
$params = [];

if ($search) {
    $whereConditions[] = "(codigo_interno LIKE ? OR marca_modelo LIKE ? OR responsavel LIKE ? OR numero_serie LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($tipo) {
    $whereConditions[] = "tipo = ?";
    $params[] = $tipo;
}

if ($status) {
    $whereConditions[] = "status = ?";
    $params[] = $status;
}

if ($setor) {
    $whereConditions[] = "setor_sala LIKE ?";
    $params[] = "%$setor%";
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Contar total de registros
$totalRecords = countRecords('equipamentos', implode(' AND ', $whereConditions), $params);

// Calcular paginação
$pagination = paginate($totalRecords, $recordsPerPage, $page);

// Buscar equipamentos
$sql = "SELECT e.*, f.nome AS fornecedor_nome FROM equipamentos e LEFT JOIN fornecedores f ON e.fornecedor_id = f.id $whereClause ORDER BY e.codigo_interno LIMIT {$recordsPerPage} OFFSET {$pagination["offset"]}";
$equipamentos = executeQuery($sql, $params);

// Buscar setores únicos para filtro
$setores = executeQuery("SELECT DISTINCT setor_sala FROM equipamentos WHERE setor_sala IS NOT NULL AND setor_sala != '' ORDER BY setor_sala");

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
        <i class="bi bi-laptop me-2"></i>
        Equipamentos
    </h2>
    <a href="form.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Novo Equipamento
    </a>
</div>

<!-- Estatísticas rápidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-laptop display-6"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo countRecords('equipamentos', 'status = ?', ['ativo']); ?></h4>
                        <p class="mb-0">Ativos</p>
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
                        <i class="bi bi-tools display-6"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo countRecords('equipamentos', 'status = ?', ['em_manutencao']); ?></h4>
                        <p class="mb-0">Em Manutenção</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-exclamation-triangle display-6"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo countRecords('equipamentos', 'garantia_ate < ? AND status = ?', [date('Y-m-d'), 'ativo']); ?></h4>
                        <p class="mb-0">Garantia Vencida</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-trash display-6"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo countRecords('equipamentos', 'status = ?', ['descartado']); ?></h4>
                        <p class="mb-0">Descartados</p>
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
                       placeholder="Código, marca, responsável...">
            </div>
            
            <div class="col-md-2">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="notebook" <?php echo $tipo === 'notebook' ? 'selected' : ''; ?>>Notebook</option>
                    <option value="desktop" <?php echo $tipo === 'desktop' ? 'selected' : ''; ?>>Desktop</option>
                    <option value="celular" <?php echo $tipo === 'celular' ? 'selected' : ''; ?>>Celular</option>
                    <option value="impressora" <?php echo $tipo === 'impressora' ? 'selected' : ''; ?>>Impressora</option>
                    <option value="tablet" <?php echo $tipo === 'tablet' ? 'selected' : ''; ?>>Tablet</option>
                    <option value="outro" <?php echo $tipo === 'outro' ? 'selected' : ''; ?>>Outro</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="ativo" <?php echo $status === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                    <option value="em_manutencao" <?php echo $status === 'em_manutencao' ? 'selected' : ''; ?>>Em Manutenção</option>
                    <option value="descartado" <?php echo $status === 'descartado' ? 'selected' : ''; ?>>Descartado</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="setor" class="form-label">Setor</label>
                <select class="form-select" id="setor" name="setor">
                    <option value="">Todos os setores</option>
                    <?php foreach ($setores as $setorItem): ?>
                        <option value="<?php echo sanitize($setorItem['setor_sala']); ?>" 
                                <?php echo $setor === $setorItem['setor_sala'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($setorItem['setor_sala']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i>
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
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

<!-- Tabela de equipamentos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>
            Lista de Equipamentos
            <span class="badge bg-primary ms-2"><?php echo $totalRecords; ?> registros</span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($equipamentos)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Nenhum equipamento encontrado</h4>
                <p class="text-muted">
                    <?php if ($search || $tipo || $status || $setor): ?>
                        Tente ajustar os filtros de busca.
                    <?php else: ?>
                        Comece cadastrando um novo equipamento.
                    <?php endif; ?>
                </p>
                <a href="form.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Cadastrar Primeiro Equipamento
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="equipamentosTable">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Equipamento</th>
                            <th>Responsável</th>
                            <th>Localização</th>
                            <th>Fornecedor</th>
                            <th>Toner</th>
                            <th>Datacard</th>
                            <th>Garantia</th>
                            <th>Status</th>
                            <th width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipamentos as $equipamento): ?>
                            <?php 
                            $garantiaStatus = null;
                            if ($equipamento['garantia_ate']) {
                                $garantiaStatus = getExpiryStatus($equipamento['garantia_ate']);
                            }
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo sanitize($equipamento['codigo_interno']); ?></strong>
                                    <br><small class="text-muted"><?php echo ucfirst($equipamento['tipo']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo sanitize($equipamento['marca_modelo']); ?></strong>
                                    <?php if ($equipamento['numero_serie']): ?>
                                        <br><small class="text-muted">S/N: <?php echo sanitize($equipamento['numero_serie']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo sanitize($equipamento['responsavel']); ?></td>
                                <td><?php echo sanitize($equipamento["setor_sala"]); ?></td>
                                <td><?php echo htmlspecialchars($equipamento["fornecedor_nome"]); ?></td>
                                <td>
                                    <?php if ($equipamento["tipo"] === "impressora"): ?>
                                        <?php echo sanitize($equipamento["toner_info"]); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($equipamento["tipo"] === "impressora"): ?>
                                        <?php echo $equipamento["is_datacard"] ? "Sim" : "Não"; ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($equipamento['garantia_ate']): ?>
                                        <?php echo formatDateBR($equipamento['garantia_ate']); ?>
                                        <?php if ($garantiaStatus): ?>
                                            <br>
                                            <span class="badge bg-<?php echo $garantiaStatus['class']; ?> small">
                                                <?php echo $garantiaStatus['text']; ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Não definida</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo str_replace('_', '-', $equipamento['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $equipamento['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="form.php?id=<?php echo $equipamento['id']; ?>" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="../manutencoes/index.php?equipamento_id=<?php echo $equipamento['id']; ?>" 
                                           class="btn btn-outline-warning" 
                                           data-bs-toggle="tooltip" 
                                           title="Ver Manutenções">
                                            <i class="bi bi-tools"></i>
                                        </a>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirmDelete('Tem certeza que deseja excluir este equipamento?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $equipamento['id']; ?>">
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
                        'tipo' => $tipo,
                        'status' => $status,
                        'setor' => $setor
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

