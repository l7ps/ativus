<?php
/**
 * Sistema Ativus - Formulário de Assinaturas
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Assinatura';
$basePath = '../../';

// Verificar se é edição
$id = intval($_GET['id'] ?? 0);
$isEdit = $id > 0;

// Dados do formulário
$assinatura = [
    'nome_servico' => '',
    'tipo' => 'mensal',
    'responsavel' => '',
    'valor_padrao' => '',
    'data_inicio' => '',
    'data_vencimento' => '',
    'status' => 'ativo'
];

// Se for edição, carregar dados
if ($isEdit) {
    $result = executeQuery("SELECT * FROM assinaturas WHERE id = ?", [$id]);
    if (empty($result)) {
        header('Location: index.php');
        exit;
    }
    $assinatura = $result[0];
    $pageTitle = 'Editar Assinatura';
} else {
    $pageTitle = 'Nova Assinatura';
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome_servico' => sanitize($_POST['nome_servico'] ?? ''),
        'tipo' => sanitize($_POST['tipo'] ?? 'mensal'),
        'responsavel' => sanitize($_POST['responsavel'] ?? ''),
        'valor_padrao' => parseMoney($_POST['valor_padrao'] ?? '0'),
        'data_inicio' => formatDateDB($_POST['data_inicio'] ?? ''),
        'data_vencimento' => formatDateDB($_POST['data_vencimento'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'ativo')
    ];
    
    // Validações
    $errors = [];
    
    if (empty($dados['nome_servico'])) {
        $errors[] = "Nome do serviço é obrigatório";
    }
    
    if (!in_array($dados['tipo'], ['mensal', 'anual', 'recarga'])) {
        $errors[] = "Tipo inválido";
    }
    
    if (!in_array($dados['status'], ['ativo', 'cancelado'])) {
        $errors[] = "Status inválido";
    }
    
    if ($dados['valor_padrao'] < 0) {
        $errors[] = "Valor não pode ser negativo";
    }
    
    // Se não há erros, salvar
    if (empty($errors)) {
        try {
            if ($isEdit) {
                $sql = "UPDATE assinaturas SET 
                        nome_servico = ?, tipo = ?, responsavel = ?, valor_padrao = ?, 
                        data_inicio = ?, data_vencimento = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?";
                $params = array_values($dados);
                $params[] = $id;
                executeStatement($sql, $params);
                $success = "Assinatura atualizada com sucesso!";
            } else {
                $sql = "INSERT INTO assinaturas (nome_servico, tipo, responsavel, valor_padrao, data_inicio, data_vencimento, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                executeStatement($sql, array_values($dados));
                $success = "Assinatura cadastrada com sucesso!";
                
                // Redirecionar para edição do novo registro
                $newId = getLastInsertId();
                header("Location: form.php?id=$newId&success=1");
                exit;
            }
            
            // Atualizar dados para exibição
            $assinatura = $dados;
            
        } catch (Exception $e) {
            $errors[] = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

// Verificar se veio de redirecionamento com sucesso
if (isset($_GET['success'])) {
    $success = "Assinatura cadastrada com sucesso!";
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
        <i class="bi bi-calendar-check me-2"></i>
        <?php echo $isEdit ? 'Editar Assinatura' : 'Nova Assinatura'; ?>
    </h2>
    <a href="index.php" class="btn btn-outline-secondary">
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
                    Dados da Assinatura
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" data-validate>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="nome_servico" class="form-label">Nome do Serviço *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nome_servico" 
                                   name="nome_servico" 
                                   value="<?php echo sanitize($assinatura['nome_servico']); ?>" 
                                   required
                                   placeholder="Ex: Office 365, Canva Pro, WhatsApp API">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="tipo" class="form-label">Tipo *</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="mensal" <?php echo $assinatura['tipo'] === 'mensal' ? 'selected' : ''; ?>>Mensal</option>
                                <option value="anual" <?php echo $assinatura['tipo'] === 'anual' ? 'selected' : ''; ?>>Anual</option>
                                <option value="recarga" <?php echo $assinatura['tipo'] === 'recarga' ? 'selected' : ''; ?>>Recarga</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="responsavel" class="form-label">Responsável</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="responsavel" 
                                   name="responsavel" 
                                   value="<?php echo sanitize($assinatura['responsavel']); ?>"
                                   placeholder="Ex: TI, Marketing, Vendas">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="valor_padrao" class="form-label">Valor Padrão</label>
                            <input type="text" 
                                   class="form-control money-input" 
                                   id="valor_padrao" 
                                   name="valor_padrao" 
                                   value="<?php echo $assinatura['valor_padrao'] ? formatMoney($assinatura['valor_padrao']) : ''; ?>"
                                   placeholder="R$ 0,00">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="data_inicio" class="form-label">Data de Início</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_inicio" 
                                   name="data_inicio" 
                                   value="<?php echo $assinatura['data_inicio']; ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="data_vencimento" class="form-label">Data de Vencimento</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_vencimento" 
                                   name="data_vencimento" 
                                   value="<?php echo $assinatura['data_vencimento']; ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="ativo" <?php echo $assinatura['status'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="cancelado" <?php echo $assinatura['status'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>
                            <?php echo $isEdit ? 'Atualizar' : 'Cadastrar'; ?>
                        </button>
                        
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-2"></i>
                            Cancelar
                        </a>
                        
                        <?php if ($isEdit): ?>
                            <a href="form.php" class="btn btn-outline-success">
                                <i class="bi bi-plus-circle me-2"></i>
                                Nova Assinatura
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Seção de Anexos -->
        <?php if ($isEdit): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-paperclip me-2"></i>
                    Anexos e Comprovantes
                </h6>
            </div>
            <div class="card-body">
                <!-- Upload de arquivos -->
                <div class="mb-3">
                    <label for="arquivo_upload" class="form-label">Adicionar Anexo</label>
                    <input type="file" class="form-control" id="arquivo_upload" accept="image/*,.pdf" multiple>
                    <div class="form-text">Formatos aceitos: JPG, PNG, GIF, PDF (máximo 5MB por arquivo)</div>
                </div>
                
                <button type="button" class="btn btn-outline-primary" id="btn_upload">
                    <i class="bi bi-upload me-2"></i>
                    Enviar Arquivos
                </button>
                
                <hr>
                
                <!-- Lista de anexos existentes -->
                <div id="lista_anexos">
                    <h6>Anexos Existentes:</h6>
                    <div id="anexos_container">
                        <!-- Anexos serão carregados via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
                    <strong>Tipos de Assinatura:</strong>
                    <ul class="list-unstyled mt-2 small">
                        <li><span class="badge bg-primary me-2">Mensal</span> Renovação mensal</li>
                        <li><span class="badge bg-info me-2">Anual</span> Renovação anual</li>
                        <li><span class="badge bg-warning me-2">Recarga</span> Pagamento por demanda</li>
                    </ul>
                </div>
                
                <?php if ($isEdit): ?>
                    <div class="mb-3">
                        <strong>Criado em:</strong><br>
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($assinatura['created_at'])); ?></small>
                    </div>
                    
                    <?php if ($assinatura['updated_at'] !== $assinatura['created_at']): ?>
                        <div class="mb-3">
                            <strong>Última atualização:</strong><br>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($assinatura['updated_at'])); ?></small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <a href="../pagamentos/form.php?assinatura_id=<?php echo $id; ?>" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-cash-coin me-2"></i>
                            Registrar Pagamento
                        </a>
                        
                        <a href="../pagamentos/index.php?assinatura_id=<?php echo $id; ?>" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-list-ul me-2"></i>
                            Ver Histórico de Pagamentos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($isEdit): ?>
        <!-- Histórico de Pagamentos -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Últimos Pagamentos
                </h6>
            </div>
            <div class="card-body">
                <?php
                // Buscar últimos pagamentos desta assinatura
                $ultimosPagamentos = executeQuery(
                    "SELECT * FROM pagamentos WHERE assinatura_id = ? ORDER BY data_pagamento DESC LIMIT 5",
                    [$id]
                );
                ?>
                
                <?php if (empty($ultimosPagamentos)): ?>
                    <p class="text-muted text-center small">
                        <i class="bi bi-inbox me-2"></i>
                        Nenhum pagamento registrado
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Valor</th>
                                    <th>Forma</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosPagamentos as $pagamento): ?>
                                    <tr>
                                        <td><?php echo formatDateBR($pagamento['data_pagamento']); ?></td>
                                        <td><?php echo formatMoney($pagamento['valor']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary small">
                                                <?php echo ucfirst($pagamento['forma_pagamento']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php
                    // Calcular total pago
                    $totalPago = executeQuery(
                        "SELECT COALESCE(SUM(valor), 0) as total FROM pagamentos WHERE assinatura_id = ?",
                        [$id]
                    )[0]['total'];
                    ?>
                    
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <strong>Total pago:</strong> <?php echo formatMoney($totalPago); ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
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
                        Use nomes descritivos para facilitar a identificação
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Defina o responsável para melhor controle
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Configure alertas de vencimento
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
// Aplicar formatação de moeda
document.addEventListener('DOMContentLoaded', function() {
    const moneyInput = document.getElementById('valor_padrao');
    if (moneyInput) {
        moneyInput.addEventListener('input', function() {
            formatMoney(this);
        });
    }
    
    // Calcular data de vencimento baseada no tipo
    const tipoSelect = document.getElementById('tipo');
    const dataInicio = document.getElementById('data_inicio');
    const dataVencimento = document.getElementById('data_vencimento');
    
    function calcularVencimento() {
        if (dataInicio.value && !dataVencimento.value) {
            const inicio = new Date(dataInicio.value);
            const tipo = tipoSelect.value;
            let vencimento = new Date(inicio);
            
            if (tipo === 'mensal') {
                vencimento.setMonth(vencimento.getMonth() + 1);
            } else if (tipo === 'anual') {
                vencimento.setFullYear(vencimento.getFullYear() + 1);
            }
            
            if (tipo !== 'recarga') {
                dataVencimento.value = vencimento.toISOString().split('T')[0];
            }
        }
    }
    
    tipoSelect.addEventListener('change', calcularVencimento);
    dataInicio.addEventListener('change', calcularVencimento);
    
    // Funcionalidade de anexos
    " . ($isEdit ? "
    const assinaturaId = " . $id . ";
    const btnUpload = document.getElementById('btn_upload');
    const arquivoUpload = document.getElementById('arquivo_upload');
    const anexosContainer = document.getElementById('anexos_container');
    
    // Carregar anexos existentes
    carregarAnexos();
    
    // Upload de arquivos
    btnUpload.addEventListener('click', function() {
        const files = arquivoUpload.files;
        if (files.length === 0) {
            alert('Selecione pelo menos um arquivo');
            return;
        }
        
        for (let i = 0; i < files.length; i++) {
            uploadArquivo(files[i]);
        }
        
        arquivoUpload.value = '';
    });
    
    function uploadArquivo(file) {
        const formData = new FormData();
        formData.append('arquivo', file);
        formData.append('assinatura_id', assinaturaId);
        
        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                carregarAnexos();
                showAlert('Arquivo enviado com sucesso!', 'success');
            } else {
                showAlert('Erro: ' + data.error, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Erro no upload', 'danger');
        });
    }
    
    function carregarAnexos() {
        fetch('get_anexos.php?assinatura_id=' + assinaturaId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                exibirAnexos(data.anexos);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar anexos:', error);
        });
    }
    
    function exibirAnexos(anexos) {
        if (anexos.length === 0) {
            anexosContainer.innerHTML = '<p class=\"text-muted\">Nenhum anexo encontrado</p>';
            return;
        }
        
        let html = '<div class=\"row\">';
        anexos.forEach(anexo => {
            const isImage = anexo.tipo_arquivo.startsWith('image/');
            const icon = isImage ? 'bi-image' : 'bi-file-earmark-pdf';
            
            html += `
                <div class=\"col-md-6 mb-3\">
                    <div class=\"card\">
                        <div class=\"card-body p-2\">
                            <div class=\"d-flex align-items-center\">
                                <i class=\"bi \${icon} me-2\"></i>
                                <div class=\"flex-grow-1\">
                                    <small class=\"fw-bold\">\${anexo.nome_original}</small><br>
                                    <small class=\"text-muted\">\${anexo.tamanho}</small>
                                </div>
                                <div class=\"btn-group btn-group-sm\">
                                    <a href=\"\${anexo.url}\" target=\"_blank\" class=\"btn btn-outline-primary\" title=\"Visualizar\">
                                        <i class=\"bi bi-eye\"></i>
                                    </a>
                                    <button type=\"button\" class=\"btn btn-outline-danger\" onclick=\"deletarAnexo(\${anexo.id})\" title=\"Excluir\">
                                        <i class=\"bi bi-trash\"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        anexosContainer.innerHTML = html;
    }
    
    window.deletarAnexo = function(anexoId) {
        if (!confirm('Tem certeza que deseja excluir este anexo?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('anexo_id', anexoId);
        
        fetch('delete_anexo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                carregarAnexos();
                showAlert('Anexo removido com sucesso!', 'success');
            } else {
                showAlert('Erro: ' + data.error, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Erro ao remover anexo', 'danger');
        });
    };
    " : "") . "
});
</script>
";

include '../../templates/footer.php';
?>

