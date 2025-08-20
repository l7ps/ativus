<?php
/**
 * Sistema Ativus - Listagem de Fornecedores
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Fornecedores';
$basePath = '../../';

// Processar exclusão
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        executeStatement("DELETE FROM fornecedores WHERE id = ?", [$id]);
        $success = "Fornecedor excluído com sucesso!";
    } catch (Exception $e) {
        $error = "Erro ao excluir fornecedor: " . $e->getMessage();
    }
}

// Buscar fornecedores
$fornecedores = executeQuery("SELECT * FROM fornecedores ORDER BY nome ASC");

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
        <i class="bi bi-truck me-2"></i>
        Fornecedores
    </h2>
    <a href="form.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Novo Fornecedor
    </a>
</div>

<!-- Tabela de Fornecedores -->
<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($fornecedores)): ?>
            <div class="alert alert-info text-center" role="alert">
                Nenhum fornecedor cadastrado ainda. <a href="form.php">Adicionar um novo fornecedor</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Contato</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fornecedores as $fornecedor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fornecedor['nome']); ?></td>
                                <td><?php echo htmlspecialchars($fornecedor['contato']); ?></td>
                                <td><?php echo htmlspecialchars($fornecedor['telefone']); ?></td>
                                <td><?php echo htmlspecialchars($fornecedor['email']); ?></td>
                                <td>
                                    <a href="form.php?id=<?php echo $fornecedor['id']; ?>" class="btn btn-sm btn-outline-primary me-2" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $fornecedor['id']; ?>, '<?php echo htmlspecialchars($fornecedor['nome']); ?>')" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(id, nome) {
    if (confirm(`Tem certeza que deseja excluir o fornecedor '${nome}'? Esta ação é irreversível.`)) {
        window.location.href = `index.php?action=delete&id=${id}`;
    }
}
</script>

<?php include '../../templates/footer.php'; ?>


