<?php
/**
 * Sistema Ativus - Formulário de Fornecedores
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Fornecedor';
$basePath = '../../';

// Verificar se é edição
$id = intval($_GET['id'] ?? 0);
$isEdit = $id > 0;

// Dados do formulário
$fornecedor = [
    'nome' => '',
    'contato' => '',
    'telefone' => '',
    'email' => '',
    'endereco' => '',
    'observacoes' => ''
];

// Se for edição, carregar dados
if ($isEdit) {
    $result = executeQuery("SELECT * FROM fornecedores WHERE id = ?", [$id]);
    if (empty($result)) {
        header('Location: index.php');
        exit;
    }
    $fornecedor = $result[0];
    $pageTitle = 'Editar Fornecedor';
} else {
    $pageTitle = 'Novo Fornecedor';
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome' => sanitize($_POST['nome'] ?? ''),
        'contato' => sanitize($_POST['contato'] ?? ''),
        'telefone' => sanitize($_POST['telefone'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'endereco' => sanitize($_POST['endereco'] ?? ''),
        'observacoes' => sanitize($_POST['observacoes'] ?? '')
    ];
    
    // Validações
    $errors = [];
    
    if (empty($dados['nome'])) {
        $errors[] = "Nome do fornecedor é obrigatório";
    }
    
    if (!empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }
    
    // Se não há erros, salvar
    if (empty($errors)) {
        try {
            if ($isEdit) {
                $sql = "UPDATE fornecedores SET 
                        nome = ?, contato = ?, telefone = ?, email = ?, endereco = ?, observacoes = ?, updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?";
                $params = array_values($dados);
                $params[] = $id;
                executeStatement($sql, $params);
                $success = "Fornecedor atualizado com sucesso!";
            } else {
                $sql = "INSERT INTO fornecedores (nome, contato, telefone, email, endereco, observacoes) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                executeStatement($sql, array_values($dados));
                $success = "Fornecedor cadastrado com sucesso!";
                
                // Redirecionar para edição do novo registro
                $newId = getLastInsertId();
                header("Location: form.php?id=$newId&success=1");
                exit;
            }
            
            // Atualizar dados para exibição
            $fornecedor = $dados;
            
        } catch (Exception $e) {
            $errors[] = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

include '../../templates/header.php';
?>

<?php if (isset($success)): ?>
    <?php echo showAlert($success, 'success'); ?>
<?php endif; ?>

<?php if (isset($errors)): ?>
    <?php foreach ($errors as $err): ?>
        <?php echo showAlert($err, 'danger'); ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Cabeçalho da página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-truck me-2"></i>
        <?php echo $pageTitle; ?>
    </h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>
        Voltar
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Fornecedor *</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($fornecedor['nome']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="contato" class="form-label">Pessoa de Contato</label>
                        <input type="text" class="form-control" id="contato" name="contato" value="<?php echo htmlspecialchars($fornecedor['contato']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($fornecedor['telefone']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($fornecedor['email']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="endereco" class="form-label">Endereço</label>
                        <textarea class="form-control" id="endereco" name="endereco" rows="3"><?php echo htmlspecialchars($fornecedor['endereco']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo htmlspecialchars($fornecedor['observacoes']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>
                        Salvar Fornecedor
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Dicas -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-lightbulb me-2"></i>
                    Dicas
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Mantenha os dados do fornecedor atualizados para facilitar o contato.
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Utilize o campo de observações para informações importantes sobre o relacionamento.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>


