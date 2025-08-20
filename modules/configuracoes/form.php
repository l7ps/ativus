<?php
/**
 * Sistema Ativus - Formulário de Configurações
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Configuração';
$basePath = '../../';

// Verificar se é edição
$id = intval($_GET['id'] ?? 0);
$isEdit = $id > 0;

// Dados do formulário
$configuracao = [
    'categoria' => '',
    'chave' => '',
    'descricao' => '',
    'tipo' => 'text',
    'valor' => '',
    'opcoes' => '',
    'obrigatorio' => 0,
    'ativo' => 1
];

// Se for edição, carregar dados
if ($isEdit) {
    $result = executeQuery("SELECT * FROM configuracoes WHERE id = ?", [$id]);
    if (empty($result)) {
        header('Location: index.php');
        exit;
    }
    $configuracao = $result[0];
    $pageTitle = 'Editar Configuração';
} else {
    $pageTitle = 'Nova Configuração';
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'categoria' => sanitize($_POST['categoria'] ?? ''),
        'chave' => sanitize($_POST['chave'] ?? ''),
        'descricao' => sanitize($_POST['descricao'] ?? ''),
        'tipo' => sanitize($_POST['tipo'] ?? 'text'),
        'valor' => sanitize($_POST['valor'] ?? ''),
        'opcoes' => sanitize($_POST['opcoes'] ?? ''),
        'obrigatorio' => isset($_POST['obrigatorio']) ? 1 : 0,
        'ativo' => isset($_POST['ativo']) ? 1 : 0
    ];
    
    // Validações
    $errors = [];
    
    if (empty($dados['categoria'])) {
        $errors[] = "Categoria é obrigatória";
    }
    
    if (empty($dados['chave'])) {
        $errors[] = "Chave é obrigatória";
    } else {
        // Verificar se chave já existe (exceto no próprio registro em edição)
        $existingKey = executeQuery(
            "SELECT id FROM configuracoes WHERE chave = ?" . ($isEdit ? " AND id != ?" : ""),
            $isEdit ? [$dados['chave'], $id] : [$dados['chave']]
        );
        if (!empty($existingKey)) {
            $errors[] = "Chave já existe";
        }
    }
    
    if (empty($dados['descricao'])) {
        $errors[] = "Descrição é obrigatória";
    }
    
    if (!in_array($dados['tipo'], ['text', 'number', 'boolean', 'select', 'textarea'])) {
        $errors[] = "Tipo inválido";
    }
    
    if ($dados['tipo'] === 'select' && empty($dados['opcoes'])) {
        $errors[] = "Opções são obrigatórias para tipo 'Lista'";
    }
    
    // Processar valor baseado no tipo
    if ($dados['tipo'] === 'boolean') {
        $dados['valor'] = isset($_POST['valor_boolean']) ? '1' : '0';
    } elseif ($dados['tipo'] === 'number') {
        $dados['valor'] = is_numeric($dados['valor']) ? $dados['valor'] : '0';
    }
    
    // Se não há erros, salvar
    if (empty($errors)) {
        try {
            if ($isEdit) {
                $sql = "UPDATE configuracoes SET 
                        categoria = ?, chave = ?, descricao = ?, tipo = ?, 
                        valor = ?, opcoes = ?, obrigatorio = ?, ativo = ?, updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?";
                $params = array_values($dados);
                $params[] = $id;
                executeStatement($sql, $params);
                $success = "Configuração atualizada com sucesso!";
            } else {
                $sql = "INSERT INTO configuracoes (categoria, chave, descricao, tipo, valor, opcoes, obrigatorio, ativo) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                executeStatement($sql, array_values($dados));
                $success = "Configuração cadastrada com sucesso!";
                
                // Redirecionar para edição do novo registro
                $newId = getLastInsertId();
                header("Location: form.php?id=$newId&success=1");
                exit;
            }
            
            // Atualizar dados para exibição
            $configuracao = $dados;
            
        } catch (Exception $e) {
            $errors[] = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

// Verificar se veio de redirecionamento com sucesso
if (isset($_GET['success'])) {
    $success = "Configuração cadastrada com sucesso!";
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
        <i class="bi bi-gear me-2"></i>
        <?php echo $isEdit ? 'Editar Configuração' : 'Nova Configuração'; ?>
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
                    Dados da Configuração
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" data-validate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="categoria" class="form-label">Categoria *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="categoria" 
                                   name="categoria" 
                                   value="<?php echo htmlspecialchars($configuracao['categoria']); ?>" 
                                   required
                                   placeholder="Ex: sistema, equipamentos, assinaturas"
                                   list="categoriasList">
                            <datalist id="categoriasList">
                                <option value="sistema">
                                <option value="equipamentos">
                                <option value="assinaturas">
                                <option value="manutencoes">
                                <option value="relatorios">
                                <option value="pagamentos">
                            </datalist>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="chave" class="form-label">Chave *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="chave" 
                                   name="chave" 
                                   value="<?php echo htmlspecialchars($configuracao['chave']); ?>" 
                                   required
                                   placeholder="Ex: tipos_equipamento, status_manutencao">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição *</label>
                        <input type="text" 
                               class="form-control" 
                               id="descricao" 
                               name="descricao" 
                               value="<?php echo htmlspecialchars($configuracao['descricao']); ?>" 
                               required
                               placeholder="Descrição clara da configuração">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo" class="form-label">Tipo *</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="text" <?php echo $configuracao['tipo'] === 'text' ? 'selected' : ''; ?>>Texto</option>
                                <option value="number" <?php echo $configuracao['tipo'] === 'number' ? 'selected' : ''; ?>>Número</option>
                                <option value="boolean" <?php echo $configuracao['tipo'] === 'boolean' ? 'selected' : ''; ?>>Sim/Não</option>
                                <option value="select" <?php echo $configuracao['tipo'] === 'select' ? 'selected' : ''; ?>>Lista</option>
                                <option value="textarea" <?php echo $configuracao['tipo'] === 'textarea' ? 'selected' : ''; ?>>Texto Longo</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" value="1" id="obrigatorio" name="obrigatorio" <?php echo $configuracao['obrigatorio'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="obrigatorio">
                                    Campo obrigatório
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="ativo" name="ativo" <?php echo $configuracao['ativo'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="ativo">
                                    Configuração ativa
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campo de valor dinâmico baseado no tipo -->
                    <div id="valor_text" class="mb-3" style="display: none;">
                        <label for="valor" class="form-label">Valor</label>
                        <input type="text" 
                               class="form-control" 
                               name="valor" 
                               value="<?php echo htmlspecialchars($configuracao['valor']); ?>"
                               placeholder="Valor da configuração">
                    </div>
                    
                    <div id="valor_number" class="mb-3" style="display: none;">
                        <label for="valor_num" class="form-label">Valor</label>
                        <input type="number" 
                               class="form-control" 
                               name="valor" 
                               value="<?php echo htmlspecialchars($configuracao['valor']); ?>"
                               placeholder="0">
                    </div>
                    
                    <div id="valor_boolean" class="mb-3" style="display: none;">
                        <label class="form-label">Valor</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="valor_bool" name="valor_boolean" <?php echo $configuracao['valor'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="valor_bool">
                                Sim
                            </label>
                        </div>
                    </div>
                    
                    <div id="valor_textarea" class="mb-3" style="display: none;">
                        <label for="valor_text_area" class="form-label">Valor</label>
                        <textarea class="form-control" 
                                  name="valor" 
                                  rows="4"
                                  placeholder="Valor da configuração"><?php echo htmlspecialchars($configuracao['valor']); ?></textarea>
                    </div>
                    
                    <div id="opcoes_field" class="mb-3" style="display: none;">
                        <label for="opcoes" class="form-label">Opções *</label>
                        <textarea class="form-control" 
                                  id="opcoes" 
                                  name="opcoes" 
                                  rows="3"
                                  placeholder="Uma opção por linha ou separadas por vírgula"><?php echo htmlspecialchars($configuracao['opcoes']); ?></textarea>
                        <div class="form-text">
                            Para listas de seleção, digite uma opção por linha ou separe por vírgula.
                        </div>
                    </div>
                    
                    <div id="valor_select" class="mb-3" style="display: none;">
                        <label for="valor_sel" class="form-label">Valor Selecionado</label>
                        <select class="form-select" id="valor_sel" name="valor">
                            <option value="">Selecione uma opção</option>
                        </select>
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
                                Nova Configuração
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Informações sobre tipos -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Tipos de Campo
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Texto:</strong>
                    <p class="small text-muted mb-2">Campo de texto simples para valores como nomes, códigos, etc.</p>
                </div>
                
                <div class="mb-3">
                    <strong>Número:</strong>
                    <p class="small text-muted mb-2">Campo numérico para valores como quantidades, preços, etc.</p>
                </div>
                
                <div class="mb-3">
                    <strong>Sim/Não:</strong>
                    <p class="small text-muted mb-2">Campo booleano para configurações de ativação/desativação.</p>
                </div>
                
                <div class="mb-3">
                    <strong>Lista:</strong>
                    <p class="small text-muted mb-2">Lista de opções para seleção. Define as opções disponíveis no sistema.</p>
                </div>
                
                <div class="mb-0">
                    <strong>Texto Longo:</strong>
                    <p class="small text-muted mb-0">Campo de texto maior para descrições, observações, etc.</p>
                </div>
            </div>
        </div>
        
        <!-- Exemplos -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-lightbulb me-2"></i>
                    Exemplos de Uso
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <strong>tipos_equipamento:</strong> notebook,desktop,impressora
                    </li>
                    <li class="mb-2">
                        <strong>status_manutencao:</strong> pendente,em_andamento,concluida
                    </li>
                    <li class="mb-2">
                        <strong>prazo_garantia_padrao:</strong> 36 (meses)
                    </li>
                    <li class="mb-2">
                        <strong>backup_automatico:</strong> true/false
                    </li>
                    <li class="mb-0">
                        <strong>observacoes_padrao:</strong> Texto longo...
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const tipoSelect = document.getElementById("tipo");
    const opcoesField = document.getElementById("opcoes_field");
    const valorText = document.getElementById("valor_text");
    const valorNumber = document.getElementById("valor_number");
    const valorBoolean = document.getElementById("valor_boolean");
    const valorTextarea = document.getElementById("valor_textarea");
    const valorSelect = document.getElementById("valor_select");
    const valorSelElement = document.getElementById("valor_sel");
    const opcoesTextarea = document.getElementById("opcoes");

    function toggleFields() {
        // Esconder todos os campos
        valorText.style.display = "none";
        valorNumber.style.display = "none";
        valorBoolean.style.display = "none";
        valorTextarea.style.display = "none";
        valorSelect.style.display = "none";
        opcoesField.style.display = "none";

        // Mostrar campo apropriado
        const tipo = tipoSelect.value;
        
        switch(tipo) {
            case 'text':
                valorText.style.display = "block";
                break;
            case 'number':
                valorNumber.style.display = "block";
                break;
            case 'boolean':
                valorBoolean.style.display = "block";
                break;
            case 'textarea':
                valorTextarea.style.display = "block";
                break;
            case 'select':
                opcoesField.style.display = "block";
                valorSelect.style.display = "block";
                updateSelectOptions();
                break;
        }
    }

    function updateSelectOptions() {
        const opcoes = opcoesTextarea.value.trim();
        valorSelElement.innerHTML = '<option value="">Selecione uma opção</option>';
        
        if (opcoes) {
            const lista = opcoes.includes('\n') ? opcoes.split('\n') : opcoes.split(',');
            const valorAtual = '<?php echo addslashes($configuracao['valor']); ?>';
            
            lista.forEach(opcao => {
                const opcaoTrim = opcao.trim();
                if (opcaoTrim) {
                    const option = document.createElement('option');
                    option.value = opcaoTrim;
                    option.textContent = opcaoTrim;
                    if (opcaoTrim === valorAtual) {
                        option.selected = true;
                    }
                    valorSelElement.appendChild(option);
                }
            });
        }
    }

    // Event Listeners
    tipoSelect.addEventListener("change", toggleFields);
    opcoesTextarea.addEventListener("input", updateSelectOptions);

    // Initial call on page load
    toggleFields();
});
</script>

<?php include '../../templates/footer.php'; ?>

