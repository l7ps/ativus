<?php
/**
 * Sistema Ativus - Dashboard Principal
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Dashboard';

// Obter estatísticas do dashboard
try {
    // Contar assinaturas ativas
    $assinaturasAtivas = countRecords('assinaturas', 'status = ?', ['ativo']);
    
    // Contar equipamentos cadastrados
    $equipamentosCadastrados = countRecords('equipamentos', 'status != ?', ['descartado']);
    
    // Calcular gastos do mês atual
    $mesAtual = date('Y-m');
    $gastosPagamentos = executeQuery(
        "SELECT COALESCE(SUM(valor), 0) as total FROM pagamentos WHERE strftime('%Y-%m', data_pagamento) = ?",
        [$mesAtual]
    )[0]['total'] ?? 0;
    
    $gastosManutencoes = executeQuery(
        "SELECT COALESCE(SUM(custo), 0) as total FROM manutencoes WHERE strftime('%Y-%m', data_manutencao) = ?",
        [$mesAtual]
    )[0]['total'] ?? 0;
    
    $gastosDoMes = $gastosPagamentos + $gastosManutencoes;
    
    // Próximos vencimentos (30 dias)
    $dataLimite = date('Y-m-d', strtotime('+30 days'));
    $proximosVencimentos = executeQuery(
        "SELECT * FROM assinaturas WHERE status = 'ativo' AND data_vencimento <= ? AND data_vencimento >= ? ORDER BY data_vencimento ASC",
        [$dataLimite, date('Y-m-d')]
    );
    
    // Equipamentos em manutenção
    $equipamentosManutencao = countRecords('equipamentos', 'status = ?', ['em_manutencao']);
    
    // Últimos pagamentos
    $ultimosPagamentos = executeQuery(
        "SELECT p.*, a.nome_servico 
         FROM pagamentos p 
         LEFT JOIN assinaturas a ON p.assinatura_id = a.id 
         ORDER BY p.data_pagamento DESC 
         LIMIT 5"
    );
    
    // Últimas manutenções
    $ultimasManutencoes = executeQuery(
        "SELECT m.*, e.codigo_interno, e.tipo 
         FROM manutencoes m 
         INNER JOIN equipamentos e ON m.equipamento_id = e.id 
         ORDER BY m.data_manutencao DESC 
         LIMIT 5"
    );
    
} catch (Exception $e) {
    $error = "Erro ao carregar dados do dashboard: " . $e->getMessage();
}

include 'templates/header.php';
?>

<?php if (isset($error)): ?>
    <?php echo showAlert($error, 'danger'); ?>
<?php endif; ?>

<!-- Estatísticas principais -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card">
            <div class="d-flex align-items-center">
                <div class="card-icon me-3">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <div class="card-number"><?php echo $assinaturasAtivas; ?></div>
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
                    <div class="card-number"><?php echo $equipamentosCadastrados; ?></div>
                    <div class="card-text">Equipamentos</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);">
            <div class="d-flex align-items-center">
                <div class="card-icon me-3">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div>
                    <div class="card-number"><?php echo formatMoney($gastosDoMes); ?></div>
                    <div class="card-text">Gastos do Mês</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="d-flex align-items-center">
                <div class="card-icon me-3">
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <div class="card-number"><?php echo $equipamentosManutencao; ?></div>
                    <div class="card-text">Em Manutenção</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Atalhos rápidos -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightning-charge me-2"></i>
                    Ações Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="modules/assinaturas/form.php" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nova Assinatura
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="modules/pagamentos/form.php" class="btn btn-success w-100">
                            <i class="bi bi-cash-coin me-2"></i>
                            Registrar Pagamento
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="modules/equipamentos/form.php" class="btn btn-info w-100">
                            <i class="bi bi-laptop me-2"></i>
                            Novo Equipamento
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="modules/manutencoes/form.php" class="btn btn-warning w-100">
                            <i class="bi bi-tools me-2"></i>
                            Nova Manutenção
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Próximos vencimentos -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Próximos Vencimentos
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($proximosVencimentos)): ?>
                    <p class="text-muted text-center py-3">
                        <i class="bi bi-check-circle me-2"></i>
                        Nenhum vencimento nos próximos 30 dias
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Serviço</th>
                                    <th>Vencimento</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximosVencimentos as $assinatura): ?>
                                    <?php $status = getExpiryStatus($assinatura['data_vencimento']); ?>
                                    <tr>
                                        <td><?php echo sanitize($assinatura['nome_servico']); ?></td>
                                        <td><?php echo formatDateBR($assinatura['data_vencimento']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $status['class']; ?>">
                                                <?php echo $status['text']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="modules/assinaturas/" class="btn btn-outline-primary btn-sm">
                            Ver todas as assinaturas
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Últimos pagamentos -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Últimos Pagamentos
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($ultimosPagamentos)): ?>
                    <p class="text-muted text-center py-3">
                        <i class="bi bi-inbox me-2"></i>
                        Nenhum pagamento registrado
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Serviço</th>
                                    <th>Data</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosPagamentos as $pagamento): ?>
                                    <tr>
                                        <td><?php echo sanitize($pagamento['nome_servico'] ?? 'Serviço removido'); ?></td>
                                        <td><?php echo formatDateBR($pagamento['data_pagamento']); ?></td>
                                        <td><?php echo formatMoney($pagamento['valor']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="modules/pagamentos/" class="btn btn-outline-success btn-sm">
                            Ver todos os pagamentos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Últimas manutenções -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-wrench me-2"></i>
                    Últimas Manutenções
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($ultimasManutencoes)): ?>
                    <p class="text-muted text-center py-3">
                        <i class="bi bi-inbox me-2"></i>
                        Nenhuma manutenção registrada
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Equipamento</th>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th>Tipo Manutenção</th>
                                    <th>Custo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimasManutencoes as $manutencao): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo sanitize($manutencao['codigo_interno']); ?></strong><br>
                                            <small class="text-muted"><?php echo ucfirst($manutencao['tipo']); ?></small>
                                        </td>
                                        <td><?php echo ucfirst($manutencao['tipo']); ?></td>
                                        <td><?php echo formatDateBR($manutencao['data_manutencao']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $manutencao['tipo'] == 'preventiva' ? 'info' : 'warning'; ?>">
                                                <?php echo ucfirst($manutencao['tipo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatMoney($manutencao['custo']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="modules/manutencoes/" class="btn btn-outline-warning btn-sm">
                            Ver todas as manutenções
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>

