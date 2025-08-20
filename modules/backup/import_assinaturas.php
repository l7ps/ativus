<?php
/**
 * Sistema Ativus - Importação Rápida de Assinaturas
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Importar Assinaturas';
$basePath = '../../';

// Processar importação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        try {
            $csvFile = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($csvFile, 'r');
            
            if ($handle === false) {
                throw new Exception('Não foi possível abrir o arquivo CSV.');
            }
            
            $importados = 0;
            $erros = [];
            $linha = 0;
            
            // Pular cabeçalho se existir
            if (isset($_POST['tem_cabecalho'])) {
                fgetcsv($handle, 1000, ',');
            }
            
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $linha++;
                
                // Verificar se tem dados suficientes (mínimo para nome_servico, tipo, data_inicio)
                if (count($data) < 3) {
                    $erros[] = "Linha $linha: Dados insuficientes. Verifique o formato do CSV.";
                    continue;
                }
                
                // Mapear dados do CSV para o array de assinatura
                // Ordem das colunas no CSV: nome_servico,tipo,data_inicio,data_fim,valor_mensal,status,observacoes
                $assinatura = [
                    'nome_servico' => trim($data[0] ?? ''),
                    'tipo' => trim($data[1] ?? 'software'),
                    'data_inicio' => !empty($data[2]) ? formatDateDB($data[2]) : null,
                    'data_fim' => !empty($data[3]) ? formatDateDB($data[3]) : null,
                    'valor_mensal' => !empty($data[4]) ? parseMoney($data[4]) : 0,
                    'status' => trim($data[5] ?? 'ativa'),
                    'observacoes' => trim($data[6] ?? '')
                ];
                
                // Validações básicas
                if (empty($assinatura['nome_servico'])) {
                    $erros[] = "Linha $linha: Nome do serviço é obrigatório.";
                    continue;
                }
                
                // Verificar se assinatura já existe (considerando nome_servico e tipo como chave)
                $existingService = executeQuery("SELECT id FROM assinaturas WHERE nome_servico = ? AND tipo = ?", [$assinatura['nome_servico'], $assinatura['tipo']]);
                if (!empty($existingService)) {
                    if (isset($_POST['atualizar_existentes'])) {
                        // Atualizar assinatura existente
                        $sql = "UPDATE assinaturas SET 
                                tipo = ?, data_inicio = ?, data_fim = ?, valor_mensal = ?, 
                                status = ?, observacoes = ?, updated_at = CURRENT_TIMESTAMP 
                                WHERE nome_servico = ? AND tipo = ?";
                        $params = [
                            $assinatura['tipo'], $assinatura['data_inicio'], $assinatura['data_fim'],
                            $assinatura['valor_mensal'], $assinatura['status'], $assinatura['observacoes'],
                            $assinatura['nome_servico'], $assinatura['tipo']
                        ];
                        executeStatement($sql, $params);
                        $importados++;
                    } else {
                        $erros[] = "Linha $linha: Assinatura '{$assinatura['nome_servico']}' do tipo '{$assinatura['tipo']}' já existe. Para atualizar, marque a opção 'Atualizar assinaturas existentes'.";
                        continue;
                    }
                } else {
                    // Inserir nova assinatura
                    $sql = "INSERT INTO assinaturas (nome_servico, tipo, data_inicio, data_fim, valor_mensal, status, observacoes) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $params = array_values($assinatura);
                    executeStatement($sql, $params);
                    $importados++;
                }
            }
            
            fclose($handle);
            
            $success = "Importação concluída! $importados assinaturas importadas.";
            if (!empty($erros)) {
                $error = "Alguns erros ocorreram durante a importação:\n" . implode("\n", array_slice($erros, 0, 10));
                if (count($erros) > 10) {
                    $error .= "\n... e mais " . (count($erros) - 10) . " erros.";
                }
            }
            
        } catch (Exception $e) {
            $error = "Erro na importação: " . $e->getMessage();
        }
    } else {
        $error = "Erro no upload do arquivo CSV.";
    }
}

include '../../templates/header.php';
?>

<?php if (isset($success)): ?>
    <?php echo showAlert($success, 'success'); ?>
<?php endif; ?>

<?php if (isset($error)): ?>
    <?php echo showAlert(nl2br(htmlspecialchars($error)), 'danger'); ?>
<?php endif; ?>

<!-- Cabeçalho da página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-file-text me-2"></i>
        Importar Assinaturas
    </h2>
    <a href="../backup/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>
        Voltar
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-upload me-2"></i>
                    Upload do Arquivo CSV
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Arquivo CSV *</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                        <div class="form-text">Selecione um arquivo CSV com os dados das assinaturas.</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tem_cabecalho" name="tem_cabecalho" checked>
                            <label class="form-check-label" for="tem_cabecalho">
                                Arquivo possui cabeçalho (primeira linha)
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="atualizar_existentes" name="atualizar_existentes">
                            <label class="form-check-label" for="atualizar_existentes">
                                Atualizar assinaturas existentes (mesmo nome e tipo)
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Importante:</strong> Verifique se o arquivo está no formato correto antes de importar. 
                        Faça backup dos dados antes de prosseguir.
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i>
                        Importar Assinaturas
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Formato do Arquivo CSV
                </h6>
            </div>
            <div class="card-body">
                <p class="small">O arquivo CSV deve conter as seguintes colunas na ordem:</p>
                <ol class="small">
                    <li><strong>Nome do Serviço</strong> (obrigatório)</li>
                    <li><strong>Tipo</strong> (software, servicos, suporte, hospedagem)</li>
                    <li>Data de Início (AAAA-MM-DD)</li>
                    <li>Data de Fim (AAAA-MM-DD)</li>
                    <li>Valor Mensal (ex: 150.00)</li>
                    <li>Status (ativa, vencendo, vencida, cancelada)</li>
                    <li>Observações</li>
                </ol>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-download me-2"></i>
                    Modelo de Arquivo
                </h6>
            </div>
            <div class="card-body">
                <p class="small">Baixe um modelo de arquivo CSV para facilitar a importação:</p>
                <a href="modelo_importacao_assinaturas.csv" class="btn btn-outline-primary btn-sm" download>
                    <i class="bi bi-download me-2"></i>
                    Baixar Modelo CSV
                </a>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Dicas Importantes
                </h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Use vírgula (,) como separador</li>
                    <li>Datas no formato AAAA-MM-DD</li>
                    <li>Valores monetários com ponto decimal (ex: 123.45)</li>
                    <li>Combinação nome do serviço + tipo deve ser única</li>
                    <li>Tipos válidos: software, servicos, suporte, hospedagem</li>
                    <li>Status válidos: ativa, vencendo, vencida, cancelada</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>


