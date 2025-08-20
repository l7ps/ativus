<?php
/**
 * Sistema Ativus - Setup do Banco de Dados
 * Este arquivo cria o banco de dados SQLite e todas as tabelas necessárias
 */

// Configurações
$dbPath = __DIR__ . 
'/db/database.sqlite';
$dbDir = dirname($dbPath);

// Criar diretório db se não existir
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

try {
    // Conectar ao SQLite
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL para criar as tabelas
    $sql = "
    -- Tabela de Assinaturas/Serviços
    CREATE TABLE IF NOT EXISTS assinaturas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_servico VARCHAR(255) NOT NULL,
        tipo VARCHAR(20) NOT NULL DEFAULT 'mensal',
        responsavel VARCHAR(255),
        valor_padrao DECIMAL(10,2),
        data_inicio DATE,
        data_vencimento DATE,
        status VARCHAR(20) NOT NULL DEFAULT 'ativo',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Tabela de Pagamentos/Recargas
    CREATE TABLE IF NOT EXISTS pagamentos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        assinatura_id INTEGER,
        data_pagamento DATE NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        forma_pagamento VARCHAR(20) NOT NULL,
        comprovante VARCHAR(255),
        observacoes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id) ON DELETE SET NULL
    );

    -- Tabela de Fornecedores
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

    -- Tabela de Equipamentos
    CREATE TABLE IF NOT EXISTS equipamentos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo_interno VARCHAR(100) UNIQUE,
        tipo VARCHAR(20) NOT NULL,
        marca_modelo VARCHAR(255),
        numero_serie VARCHAR(255),
        responsavel VARCHAR(255),
        setor_sala VARCHAR(255),
        data_aquisicao DATE,
        garantia_ate DATE,
        valor_aquisicao DECIMAL(10,2),
        status VARCHAR(20) NOT NULL DEFAULT 'ativo',
        toner_info TEXT,
        is_datacard INTEGER DEFAULT 0,
        location TEXT,
        fornecedor_id INTEGER, -- Alterado para fornecedor_id
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL
    );

    -- Tabela de Manutenções
    CREATE TABLE IF NOT EXISTS manutencoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        equipamento_id INTEGER NOT NULL,
        data_manutencao DATE NOT NULL,
        tipo VARCHAR(20) NOT NULL,
        custo DECIMAL(10,2),
        observacoes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id) ON DELETE CASCADE
    );

    -- Tabela de Configurações do Sistema
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

    -- Tabela de anexos para assinaturas
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

    -- Índices para melhor performance
    CREATE INDEX IF NOT EXISTS idx_assinaturas_status ON assinaturas(status);
    CREATE INDEX IF NOT EXISTS idx_assinaturas_vencimento ON assinaturas(data_vencimento);
    CREATE INDEX IF NOT EXISTS idx_pagamentos_data ON pagamentos(data_pagamento);
    CREATE INDEX IF NOT EXISTS idx_equipamentos_status ON equipamentos(status);
    CREATE INDEX IF NOT EXISTS idx_manutencoes_data ON manutencoes(data_manutencao);
    ";

    // Executar o SQL
    $pdo->exec($sql);
    
    // Inserir configurações padrão do sistema
    $insertConfigs = "
    INSERT OR IGNORE INTO configuracoes (categoria, chave, descricao, tipo, valor, opcoes) VALUES
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
    ('sistema', 'observacoes_padrao_manutencao', 'Observações padrão para manutenções', 'textarea', 'Manutenção realizada conforme procedimento padrão.', '');
    
    REPLACE INTO configuracoes (categoria, chave, descricao, tipo, valor) VALUES
    ('sistema', 'versao_sistema', 'Versão atual do sistema', 'text', '2.0');
    ";
    $pdo->exec($insertConfigs);

    // Inserir dados de exemplo (opcional)
    $insertExampleData = "
    -- Dados de exemplo para Assinaturas
    INSERT OR IGNORE INTO assinaturas (id, nome_servico, tipo, responsavel, valor_padrao, data_inicio, data_vencimento, status) VALUES
    (1, 'Office 365 Business', 'mensal', 'TI', 45.00, '2024-01-01', '2024-12-31', 'ativo'),
    (2, 'Canva Pro', 'anual', 'Marketing', 119.99, '2024-01-15', '2025-01-15', 'ativo'),
    (3, 'WhatsApp Business API', 'mensal', 'Vendas', 89.90, '2024-02-01', '2024-12-31', 'ativo');

    -- Dados de exemplo para Fornecedores
    INSERT OR IGNORE INTO fornecedores (id, nome, contato, telefone, email) VALUES
    (1, 'Dell Brasil', 'Carlos Mendes', '(11) 98765-4321', 'carlos.mendes@dell.com'),
    (2, 'HP Inc.', 'Ana Paula', '(21) 99876-5432', 'ana.paula@hp.com'),
    (3, 'Canon do Brasil', 'Roberto Silva', '(31) 97654-3210', 'roberto.silva@canon.com');

    -- Dados de exemplo para Equipamentos
    INSERT OR IGNORE INTO equipamentos (id, codigo_interno, tipo, marca_modelo, numero_serie, responsavel, setor_sala, data_aquisicao, garantia_ate, valor_aquisicao, status, fornecedor_id) VALUES
    (1, 'NB001', 'notebook', 'Dell Inspiron 15', 'DL123456789', 'João Silva', 'TI - Sala 101', '2023-06-15', '2026-06-15', 2500.00, 'ativo', 1),
    (2, 'DT001', 'desktop', 'HP EliteDesk 800', 'HP987654321', 'Maria Santos', 'Financeiro - Sala 205', '2023-08-20', '2026-08-20', 1800.00, 'ativo', 2),
    (3, 'IMP001', 'impressora', 'Canon PIXMA G6020', 'CN456789123', 'Recepção', 'Térreo - Recepção', '2023-09-10', '2025-09-10', 899.99, 'ativo', 3);

    -- Dados de exemplo para Pagamentos
    INSERT OR IGNORE INTO pagamentos (id, assinatura_id, data_pagamento, valor, forma_pagamento, observacoes) VALUES
    (1, 1, '2024-01-05', 45.00, 'cartao', 'Pagamento automático'),
    (2, 1, '2024-02-05', 45.00, 'cartao', 'Pagamento automático'),
    (3, 2, '2024-01-15', 119.99, 'boleto', 'Pagamento anual'),
    (4, 3, '2024-02-01', 89.90, 'pix', 'Primeiro pagamento');

    -- Dados de exemplo para Manutenções
    INSERT OR IGNORE INTO manutencoes (id, equipamento_id, data_manutencao, tipo, custo, observacoes) VALUES
    (1, 1, '2024-01-20', 'preventiva', 0.00, 'Limpeza e atualização de drivers'),
    (2, 3, '2024-02-10', 'corretiva', 150.00, 'Troca de cartucho e limpeza dos cabeçotes');
    ";
    
    $pdo->exec($insertExampleData);
    
    $message = "✅ Banco de dados criado com sucesso!";
    $details = "
    - Arquivo do banco: " . $dbPath . "
    - Tabelas criadas: assinaturas, pagamentos, equipamentos, manutencoes, configuracoes, anexos_assinaturas, fornecedores
    - Dados de exemplo e configurações padrão inseridos
    - Sistema pronto para uso!
    ";
    
} catch (PDOException $e) {
    $message = "❌ Erro ao criar o banco de dados: " . $e->getMessage();
    $details = "";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Sistema Ativus</title>
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


