<?php
/**
 * Sistema Ativus - Inicialização/Atualização do Banco de Dados
 * Este script garante que todas as tabelas e colunas necessárias estejam presentes.
 */

// Configurações
$dbPath = __DIR__ . 
'/db/database.sqlite';
$dbDir = dirname($dbPath);

// Criar diretório db se não existir
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

$message = '';
$details = '';

try {
    // Conectar ao SQLite
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Habilitar suporte a FOREIGN KEY no SQLite
    $pdo->exec('PRAGMA foreign_keys = ON;');

    // Função auxiliar para verificar se uma coluna existe
    function columnExists($pdo, $tableName, $columnName) {
        $stmt = $pdo->prepare("PRAGMA table_info($tableName);");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            if ($col['name'] === $columnName) {
                return true;
            }
        }
        return false;
    }

    // 1. Criar tabela de fornecedores (se não existir)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fornecedores (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome VARCHAR(255) NOT NULL UNIQUE,
            contato VARCHAR(255),
            telefone VARCHAR(50),
            email VARCHAR(255),
            endereco TEXT,
            observacoes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // 2. Adicionar coluna fornecedor_id na tabela equipamentos (se não existir)
    if (!columnExists($pdo, 'equipamentos', 'fornecedor_id')) {
        $pdo->exec("ALTER TABLE equipamentos ADD COLUMN fornecedor_id INTEGER NULL;");
    }

    // 3. Adicionar coluna fornecedor_id na tabela manutencoes (se não existir)
    if (!columnExists($pdo, 'manutencoes', 'fornecedor_id')) {
        $pdo->exec("ALTER TABLE manutencoes ADD COLUMN fornecedor_id INTEGER NULL;");
    }

    // 4. Criar tabela de configurações do sistema (se não existir)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS configuracoes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            categoria VARCHAR(100) NOT NULL,
            chave VARCHAR(100) NOT NULL UNIQUE,
            descricao VARCHAR(255) NOT NULL,
            tipo TEXT DEFAULT 'text', -- SQLite não tem ENUM, usar TEXT
            valor TEXT,
            opcoes TEXT,
            obrigatorio BOOLEAN DEFAULT 0, -- 0 para FALSE, 1 para TRUE
            ativo BOOLEAN DEFAULT 1, -- 0 para FALSE, 1 para TRUE
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // 5. Criar tabela de anexos para assinaturas (se não existir)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS anexos_assinaturas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            assinatura_id INTEGER NOT NULL,
            nome_original VARCHAR(255) NOT NULL,
            nome_arquivo VARCHAR(255) NOT NULL,
            tipo_arquivo VARCHAR(100) NOT NULL,
            tamanho INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id) ON DELETE CASCADE
        );
    ");

    // 6. Inserir configurações padrão do sistema (usando INSERT OR IGNORE)
    $pdo->exec("INSERT OR IGNORE INTO configuracoes (categoria, chave, descricao, tipo, valor, opcoes) VALUES
    ('equipamentos', 'tipos_equipamento', 'Tipos de equipamentos disponíveis', 'select', 'notebook', 'notebook,desktop,celular,impressora,tablet,outro'),
    ('equipamentos', 'status_equipamento', 'Status possíveis para equipamentos', 'select', 'ativo', 'ativo,em_manutencao,descartado'),
    ('sistema', 'prazo_garantia_padrao', 'Prazo padrão de garantia em meses', 'number', '36', ''),
    ('sistema', 'backup_automatico', 'Ativar backup automático', 'boolean', '1', ''),
    ('manutencoes', 'tipos_manutencao', 'Tipos de manutenção disponíveis', 'select', 'preventiva', 'preventiva,corretiva,preditiva'),
    ('manutencoes', 'status_manutencao', 'Status possíveis para manutenções', 'select', 'agendada', 'agendada,em_andamento,concluida,cancelada'),
    ('assinaturas', 'tipos_assinatura', 'Tipos de assinatura disponíveis', 'select', 'software', 'software,servicos,suporte,hospedagem'),
    ('assinaturas', 'status_assinatura', 'Status possíveis para assinaturas', 'select', 'ativa', 'ativa,vencendo,vencida,cancelada'),
    ('sistema', 'empresa_nome', 'Nome da empresa', 'text', 'Sua Empresa', ''),
    ('sistema', 'empresa_cnpj', 'CNPJ da empresa', 'text', '', ''),
    ('sistema', 'observacoes_padrao_equipamento', 'Observações padrão para equipamentos', 'textarea', 'Equipamento em perfeito estado de funcionamento.', ''),
    ('sistema', 'observacoes_padrao_manutencao', 'Observações padrão para manutenções', 'textarea', 'Manutenção realizada conforme procedimento padrão.', '');");

    // 7. Atualizar versão do sistema (usando REPLACE INTO para SQLite)
    $pdo->exec("REPLACE INTO configuracoes (categoria, chave, descricao, tipo, valor) VALUES
    ('sistema', 'versao_sistema', 'Versão atual do sistema', 'text', '2.3');");
    
    $message = "✅ Banco de dados atualizado com sucesso!";
    $details = "
    - Arquivo do banco: " . $dbPath . "
    - Tabelas e colunas verificadas/criadas: fornecedores, configuracoes, anexos_assinaturas, fornecedor_id em equipamentos e manutencoes.
    - Sistema pronto para uso!
    ";
    
} catch (PDOException $e) {
    $message = "❌ Erro ao atualizar o banco de dados: " . $e->getMessage();
    $details = "";
} catch (Exception $e) {
    $message = "❌ Erro: " . $e->getMessage();
    $details = "";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicialização/Atualização do Banco de Dados - Sistema Ativus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2d5016 0%, #4a7c59 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 2rem;
            max-width: 600px;
            width: 100%;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h1 {
            color: #2d5016;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .logo p {
            color: #6c757d;
            margin: 0;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .details {
            background: #f8f9fa;
            border-left: 4px solid #2d5016;
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 0 5px 5px 0;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="logo">
            <h1>🚀 ATIVUS</h1>
            <p>Sistema de Gestão Empresarial</p>
        </div>
        
        <div class="text-center">
            <h3 class="<?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </h3>
            
            <?php if ($details): ?>
            <div class="details">
                <pre><?php echo $details; ?></pre>
            </div>
            <?php endif; ?>
            
            <?php if (strpos($message, '✅') !== false): ?>
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary btn-lg">
                    🏠 Ir para o Dashboard
                </a>
            </div>
            <?php else: ?>
            <div class="mt-4">
                <button onclick="location.reload()" class="btn btn-warning btn-lg">
                    🔄 Tentar Novamente
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


