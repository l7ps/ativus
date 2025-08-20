<?php
/**
 * Sistema Ativus - Importação Rápida de Equipamentos
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Importar Equipamentos';
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
                
                // Verificar se tem dados suficientes (mínimo para código, tipo, marca_modelo)
                if (count($data) < 3) {
                    $erros[] = "Linha $linha: Dados insuficientes. Verifique o formato do CSV.";
                    continue;
                }
                
                // Mapear dados do CSV para o array de equipamento
                // Ordem das colunas no CSV: codigo_interno,tipo,marca_modelo,numero_serie,responsavel,setor_sala,data_aquisicao,garantia_ate,valor_aquisicao,status,toner_info,is_datacard,location,fornecedor_nome
                $equipamento = [
                    'codigo_interno' => trim($data[0] ?? ''),
                    'tipo' => trim($data[1] ?? 'outro'),
                    'marca_modelo' => trim($data[2] ?? ''),
                    'numero_serie' => trim($data[3] ?? ''),
                    'responsavel' => trim($data[4] ?? ''),
                    'setor_sala' => trim($data[5] ?? ''),
                    'data_aquisicao' => !empty($data[6]) ? formatDateDB($data[6]) : null,
                    'garantia_ate' => !empty($data[7]) ? formatDateDB($data[7]) : null,
                    'valor_aquisicao' => !empty($data[8]) ? parseMoney($data[8]) : 0,
                    'status' => trim($data[9] ?? 'ativo'),
                    'toner_info' => trim($data[10] ?? ''),
                    'is_datacard' => intval($data[11] ?? 0), // Já é 0 ou 1 no CSV
                    'location' => trim($data[12] ?? ''),
                    'fornecedor_nome' => trim($data[13] ?? '') // Nome do fornecedor
                ];
                
                // Validações básicas
                if (empty($equipamento['codigo_interno'])) {
                    $erros[] = "Linha $linha: Código interno é obrigatório.";
                    continue;
                }
                
                if (empty($equipamento['marca_modelo'])) {
                    $erros[] = "Linha $linha: Marca/modelo é obrigatório.";
                    continue;
                }

                // Tratar fornecedor_id
                $fornecedorId = null;
                if (!empty($equipamento['fornecedor_nome'])) {
                    // Tentar encontrar o fornecedor existente
                    $fornecedor = executeQuery("SELECT id FROM fornecedores WHERE nome = ?", [$equipamento['fornecedor_nome']]);
                    if (!empty($fornecedor)) {
                        $fornecedorId = $fornecedor[0]['id'];
                    } else {
                        // Se não existir, criar novo fornecedor
                        executeStatement("INSERT INTO fornecedores (nome) VALUES (?) ", [$equipamento['fornecedor_nome']]);
                        $fornecedorId = getLastInsertId();
                    }
                }

                // Preparar dados para inserção/atualização no banco de dados
                $dadosDb = [
                    'codigo_interno' => $equipamento['codigo_interno'],
                    'tipo' => $equipamento['tipo'],
                    'marca_modelo' => $equipamento['marca_modelo'],
                    'numero_serie' => $equipamento['numero_serie'],
                    'responsavel' => $equipamento['responsavel'],
                    'setor_sala' => $equipamento['setor_sala'],
                    'data_aquisicao' => $equipamento['data_aquisicao'],
                    'garantia_ate' => $equipamento['garantia_ate'],
                    'valor_aquisicao' => $equipamento['valor_aquisicao'],
                    'status' => $equipamento['status'],
                    'toner_info' => $equipamento['toner_info'],
                    'is_datacard' => $equipamento['is_datacard'],
                    'location' => $equipamento['location'],
                    'fornecedor_id' => $fornecedorId
                ];
                
                // Verificar se código já existe
                $existingCode = executeQuery("SELECT id FROM equipamentos WHERE codigo_interno = ?", [$equipamento['codigo_interno']]);
                if (!empty($existingCode)) {
                    if (isset($_POST['atualizar_existentes'])) {
                        // Atualizar equipamento existente
                        $sql = "UPDATE equipamentos SET 
                                tipo = ?, marca_modelo = ?, numero_serie = ?, responsavel = ?, setor_sala = ?, 
                                data_aquisicao = ?, garantia_ate = ?, valor_aquisicao = ?, status = ?, 
                                toner_info = ?, is_datacard = ?, location = ?, fornecedor_id = ?, updated_at = CURRENT_TIMESTAMP 
                                WHERE codigo_interno = ?";
                        $params = [
                            $dadosDb['tipo'], $dadosDb['marca_modelo'], $dadosDb['numero_serie'],
                            $dadosDb['responsavel'], $dadosDb['setor_sala'], $dadosDb['data_aquisicao'],
                            $dadosDb['garantia_ate'], $dadosDb['valor_aquisicao'], $dadosDb['status'],
                            $dadosDb['toner_info'], $dadosDb['is_datacard'], $dadosDb['location'],
                            $dadosDb['fornecedor_id'], $dadosDb['codigo_interno']
                        ];
                        executeStatement($sql, $params);
                        $importados++;
                    } else {
                        $erros[] = "Linha $linha: Código '{$equipamento['codigo_interno']}' já existe. Para atualizar, marque a opção 'Atualizar equipamentos existentes'.";
                        continue;
                    }
                } else {
                    // Inserir novo equipamento
                    $sql = "INSERT INTO equipamentos (codigo_interno, tipo, marca_modelo, numero_serie, responsavel, setor_sala, data_aquisicao, garantia_ate, valor_aquisicao, status, toner_info, is_datacard, location, fornecedor_id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = array_values($dadosDb);
                    executeStatement($sql, $params);
                    $importados++;
                }
            }
            
            fclose($handle);
            
            $success = "Importação concluída! $importados equipamentos importados.";
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
        <i class="bi bi-laptop me-2"></i>
        Importar Equipamentos
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
                        <div class="form-text">Selecione um arquivo CSV com os dados dos equipamentos.</div>
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
                                Atualizar equipamentos existentes (mesmo código interno)
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
                        Importar Equipamentos
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
                    <li><strong>Código Interno</strong> (obrigatório)</li>
                    <li><strong>Tipo</strong> (notebook, desktop, impressora, etc.)</li>
                    <li><strong>Marca/Modelo</strong> (obrigatório)</li>
                    <li>Número de Série</li>
                    <li>Responsável</li>
                    <li>Setor/Sala</li>
                    <li>Data de Aquisição (AAAA-MM-DD)</li>
                    <li>Garantia Até (AAAA-MM-DD)</li>
                    <li>Valor de Aquisição (ex: 2500.00)</li>
                    <li>Status (ativo, em_manutencao, descartado)</li>
                    <li>Informações do Toner</li>
                    <li>É Datacard? (0 ou 1)</li>
                    <li>Localização</li>
                    <li><strong>Nome do Fornecedor</strong> (será criado se não existir)</li>
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
                <a href="modelo_importacao_equipamentos.csv" class="btn btn-outline-primary btn-sm" download>
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
                    <li>Códigos internos devem ser únicos</li>
                    <li>Tipos válidos: notebook, desktop, celular, impressora, tablet, outro</li>
                    <li>Status válidos: ativo, em_manutencao, descartado</li>
                    <li>Para 'É Datacard?', use 1 para sim e 0 para não.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>


