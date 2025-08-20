<?php
/**
 * Sistema Ativus - Formulário de Manutenções
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Manutenção';
$basePath = '../../';

// Verificar se é edição
$id = intval($_GET['id'] ?? 0);
$isEdit = $id > 0;

// Equipamento pré-selecionado
$preSelectedEquipamento = intval($_GET['equipamento_id'] ?? 0);

// Dados do formulário
$manutencao = [
    'equipamento_id' => $preSelectedEquipamento,
    'data_manutencao' => date('Y-m-d'),
    'tipo' => 'preventiva',
    'custo' => '',
    'observacoes' => '',
    'fornecedor_id' => null // Adicionado campo fornecedor_id
];

// Se for edição, carregar dados
if ($isEdit) {
    $result = executeQuery("SELECT * FROM manutencoes WHERE id = ?", [$id]);
    if (empty($result)) {
        header('Location: index.php');
        exit;
    }
    $manutencao = $result[0];
    $pageTitle = 'Editar Manutenção';
} else {
    $pageTitle = 'Nova Manutenção';
}

// Buscar equipamentos ativos
$equipamentos = executeQuery("SELECT id, codigo_interno, marca_modelo, tipo, status FROM equipamentos WHERE status != 'descartado' ORDER BY codigo_interno");

// Buscar fornecedores para o select
$fornecedores = executeQuery("SELECT id, nome FROM fornecedores ORDER BY nome ASC");

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fornecedorId = !empty($_POST['fornecedor_id']) ? intval($_POST['fornecedor_id']) : null; // Permite NULL

    $dados = [
        'equipamento_id' => intval($_POST['equipamento_id'] ?? 0),
        'data_manutencao' => formatDateDB($_POST['data_manutencao'] ?? ''),
        'tipo' => sanitize($_POST['tipo'] ?? 'preventiva'),
        'custo' => parseMoney($_POST['custo'] ?? '0'),
        'observacoes' => sanitize($_POST['observacoes'] ?? ''),
        'fornecedor_id' => $fornecedorId // Usa o valor ajustado
    ];
    
    // Validações
    $errors = [];
    
    if ($dados['equipamento_id'] <= 0) {
        $errors[] = "Equipamento é obrigatório";
    } else {
        // Verificar se equipamento existe
        $equipamentoExists = executeQuery("SELECT id FROM equipamentos WHERE id = ?", [$dados['equipamento_id']]);
        if (empty($equipamentoExists)) {
            $errors[] = "Equipamento não encontrado";
        }
    }
    
    if (!$dados['data_manutencao']) {
        $errors[] = "Data da manutenção é obrigatória";
    }
    
    if (!in_array($dados['tipo'], ['preventiva', 'corretiva'])) {
        $errors[] = "Tipo de manutenção inválido";
    }
    
    if ($dados['custo'] < 0) {
        $errors[] = "Custo não pode ser negativo";
    }
    
    // Se não há erros, salvar
    if (empty($errors)) {
        try {
            if ($isEdit) {
                $sql = "UPDATE manutencoes SET 
                        equipamento_id = ?, data_manutencao = ?, tipo = ?, custo = ?, 
                        observacoes = ?, fornecedor_id = ?, updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?";
                $params = array_values($dados);
                $params[] = $id;
                executeStatement($sql, $params);
                $success = "Manutenção atualizada com sucesso!";
            } else {
                $sql = "INSERT INTO manutencoes (equipamento_id, data_manutencao, tipo, custo, observacoes, fornecedor_id) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                executeStatement($sql, array_values($dados));
                $success = "Manutenção registrada com sucesso!";
                
                // Atualizar status do equipamento se necessário
                if ($dados['tipo'] === 'corretiva') {
                    executeStatement("UPDATE equipamentos SET status = 'em_manutencao' WHERE id = ?", [$dados['equipamento_id']]);
                }
                
                // Redirecionar para edição do novo registro
                $newId = getLastInsertId();
                header("Location: form.php?id=$newId&success=1");
                exit;
            }
            
            // Atualizar dados para exibição
            $manutencao = $dados;
            
        } catch (Exception $e) {
            $errors[] = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

// Verificar se veio de redirecionamento com sucesso
if (isset($_GET['success'])) {
    $success = "Manutenção registrada com sucesso!";
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
        <i class="bi bi-tools me-2"></i>
        <?php echo $isEdit ? 'Editar Manutenção' : 'Nova Manutenção'; ?>
    </h2>
    <a href="index.php<?php echo $preSelectedEquipamento ? '?equipamento_id=' . $preSelectedEquipamento : ''; ?>" class="btn btn-outline-secondary">
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
                    Dados da Manutenção
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" data-validate>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="equipamento_id" class="form-label">Equipamento *</label>
                            <select class="form-select" id="equipamento_id" name="equipamento_id" required>
                                <option value="">Selecione um equipamento</option>
                                <?php foreach ($equipamentos as $equipamento): ?>
                                    <option value="<?php echo $equipamento['id']; ?>" 
                                            data-status="<?php echo $equipamento['status']; ?>"
                                            <?php echo $manutencao['equipamento_id'] == $equipamento['id'] ? 'selected' : ''; ?>>
                                        <?php echo sanitize($equipamento['codigo_interno'] . ' - ' . $equipamento['marca_modelo']); ?>
                                        (<?php echo ucfirst($equipamento['tipo']); ?>)
                                        <?php if ($equipamento['status'] === 'em_manutencao'): ?>
                                            - EM MANUTENÇÃO
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="data_manutencao" class="form-label">Data da Manutenção *</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_manutencao" 
                                   name="data_manutencao" 
                                   value="<?php echo $manutencao['data_manutencao']; ?>" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo" class="form-label">Tipo de Manutenção *</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="preventiva" <?php echo $manutencao['tipo'] === 'preventiva' ? 'selected' : ''; ?>>Preventiva</option>
                                <option value="corretiva" <?php echo $manutencao['tipo'] === 'corretiva' ? 'selected' : ''; ?>>Corretiva</option>
                            </select>
                            <div class="form-text">
                                <strong>Preventiva:</strong> Manutenção programada<br>
                                <strong>Corretiva:</strong> Reparo de problema
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="custo" class="form-label">Custo</label>
                            <input type="text" 
                                   class="form-control money-input" 
                                   id="custo" 
                                   name="custo" 
                                   value="<?php echo $manutencao['custo'] ? formatMoney($manutencao['custo']) : ''; ?>"
                                   placeholder="R$ 0,00">
                            <div class="form-text">Deixe em branco ou R$ 0,00 para manutenção gratuita</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="fornecedor_id" class="form-label">Fornecedor da Manutenção</label>
                        <select class="form-select" id="fornecedor_id" name="fornecedor_id">
                            <option value="">Nenhum</option>
                            <?php foreach ($fornecedores as $forn): ?>
                                <option value="<?php echo $forn['id']; ?>" <?php echo $manutencao['fornecedor_id'] == $forn['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($forn['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecione o fornecedor que realizou esta manutenção.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" 
                                  id="observacoes" 
                                  name="observacoes" 
                                  rows="4" 
                                  placeholder="Descreva o que foi feito, problemas encontrados, peças trocadas, etc."><?php echo sanitize($manutencao['observacoes']); ?></textarea>
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
                                Nova Manutenção
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Informações do equipamento selecionado -->
        <div class="card" id="equipamento-info" style="display: none;">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-laptop me-2"></i>
                    Informações do Equipamento
                </h6>
            </div>
            <div class="card-body" id="equipamento-details">
                <!-- Preenchido via JavaScript -->
            </div>
        </div>
        
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
                    <strong>Tipos de Manutenção:</strong>
                    <ul class="list-unstyled mt-2 small">
                        <li><span class="badge bg-info me-2">Preventiva</span> Manutenção programada para evitar problemas</li>
                        <li><span class="badge bg-warning me-2">Corretiva</span> Reparo de problema</li>
                    </ul>
                </div>
                
                <?php if ($isEdit): ?>
                    <div class="mb-3">
                        <strong>Registrada em:</strong><br>
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($manutencao['created_at'])); ?></small>
                    </div>
                    
                    <?php if ($manutencao['updated_at'] !== $manutencao['created_at']): ?>
                        <div class="mb-3">
                            <strong>Última atualização:</strong><br>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($manutencao['updated_at'])); ?></small>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Histórico de manutenções do equipamento -->
        <?php if ($isEdit || $preSelectedEquipamento): ?>
            <?php 
            $equipamentoIdHistorico = $isEdit ? $manutencao['equipamento_id'] : $preSelectedEquipamento;
            $historicoManutencoes = executeQuery(
                "SELECT * FROM manutencoes WHERE equipamento_id = ?" . ($isEdit ? " AND id != ?" : "") . " ORDER BY data_manutencao DESC LIMIT 5",
                $isEdit ? [$equipamentoIdHistorico, $id] : [$equipamentoIdHistorico]
            );
            ?>
            
            <?php if (!empty($historicoManutencoes)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Histórico Recente
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($historicoManutencoes as $historico): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <small class="text-muted"><?php echo formatDateBR($historico['data_manutencao']); ?></small>
                                    <br>
                                    <span class="badge bg-<?php echo $historico['tipo'] == 'preventiva' ? 'info' : 'warning'; ?> small">
                                        <?php echo ucfirst($historico['tipo']); ?>
                                    </span>
                                </div>
                                <div class="text-end">
                                    <strong><?php echo formatMoney($historico['custo']); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="index.php?equipamento_id=<?php echo $equipamentoIdHistorico; ?>" class="btn btn-outline-info btn-sm">
                                Ver histórico completo
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
                        Registre manutenções preventivas regularmente
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Detalhe bem as observações para futuras referências
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Inclua custos de peças e mão de obra
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Mantenha histórico atualizado para controle
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
    const custoInput = document.getElementById('custo');
    if (custoInput) {
        custoInput.addEventListener('input', function() {
            formatMoney(this);
        });
    }
    
    // Mostrar informações do equipamento selecionado
    const equipamentoSelect = document.getElementById('equipamento_id');
    const equipamentoInfoCard = document.getElementById('equipamento-info');
    const equipamentoDetailsDiv = document.getElementById('equipamento-details');

    function loadEquipamentoDetails(equipamentoId) {
        if (equipamentoId) {
            fetch(`../../api/get_equipamento_details.php?id=${equipamentoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const eq = data.equipamento;
                        let statusBadgeClass = '';
                        if (eq.status === 'ativo') statusBadgeClass = 'bg-success';
                        else if (eq.status === 'em_manutencao') statusBadgeClass = 'bg-warning text-dark';
                        else if (eq.status === 'descartado') statusBadgeClass = 'bg-danger';

                        equipamentoDetailsDiv.innerHTML = `
                            <p><strong>Código:</strong> ${eq.codigo_interno}</p>
                            <p><strong>Tipo:</strong> ${eq.tipo}</p>
                            <p><strong>Marca/Modelo:</strong> ${eq.marca_modelo}</p>
                            <p><strong>Status:</strong> <span class=\"badge ${statusBadgeClass}\">${eq.status}</span></p>
                            <p><strong>Responsável:</strong> ${eq.responsavel || 'N/A'}</p>
                            <p><strong>Setor/Sala:</strong> ${eq.setor_sala || 'N/A'}</p>
                            <p><strong>Fornecedor:</strong> ${eq.fornecedor_nome || 'N/A'}</p>
                            <a href=\"../equipamentos/form.php?id=${eq.id}\" class=\"btn btn-sm btn-outline-info mt-2\">Ver Detalhes do Equipamento</a>
                        `;
                        equipamentoInfoCard.style.display = 'block';
                    } else {
                        equipamentoInfoCard.style.display = 'none';
                        console.error('Erro ao carregar detalhes do equipamento:', data.message);
                    }
                })
                .catch(error => {
                    equipamentoInfoCard.style.display = 'none';
                    console.error('Erro na requisição fetch:', error);
                });
        } else {
            equipamentoInfoCard.style.display = 'none';
        }
    }

    // Carregar detalhes do equipamento se já houver um selecionado na carga da página
    if (equipamentoSelect.value) {
        loadEquipamentoDetails(equipamentoSelect.value);
    }

    equipamentoSelect.addEventListener('change', function() {
        loadEquipamentoDetails(this.value);
    });
});
</script>
";

include '../../templates/footer.php';
?>

