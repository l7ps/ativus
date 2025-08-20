<?php
/**
 * Sistema Ativus - Configurações do Sistema
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Configurações do Sistema';
$basePath = '../../';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        try {
            executeStatement("DELETE FROM configuracoes WHERE id = ?", [$id]);
            $success = "Configuração removida com sucesso!";
        } catch (Exception $e) {
            $error = "Erro ao remover configuração: " . $e->getMessage();
        }
    }
}

// Buscar configurações
$search = sanitize($_GET['search'] ?? '');
$categoria = sanitize($_GET['categoria'] ?? '');

$whereClause = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (chave LIKE ? OR descricao LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($categoria)) {
    $whereClause .= " AND categoria = ?";
    $params[] = $categoria;
}

$configuracoes = executeQuery("
    SELECT * FROM configuracoes 
    $whereClause 
    ORDER BY categoria, chave
", $params);

// Buscar categorias para filtro
$categorias = executeQuery("SELECT DISTINCT categoria FROM configuracoes ORDER BY categoria");

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
        <i class="bi bi-gear me-2"></i>
        Configurações do Sistema
    </h2>
    <a href="form.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Nova Configuração
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Chave ou descrição...">
            </div>
            
            <div class="col-md-4">
                <label for="categoria" class="form-label">Categoria</label>
                <select class="form-select" id="categoria" name="categoria">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['categoria']); ?>" 
                                <?php echo $categoria === $cat['categoria'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-search me-1"></i>
                    Filtrar
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de configurações -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>
            Configurações (<?php echo count($configuracoes); ?>)
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($configuracoes)): ?>
            <div class="text-center py-5">
                <i class="bi bi-gear display-1 text-muted"></i>
                <p class="mt-3 text-muted">Nenhuma configuração encontrada</p>
                <a href="form.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Criar primeira configuração
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Categoria</th>
                            <th>Chave</th>
                            <th>Descrição</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($configuracoes as $config): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($config['categoria']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($config['chave']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($config['descricao']); ?>
                                </td>
                                <td>
                                    <?php
                                    $tipos = [
                                        'text' => 'Texto',
                                        'number' => 'Número',
                                        'boolean' => 'Sim/Não',
                                        'select' => 'Lista',
                                        'textarea' => 'Texto Longo'
                                    ];
                                    echo $tipos[$config['tipo']] ?? $config['tipo'];
                                    ?>
                                </td>
                                <td>
                                    <?php if ($config['tipo'] === 'boolean'): ?>
                                        <span class="badge bg-<?php echo $config['valor'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $config['valor'] ? 'Sim' : 'Não'; ?>
                                        </span>
                                    <?php elseif ($config['tipo'] === 'select'): ?>
                                        <code><?php echo htmlspecialchars($config['valor']); ?></code>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars(substr($config['valor'], 0, 50)); ?>
                                        <?php if (strlen($config['valor']) > 50): ?>...<?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="form.php?id=<?php echo $config['id']; ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="confirmDelete(<?php echo $config['id']; ?>, '<?php echo addslashes($config['chave']); ?>')"
                                                title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Informações sobre configurações -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Tipos de Configuração
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <strong>Texto:</strong> Valores simples de texto
                    </li>
                    <li class="mb-2">
                        <strong>Número:</strong> Valores numéricos
                    </li>
                    <li class="mb-2">
                        <strong>Sim/Não:</strong> Valores booleanos (true/false)
                    </li>
                    <li class="mb-2">
                        <strong>Lista:</strong> Opções separadas por vírgula
                    </li>
                    <li class="mb-0">
                        <strong>Texto Longo:</strong> Textos maiores (textarea)
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-lightbulb me-2"></i>
                    Categorias Sugeridas
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <strong>sistema:</strong> Configurações gerais do sistema
                    </li>
                    <li class="mb-2">
                        <strong>equipamentos:</strong> Opções para equipamentos
                    </li>
                    <li class="mb-2">
                        <strong>assinaturas:</strong> Configurações de assinaturas
                    </li>
                    <li class="mb-2">
                        <strong>manutencoes:</strong> Opções de manutenção
                    </li>
                    <li class="mb-0">
                        <strong>relatorios:</strong> Configurações de relatórios
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a configuração <strong id="deleteConfigName"></strong>?</p>
                <p class="text-muted small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteConfigId">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteConfigId').value = id;
    document.getElementById('deleteConfigName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include '../../templates/footer.php'; ?>

