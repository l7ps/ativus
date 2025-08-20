<?php
/**
 * Sistema Ativus - Formulário de Pagamentos
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Pagamento';
$basePath = '../../';

// Verificar se é edição
$id = intval($_GET['id'] ?? 0);
$isEdit = $id > 0;

// Assinatura pré-selecionada
$preSelectedAssinatura = intval($_GET['assinatura_id'] ?? 0);

// Dados do formulário
$pagamento = [
    'assinatura_id' => $preSelectedAssinatura,
    'data_pagamento' => date('Y-m-d'),
    'valor' => '',
    'forma_pagamento' => 'pix',
    'comprovante' => '',
    'observacoes' => ''
];

// Se for edição, carregar dados
if ($isEdit) {
    $result = executeQuery("SELECT * FROM pagamentos WHERE id = ?", [$id]);
    if (empty($result)) {
        header('Location: index.php');
        exit;
    }
    $pagamento = $result[0];
    $pageTitle = 'Editar Pagamento';
} else {
    $pageTitle = 'Novo Pagamento';
}

// Buscar assinaturas ativas
$assinaturas = executeQuery("SELECT id, nome_servico, valor_padrao FROM assinaturas WHERE status = 'ativo' ORDER BY nome_servico");

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'assinatura_id' => intval($_POST['assinatura_id'] ?? 0) ?: null,
        'data_pagamento' => formatDateDB($_POST['data_pagamento'] ?? ''),
        'valor' => parseMoney($_POST['valor'] ?? '0'),
        'forma_pagamento' => sanitize($_POST['forma_pagamento'] ?? 'pix'),
        'observacoes' => sanitize($_POST['observacoes'] ?? '')
    ];
    
    // Validações
    $errors = [];
    
    if (!$dados['data_pagamento']) {
        $errors[] = "Data do pagamento é obrigatória";
    }
    
    if ($dados['valor'] <= 0) {
        $errors[] = "Valor deve ser maior que zero";
    }
    
    if (!in_array($dados['forma_pagamento'], ['cartao', 'boleto', 'pix'])) {
        $errors[] = "Forma de pagamento inválida";
    }
    
    // Upload de comprovante
    $comprovanteAtual = $pagamento['comprovante'] ?? '';
    $novoComprovante = $comprovanteAtual;
    
    if (isset($_FILES['comprovante']) && $_FILES['comprovante']['tmp_name']) {
        // Criar diretório uploads se não existir
        $uploadDir = '../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadResult = uploadFile($_FILES['comprovante'], $uploadDir);
        if ($uploadResult === false) {
            $errors[] = "Erro ao fazer upload do comprovante";
        } elseif ($uploadResult) {
            // Deletar arquivo anterior se existir
            if ($comprovanteAtual) {
                deleteFile($comprovanteAtual, $uploadDir);
            }
            $novoComprovante = $uploadResult;
        }
    }
    
    $dados['comprovante'] = $novoComprovante;
    
    // Se não há erros, salvar
    if (empty($errors)) {
        try {
            if ($isEdit) {
                $sql = "UPDATE pagamentos SET 
                        assinatura_id = ?, data_pagamento = ?, valor = ?, forma_pagamento = ?, 
                        comprovante = ?, observacoes = ?, updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?";
                $params = array_values($dados);
                $params[] = $id;
                executeStatement($sql, $params);
                $success = "Pagamento atualizado com sucesso!";
            } else {
                $sql = "INSERT INTO pagamentos (assinatura_id, data_pagamento, valor, forma_pagamento, comprovante, observacoes) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                executeStatement($sql, array_values($dados));
                $success = "Pagamento registrado com sucesso!";
                
                // Redirecionar para edição do novo registro
                $newId = getLastInsertId();
                header("Location: form.php?id=$newId&success=1");
                exit;
            }
            
            // Atualizar dados para exibição
            $pagamento = $dados;
            
        } catch (Exception $e) {
            $errors[] = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

// Verificar se veio de redirecionamento com sucesso
if (isset($_GET['success'])) {
    $success = "Pagamento registrado com sucesso!";
}

include '../../templates/header.php';
?>

<?php if (isset($success)): ?>
    <?php echo showAlert($success, 'success'); ?>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
        <?php echo showAlert($error, 'danger'); ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Cabeçalho da página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-cash-coin me-2"></i>
        <?php echo $isEdit ? 'Editar Pagamento' : 'Novo Pagamento'; ?>
    </h2>
    <a href="index.php<?php echo $preSelectedAssinatura ? '?assinatura_id=' . $preSelectedAssinatura : ''; ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>
        Voltar
    </a>
</div>

<!-- Formulário -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-form-text me-2"></i>
                    Dados do Pagamento
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" data-validate>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="assinatura_id" class="form-label">Serviço/Assinatura</label>
                            <select class="form-select" id="assinatura_id" name="assinatura_id">
                                <option value="">Selecione um serviço (opcional)</option>
                                <?php foreach ($assinaturas as $assinatura): ?>
                                    <option value="<?php echo $assinatura['id']; ?>" 
                                            data-valor="<?php echo $assinatura['valor_padrao']; ?>"
                                            <?php echo $pagamento['assinatura_id'] == $assinatura['id'] ? 'selected' : ''; ?>>
                                        <?php echo sanitize($assinatura['nome_servico']); ?>
                                        <?php if ($assinatura['valor_padrao']): ?>
                                            - <?php echo formatMoney($assinatura['valor_padrao']); ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Deixe em branco para pagamentos avulsos</div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="data_pagamento" class="form-label">Data do Pagamento *</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_pagamento" 
                                   name="data_pagamento" 
                                   value="<?php echo $pagamento['data_pagamento']; ?>" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="valor" class="form-label">Valor *</label>
                            <input type="text" 
                                   class="form-control money-input" 
                                   id="valor" 
                                   name="valor" 
                                   value="<?php echo $pagamento['valor'] ? formatMoney($pagamento['valor']) : ''; ?>" 
                                   required
                                   placeholder="R$ 0,00">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento *</label>
                            <select class="form-select" id="forma_pagamento" name="forma_pagamento" required>
                                <option value="pix" <?php echo $pagamento['forma_pagamento'] === 'pix' ? 'selected' : ''; ?>>PIX</option>
                                <option value="cartao" <?php echo $pagamento['forma_pagamento'] === 'cartao' ? 'selected' : ''; ?>>Cartão</option>
                                <option value="boleto" <?php echo $pagamento['forma_pagamento'] === 'boleto' ? 'selected' : ''; ?>>Boleto</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comprovante" class="form-label">Comprovante</label>
                        <input type="file" 
                               class="form-control" 
                               id="comprovante" 
                               name="comprovante" 
                               accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">Formatos aceitos: PDF, JPG, PNG (máx. 5MB)</div>
                        
                        <?php if ($pagamento['comprovante']): ?>
                            <div class="mt-2">
                                <small class="text-muted">Arquivo atual: </small>
                                <a href="../../uploads/<?php echo $pagamento['comprovante']; ?>" 
                                   target="_blank" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    Ver comprovante
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" 
                                  id="observacoes" 
                                  name="observacoes" 
                                  rows="3" 
                                  placeholder="Informações adicionais sobre o pagamento"><?php echo sanitize($pagamento['observacoes']); ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>
                            <?php echo $isEdit ? 'Atualizar' : 'Registrar'; ?>
                        </button>
                        
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-2"></i>
                            Cancelar
                        </a>
                        
                        <?php if ($isEdit): ?>
                            <a href="form.php" class="btn btn-outline-success">
                                <i class="bi bi-plus-circle me-2"></i>
                                Novo Pagamento
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Informações adicionais -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Informações
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Formas de Pagamento:</strong>
                    <ul class="list-unstyled mt-2 small">
                        <li><span class="badge bg-success me-2">PIX</span> Transferência instantânea</li>
                        <li><span class="badge bg-primary me-2">Cartão</span> Débito ou crédito</li>
                        <li><span class="badge bg-warning me-2">Boleto</span> Boleto bancário</li>
                    </ul>
                </div>
                
                <?php if ($isEdit): ?>
                    <div class="mb-3">
                        <strong>Registrado em:</strong><br>
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($pagamento['created_at'])); ?></small>
                    </div>
                    
                    <?php if ($pagamento['updated_at'] !== $pagamento['created_at']): ?>
                        <div class="mb-3">
                            <strong>Última atualização:</strong><br>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($pagamento['updated_at'])); ?></small>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Dicas -->
        <div class="card mt-3">
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
                        Sempre anexe o comprovante quando possível
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use observações para detalhes importantes
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Registre pagamentos no mesmo dia
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Mantenha os dados sempre atualizados
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = "
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar formatação de moeda
    const valorInput = document.getElementById('valor');
    if (valorInput) {
        valorInput.addEventListener('input', function() {
            formatMoney(this);
        });
    }
    
    // Auto-preencher valor quando selecionar assinatura
    const assinaturaSelect = document.getElementById('assinatura_id');
    assinaturaSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const valorPadrao = selectedOption.getAttribute('data-valor');
        
        if (valorPadrao && valorPadrao > 0 && !valorInput.value) {
            valorInput.value = 'R$ ' + parseFloat(valorPadrao).toFixed(2).replace('.', ',');
        }
    });
    
    // Validação de arquivo
    const comprovanteInput = document.getElementById('comprovante');
    comprovanteInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            
            if (file.size > maxSize) {
                alert('Arquivo muito grande. Máximo 5MB.');
                this.value = '';
                return;
            }
            
            if (!allowedTypes.includes(file.type)) {
                alert('Tipo de arquivo não permitido. Use PDF, JPG ou PNG.');
                this.value = '';
                return;
            }
        }
    });
});
</script>
";

include '../../templates/footer.php';
?>

