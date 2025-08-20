<?php
/**
 * Sistema Ativus - Módulo de Relatórios
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Relatórios';
$basePath = '../../';

// Processar geração de relatórios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoRelatorio = $_POST['tipo_relatorio'] ?? '';
    $dataInicio = $_POST['data_inicio'] ?? '';
    $dataFim = $_POST['data_fim'] ?? '';
    $formato = $_POST['formato'] ?? 'html';
    
    try {
        switch ($tipoRelatorio) {
            case 'assinaturas_vencimento':
                $dados = gerarRelatorioAssinaturasVencimento($dataInicio, $dataFim);
                break;
            case 'equipamentos_status':
                $dados = gerarRelatorioEquipamentosStatus();
                break;
            case 'gastos_periodo':
                $dados = gerarRelatorioGastosPeriodo($dataInicio, $dataFim);
                break;
            case 'manutencoes_periodo':
                $dados = gerarRelatorioManutencoesPeriodo($dataInicio, $dataFim);
                break;
            case 'inventario_completo':
                $dados = gerarRelatorioInventarioCompleto();
                break;
            default:
                throw new Exception('Tipo de relatório inválido');
        }
        
        if ($formato === 'csv') {
            exportarRelatorioCSV($dados, $tipoRelatorio);
        } else {
            $relatorioGerado = true;
        }
        
    } catch (Exception $e) {
        $error = "Erro ao gerar relatório: " . $e->getMessage();
    }
}

// Funções para gerar relatórios
function gerarRelatorioAssinaturasVencimento($dataInicio = '', $dataFim = '') {
    $whereClause = "WHERE status = 'ativo'";
    $params = [];
    
    if ($dataInicio && $dataFim) {
        $whereClause .= " AND data_vencimento BETWEEN ? AND ?";
        $params = [formatDateDB($dataInicio), formatDateDB($dataFim)];
    } elseif (!$dataInicio && !$dataFim) {
        // Próximos 30 dias por padrão
        $whereClause .= " AND data_vencimento BETWEEN ? AND ?";
        $params = [date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))];
    }
    
    $sql = "SELECT * FROM assinaturas $whereClause ORDER BY data_vencimento ASC";
    return executeQuery($sql, $params);
}

function gerarRelatorioEquipamentosStatus() {
    $sql = "SELECT 
                tipo,
                status,
                COUNT(*) as quantidade,
                ROUND(AVG(valor_aquisicao), 2) as valor_medio
            FROM equipamentos 
            GROUP BY tipo, status 
            ORDER BY tipo, status";
    return executeQuery($sql);
}

function gerarRelatorioGastosPeriodo($dataInicio, $dataFim) {
    $params = [formatDateDB($dataInicio), formatDateDB($dataFim)];
    
    // Gastos com pagamentos
    $pagamentos = executeQuery(
        "SELECT 
            'Pagamento' as tipo,
            a.nome_servico as descricao,
            p.data_pagamento as data,
            p.valor,
            p.forma_pagamento
         FROM pagamentos p
         LEFT JOIN assinaturas a ON p.assinatura_id = a.id
         WHERE p.data_pagamento BETWEEN ? AND ?
         ORDER BY p.data_pagamento DESC",
        $params
    );
    
    // Gastos com manutenções
    $manutencoes = executeQuery(
        "SELECT 
            'Manutenção' as tipo,
            CONCAT(e.codigo_interno, ' - ', e.marca_modelo) as descricao,
            m.data_manutencao as data,
            m.custo as valor,
            m.tipo as forma_pagamento
         FROM manutencoes m
         INNER JOIN equipamentos e ON m.equipamento_id = e.id
         WHERE m.data_manutencao BETWEEN ? AND ? AND m.custo > 0
         ORDER BY m.data_manutencao DESC",
        $params
    );
    
    return array_merge($pagamentos, $manutencoes);
}

function gerarRelatorioManutencoesPeriodo($dataInicio, $dataFim) {
    $params = [formatDateDB($dataInicio), formatDateDB($dataFim)];
    
    $sql = "SELECT 
                m.*,
                e.codigo_interno,
                e.tipo as tipo_equipamento,
                e.marca_modelo,
                e.responsavel,
                e.setor_sala
            FROM manutencoes m
            INNER JOIN equipamentos e ON m.equipamento_id = e.id
            WHERE m.data_manutencao BETWEEN ? AND ?
            ORDER BY m.data_manutencao DESC";
    
    return executeQuery($sql, $params);
}

function gerarRelatorioInventarioCompleto() {
    $sql = "SELECT 
                e.*,
                COUNT(m.id) as total_manutencoes,
                COALESCE(SUM(m.custo), 0) as custo_total_manutencoes,
                MAX(m.data_manutencao) as ultima_manutencao
            FROM equipamentos e
            LEFT JOIN manutencoes m ON e.id = m.equipamento_id
            GROUP BY e.id
            ORDER BY e.codigo_interno";
    
    return executeQuery($sql);
}

function exportarRelatorioCSV($dados, $tipo) {
    $filename = "relatorio_{$tipo}_" . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    if (!empty($dados)) {
        // Cabeçalhos
        fputcsv($output, array_keys($dados[0]), ';');
        
        // Dados
        foreach ($dados as $linha) {
            fputcsv($output, $linha, ';');
        }
    }
    
    fclose($output);
    exit;
}

include '../../templates/header.php';
?>

<?php if (isset($error)): ?>
    <?php echo showAlert($error, 'danger'); ?>
<?php endif; ?>

<!-- Cabeçalho da página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-graph-up me-2"></i>
        Relatórios e Análises
    </h2>
</div>

<!-- Estatísticas rápidas -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card">
            <div class="d-flex align-items-center">
                <div class="card-icon me-3">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <div class="card-number"><?php echo countRecords('assinaturas', 'status = ?', ['ativo']); ?></div>
                    <div class="card-text">Assinaturas Ativas</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="d-flex align-items-center">
                <div class="card-icon me-3">
                    <i class="bi bi-laptop"></i>
                </div>
                <div>
                    <div class="card-number"><?php echo countRecords('equipamentos', 'status != ?', ['descartado']); ?></div>
                    <div class="card-text">Equipamentos Ativos</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);">
            <div class="d-flex align-items-center">
                <div class="card-icon me-3">
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <div class="card-number"><?php echo countRecords('manutencoes', 'data_manutencao >= ?', [date('Y-m-01')]); ?></div>
                    <div class="card-text">Manutenções do Mês</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="d-flex align-items-center">
                <div class="card-icon me-3">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div>
                    <?php
                    $gastosMes = executeQuery(
                        "SELECT COALESCE(SUM(valor), 0) as total FROM pagamentos WHERE strftime('%Y-%m', data_pagamento) = ?",
                        [date('Y-m')]
                    )[0]['total'] ?? 0;
                    ?>
                    <div class="card-number"><?php echo formatMoney($gastosMes); ?></div>
                    <div class="card-text">Gastos do Mês</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gerador de Relatórios -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Gerar Relatório
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_relatorio" class="form-label">Tipo de Relatório *</label>
                            <select class="form-select" id="tipo_relatorio" name="tipo_relatorio" required>
                                <option value="">Selecione um relatório</option>
                                <option value="assinaturas_vencimento">Assinaturas - Próximos Vencimentos</option>
                                <option value="equipamentos_status">Equipamentos - Status por Tipo</option>
                                <option value="gastos_periodo">Gastos por Período</option>
                                <option value="manutencoes_periodo">Manutenções por Período</option>
                                <option value="inventario_completo">Inventário Completo</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="formato" class="form-label">Formato *</label>
                            <select class="form-select" id="formato" name="formato" required>
                                <option value="html">Visualizar na Tela</option>
                                <option value="csv">Exportar CSV</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row" id="periodo_fields" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="data_inicio" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="data_fim" class="form-label">Data Fim</label>
                            <input type="date" class="form-control" id="data_fim" name="data_fim">
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-fill me-2"></i>
                            Gerar Relatório
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Exibição do Relatório -->
        <?php if (isset($relatorioGerado) && $relatorioGerado && !empty($dados)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-table me-2"></i>
                    Resultado do Relatório
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <?php foreach (array_keys($dados[0]) as $coluna): ?>
                                    <th><?php echo ucfirst(str_replace('_', ' ', $coluna)); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dados as $linha): ?>
                                <tr>
                                    <?php foreach ($linha as $valor): ?>
                                        <td>
                                            <?php 
                                            // Formatação especial para alguns campos
                                            if (is_numeric($valor) && strpos($valor, '.') !== false && $valor > 0) {
                                                echo formatMoney($valor);
                                            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                                                echo formatDateBR($valor);
                                            } else {
                                                echo sanitize($valor);
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="tipo_relatorio" value="<?php echo $tipoRelatorio; ?>">
                        <input type="hidden" name="data_inicio" value="<?php echo $dataInicio; ?>">
                        <input type="hidden" name="data_fim" value="<?php echo $dataFim; ?>">
                        <input type="hidden" name="formato" value="csv">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-download me-2"></i>
                            Exportar como CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-4">
        <!-- Relatórios Rápidos -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-lightning-charge me-2"></i>
                    Relatórios Rápidos
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="tipo_relatorio" value="assinaturas_vencimento">
                        <input type="hidden" name="formato" value="html">
                        <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Vencimentos (30 dias)
                        </button>
                    </form>
                    
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="tipo_relatorio" value="equipamentos_status">
                        <input type="hidden" name="formato" value="html">
                        <button type="submit" class="btn btn-outline-info btn-sm w-100">
                            <i class="bi bi-laptop me-2"></i>
                            Status dos Equipamentos
                        </button>
                    </form>
                    
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="tipo_relatorio" value="inventario_completo">
                        <input type="hidden" name="formato" value="html">
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-list-check me-2"></i>
                            Inventário Completo
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Dicas -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-lightbulb me-2"></i>
                    Dicas de Uso
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use relatórios de vencimento para planejamento financeiro
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Exporte dados em CSV para análises externas
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Monitore gastos mensais para controle de orçamento
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use o inventário para auditoria de ativos
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
    const tipoRelatorio = document.getElementById('tipo_relatorio');
    const periodoFields = document.getElementById('periodo_fields');
    
    function togglePeriodoFields() {
        const valor = tipoRelatorio.value;
        const precisaPeriodo = ['gastos_periodo', 'manutencoes_periodo'].includes(valor);
        
        if (precisaPeriodo) {
            periodoFields.style.display = 'flex';
            document.getElementById('data_inicio').required = true;
            document.getElementById('data_fim').required = true;
        } else {
            periodoFields.style.display = 'none';
            document.getElementById('data_inicio').required = false;
            document.getElementById('data_fim').required = false;
        }
    }
    
    tipoRelatorio.addEventListener('change', togglePeriodoFields);
    togglePeriodoFields();
});
</script>
";

include '../../templates/footer.php';
?>

