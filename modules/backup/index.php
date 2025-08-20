<?php
/**
 * Sistema Ativus - Módulo de Backup
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Backup e Importação';
$basePath = '../../';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'backup_completo':
            try {
                $filename = 'backup_completo_' . date('Y-m-d_H-i-s') . '.sql';
                $filepath = '../../backups/' . $filename;
                
                // Criar diretório de backups se não existir
                if (!is_dir('../../backups')) {
                    mkdir('../../backups', 0755, true);
                }
                
                $success = createFullBackup($filepath);
                if ($success) {
                    $success = "Backup completo criado com sucesso! <a href='../../backups/$filename' class='btn btn-sm btn-outline-primary ms-2'><i class='bi bi-download'></i> Download</a>";
                } else {
                    $error = "Erro ao criar backup completo.";
                }
            } catch (Exception $e) {
                $error = "Erro ao criar backup: " . $e->getMessage();
            }
            break;
            
        case 'backup_seletivo':
            try {
                $modulos = $_POST['modulos'] ?? [];
                if (empty($modulos)) {
                    $error = "Selecione pelo menos um módulo para backup.";
                    break;
                }
                
                $filename = 'backup_seletivo_' . date('Y-m-d_H-i-s') . '.sql';
                $filepath = '../../backups/' . $filename;
                
                // Criar diretório de backups se não existir
                if (!is_dir('../../backups')) {
                    mkdir('../../backups', 0755, true);
                }
                
                $success = createSelectiveBackup($filepath, $modulos);
                if ($success) {
                    $success = "Backup seletivo criado com sucesso! <a href='../../backups/$filename' class='btn btn-sm btn-outline-primary ms-2'><i class='bi bi-download'></i> Download</a>";
                } else {
                    $error = "Erro ao criar backup seletivo.";
                }
            } catch (Exception $e) {
                $error = "Erro ao criar backup: " . $e->getMessage();
            }
            break;
            
        case 'importar_backup':
            try {
                if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
                    $error = "Erro no upload do arquivo de backup.";
                    break;
                }
                
                $uploadedFile = $_FILES['backup_file']['tmp_name'];
                $success = importBackup($uploadedFile);
                
                if ($success) {
                    $success = "Backup importado com sucesso!";
                } else {
                    $error = "Erro ao importar backup.";
                }
            } catch (Exception $e) {
                $error = "Erro ao importar backup: " . $e->getMessage();
            }
            break;
    }
}

// Listar backups existentes
$backups = [];
if (is_dir('../../backups')) {
    $backupFiles = glob('../../backups/*.sql');
    foreach ($backupFiles as $file) {
        $backups[] = [
            'name' => basename($file),
            'path' => $file,
            'size' => formatBytes(filesize($file)),
            'date' => date('d/m/Y H:i:s', filemtime($file))
        ];
    }
    // Ordenar por data (mais recente primeiro)
    usort($backups, function($a, $b) {
        return filemtime($b['path']) - filemtime($a['path']);
    });
}

// Estatísticas do banco
$stats = [
    'equipamentos' => countRecords('equipamentos'),
    'manutencoes' => countRecords('manutencoes'),
    'assinaturas' => countRecords('assinaturas'),
    'pagamentos' => countRecords('pagamentos'),
    'configuracoes' => countRecords('configuracoes')
];

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
        <i class="bi bi-cloud-download me-2"></i>
        Backup e Importação
    </h2>
</div>

<!-- Estatísticas do banco -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    Estatísticas do Banco de Dados
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-primary"><?php echo $stats['equipamentos']; ?></h4>
                            <small class="text-muted">Equipamentos</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-warning"><?php echo $stats['manutencoes']; ?></h4>
                            <small class="text-muted">Manutenções</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-info"><?php echo $stats['assinaturas']; ?></h4>
                            <small class="text-muted">Assinaturas</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-success"><?php echo $stats['pagamentos']; ?></h4>
                            <small class="text-muted">Pagamentos</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-secondary"><?php echo $stats['configuracoes']; ?></h4>
                            <small class="text-muted">Configurações</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-dark"><?php echo array_sum($stats); ?></h4>
                            <small class="text-muted">Total de Registros</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ações de backup -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-download me-2"></i>
                    Criar Backup
                </h6>
            </div>
            <div class="card-body">
                <!-- Backup Completo -->
                <div class="mb-3">
                    <h6>Backup Completo</h6>
                    <p class="text-muted small">Exporta todos os dados do sistema.</p>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="backup_completo">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-download me-2"></i>
                            Criar Backup Completo
                        </button>
                    </form>
                </div>
                
                <hr>
                
                <!-- Backup Seletivo -->
                <div>
                    <h6>Backup Seletivo</h6>
                    <p class="text-muted small">Exporta apenas os módulos selecionados.</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="backup_seletivo">
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="modulos[]" value="equipamentos" id="mod_equipamentos">
                                <label class="form-check-label" for="mod_equipamentos">
                                    Equipamentos (<?php echo $stats['equipamentos']; ?> registros)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="modulos[]" value="manutencoes" id="mod_manutencoes">
                                <label class="form-check-label" for="mod_manutencoes">
                                    Manutenções (<?php echo $stats['manutencoes']; ?> registros)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="modulos[]" value="assinaturas" id="mod_assinaturas">
                                <label class="form-check-label" for="mod_assinaturas">
                                    Assinaturas (<?php echo $stats['assinaturas']; ?> registros)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="modulos[]" value="pagamentos" id="mod_pagamentos">
                                <label class="form-check-label" for="mod_pagamentos">
                                    Pagamentos (<?php echo $stats['pagamentos']; ?> registros)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="modulos[]" value="configuracoes" id="mod_configuracoes">
                                <label class="form-check-label" for="mod_configuracoes">
                                    Configurações (<?php echo $stats['configuracoes']; ?> registros)
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-cloud-download me-2"></i>
                            Criar Backup Seletivo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-upload me-2"></i>
                    Importar Dados
                </h6>
            </div>
            <div class="card-body">
                <!-- Importar Backup -->
                <div class="mb-3">
                    <h6>Restaurar Backup</h6>
                    <p class="text-muted small">Importa dados de um arquivo de backup.</p>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="importar_backup">
                        
                        <div class="mb-3">
                            <label for="backup_file" class="form-label">Arquivo de Backup (.sql)</label>
                            <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql" required>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Atenção:</strong> A importação pode sobrescrever dados existentes. Faça backup antes de prosseguir.
                        </div>
                        
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Tem certeza? Esta ação pode sobrescrever dados existentes.')">
                            <i class="bi bi-upload me-2"></i>
                            Importar Backup
                        </button>
                    </form>
                </div>
                
                <hr>
                
                <!-- Importação Rápida -->
                <div>
                    <h6>Importação Rápida</h6>
                    <p class="text-muted small">Importa equipamentos e assinaturas via CSV.</p>
                    <div class="d-grid gap-2">
                        <a href="import_equipamentos.php" class="btn btn-outline-success">
                            <i class="bi bi-laptop me-2"></i>
                            Importar Equipamentos (CSV)
                        </a>
                        <a href="import_assinaturas.php" class="btn btn-outline-info">
                            <i class="bi bi-file-text me-2"></i>
                            Importar Assinaturas (CSV)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de backups existentes -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="bi bi-archive me-2"></i>
            Backups Existentes
        </h6>
    </div>
    <div class="card-body">
        <?php if (empty($backups)): ?>
            <div class="text-center py-4">
                <i class="bi bi-archive display-4 text-muted"></i>
                <p class="text-muted mt-3">Nenhum backup encontrado</p>
                <p class="text-muted small">Crie seu primeiro backup usando as opções acima.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome do Arquivo</th>
                            <th>Tamanho</th>
                            <th>Data de Criação</th>
                            <th width="100">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-file-earmark-zip me-2"></i>
                                    <?php echo htmlspecialchars($backup['name']); ?>
                                </td>
                                <td><?php echo $backup['size']; ?></td>
                                <td><?php echo $backup['date']; ?></td>
                                <td>
                                    <a href="<?php echo $backup['path']; ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Download">
                                        <i class="bi bi-download"></i>
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

<?php include '../../templates/footer.php'; ?>

