-- Sistema Ativus - Atualizações v2.0 (SQLite Compatible)
-- Execute este script para atualizar o banco de dados com as novas funcionalidades

-- Criar tabela de fornecedores (se não existir)
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

-- Adicionar coluna fornecedor_id na tabela equipamentos (se não existir ou se for NOT NULL)
-- Se a coluna já existe e é NOT NULL, esta alteração pode exigir recriação da tabela em SQLite.
-- Para compatibilidade, vamos tentar adicionar como NULL e, se já existir, assumir que a aplicação gerencia.
ALTER TABLE equipamentos ADD COLUMN fornecedor_id INTEGER NULL;

-- Adicionar coluna fornecedor_id na tabela manutencoes (se não existir ou se for NOT NULL)
ALTER TABLE manutencoes ADD COLUMN fornecedor_id INTEGER NULL;

-- Criar tabela de configurações do sistema (se não existir)
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

-- Criar tabela de anexos para assinaturas (se não existir)
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

-- Inserir configurações padrão do sistema
-- Para SQLite, ON DUPLICATE KEY UPDATE não existe. Usar INSERT OR IGNORE ou lógica de UPDATE/INSERT separada.
-- Para simplificar, vamos usar INSERT OR IGNORE para evitar erros se já existirem.
INSERT OR IGNORE INTO configuracoes (categoria, chave, descricao, tipo, valor, opcoes) VALUES
("equipamentos", "tipos_equipamento", "Tipos de equipamentos disponíveis", "select", "notebook", "notebook,desktop,celular,impressora,tablet,outro"),
("equipamentos", "status_equipamento", "Status possíveis para equipamentos", "select", "ativo", "ativo,em_manutencao,descartado"),
("sistema", "prazo_garantia_padrao", "Prazo padrão de garantia em meses", "number", "36", ""),
("sistema", "backup_automatico", "Ativar backup automático", "boolean", "1", ""),
("manutencoes", "tipos_manutencao", "Tipos de manutenção disponíveis", "select", "preventiva", "preventiva,corretiva,preditiva"),
("manutencoes", "status_manutencao", "Status possíveis para manutenções", "select", "agendada", "agendada,em_andamento,concluida,cancelada"),
("assinaturas", "tipos_assinatura", "Tipos de assinatura disponíveis", "select", "software", "software,servicos,suporte,hospedagem"),
("assinaturas", "status_assinatura", "Status possíveis para assinaturas", "select", "ativa", "ativa,vencendo,vencida,cancelada"),
("sistema", "empresa_nome", "Nome da empresa", "text", "Sua Empresa", ""),
("sistema", "empresa_cnpj", "CNPJ da empresa", "text", "", ""),
("sistema", "observacoes_padrao_equipamento", "Observações padrão para equipamentos", "textarea", "Equipamento em perfeito estado de funcionamento.", ""),
("sistema", "observacoes_padrao_manutencao", "Observações padrão para manutenções", "textarea", "Manutenção realizada conforme procedimento padrão.", "");

-- Atualizar versão do sistema (usando REPLACE INTO para SQLite)
-- Isso irá inserir ou substituir a linha com a chave 'versao_sistema'
REPLACE INTO configuracoes (categoria, chave, descricao, tipo, valor) VALUES
("sistema", "versao_sistema", "Versão atual do sistema", "text", "2.1");

-- Comentários sobre as melhorias implementadas:
-- 1. Módulo de fornecedores dedicado
-- 2. Campo fornecedor_id adicionado aos equipamentos para vincular a fornecedores
-- 3. Campo fornecedor_id adicionado às manutenções para vincular a fornecedores
-- 4. Sistema de configurações flexível para personalizar listas e opções
-- 5. Sistema de anexos funcional para assinaturas com upload e visualização
-- 6. Módulo de backup completo com exportação e importação
-- 7. Importação rápida de equipamentos e assinaturas via CSV
-- 8. Central de ajuda completa com orientações do sistema
-- 9. Correção do erro de sintaxe no form.php de equipamentos



