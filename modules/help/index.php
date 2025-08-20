<?php
/**
 * Sistema Ativus - Central de Ajuda
 */

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pageTitle = 'Central de Ajuda';
$basePath = '../../';

include '../../templates/header.php';
?>

<!-- Cabeçalho da página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-question-circle me-2"></i>
        Central de Ajuda
    </h2>
</div>

<!-- Menu de navegação da ajuda -->
<div class="row mb-4">
    <div class="col-12">
        <nav class="nav nav-pills nav-fill">
            <a class="nav-link active" href="#inicio" data-bs-toggle="pill">
                <i class="bi bi-house me-2"></i>Início
            </a>
            <a class="nav-link" href="#equipamentos" data-bs-toggle="pill">
                <i class="bi bi-laptop me-2"></i>Equipamentos
            </a>
            <a class="nav-link" href="#manutencoes" data-bs-toggle="pill">
                <i class="bi bi-tools me-2"></i>Manutenções
            </a>
            <a class="nav-link" href="#assinaturas" data-bs-toggle="pill">
                <i class="bi bi-file-text me-2"></i>Assinaturas
            </a>
            <a class="nav-link" href="#configuracoes" data-bs-toggle="pill">
                <i class="bi bi-gear me-2"></i>Configurações
            </a>
            <a class="nav-link" href="#backup" data-bs-toggle="pill">
                <i class="bi bi-cloud-download me-2"></i>Backup
            </a>
        </nav>
    </div>
</div>

<!-- Conteúdo da ajuda -->
<div class="tab-content">
    <!-- Início -->
    <div class="tab-pane fade show active" id="inicio">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Bem-vindo ao Sistema Ativus
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="lead">O Sistema Ativus é uma solução completa para gerenciamento de equipamentos, manutenções, assinaturas e pagamentos.</p>
                        
                        <h6 class="mt-4">Principais Funcionalidades:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <strong>Gestão de Equipamentos</strong><br>
                                        <small class="text-muted">Cadastro e controle de notebooks, desktops, impressoras e outros equipamentos</small>
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <strong>Controle de Manutenções</strong><br>
                                        <small class="text-muted">Agendamento e acompanhamento de manutenções preventivas e corretivas</small>
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <strong>Gestão de Assinaturas</strong><br>
                                        <small class="text-muted">Controle de assinaturas de software e serviços com anexos de comprovantes</small>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <strong>Controle de Pagamentos</strong><br>
                                        <small class="text-muted">Acompanhamento de pagamentos e vencimentos</small>
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <strong>Relatórios Gerenciais</strong><br>
                                        <small class="text-muted">Relatórios detalhados sobre equipamentos, custos e manutenções</small>
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <strong>Configurações Flexíveis</strong><br>
                                        <small class="text-muted">Sistema de configurações personalizáveis para cada necessidade</small>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-rocket me-2"></i>
                            Primeiros Passos
                        </h6>
                    </div>
                    <div class="card-body">
                        <ol class="list-unstyled">
                            <li class="mb-3">
                                <span class="badge bg-primary rounded-pill me-2">1</span>
                                <strong>Configure o Sistema</strong><br>
                                <small class="text-muted">Acesse Configurações para definir tipos de equipamentos, status e outras opções</small>
                            </li>
                            <li class="mb-3">
                                <span class="badge bg-primary rounded-pill me-2">2</span>
                                <strong>Cadastre Equipamentos</strong><br>
                                <small class="text-muted">Registre seus equipamentos com informações completas</small>
                            </li>
                            <li class="mb-3">
                                <span class="badge bg-primary rounded-pill me-2">3</span>
                                <strong>Agende Manutenções</strong><br>
                                <small class="text-muted">Programe manutenções preventivas e registre as corretivas</small>
                            </li>
                            <li class="mb-0">
                                <span class="badge bg-primary rounded-pill me-2">4</span>
                                <strong>Gerencie Assinaturas</strong><br>
                                <small class="text-muted">Controle suas assinaturas de software e serviços</small>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipamentos -->
    <div class="tab-pane fade" id="equipamentos">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-laptop me-2"></i>
                    Gestão de Equipamentos
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Como Cadastrar um Equipamento:</h6>
                        <ol>
                            <li>Acesse o menu <strong>Equipamentos</strong></li>
                            <li>Clique em <strong>Novo Equipamento</strong></li>
                            <li>Preencha os campos obrigatórios:
                                <ul>
                                    <li>Código Interno (gerado automaticamente)</li>
                                    <li>Tipo (notebook, desktop, impressora, etc.)</li>
                                    <li>Marca/Modelo</li>
                                    <li>Status (ativo, em manutenção, descartado)</li>
                                </ul>
                            </li>
                            <li>Preencha informações adicionais como responsável, setor, fornecedor</li>
                            <li>Para impressoras, informe dados específicos como toner e localização</li>
                            <li>Clique em <strong>Cadastrar</strong></li>
                        </ol>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Tipos de Equipamento:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Descrição</th>
                                        <th>Campos Específicos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-primary">Notebook</span></td>
                                        <td>Computadores portáteis</td>
                                        <td>-</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-info">Desktop</span></td>
                                        <td>Computadores de mesa</td>
                                        <td>-</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">Impressora</span></td>
                                        <td>Equipamentos de impressão</td>
                                        <td>Toner, Localização, Datacard</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">Celular</span></td>
                                        <td>Dispositivos móveis</td>
                                        <td>-</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-secondary">Tablet</span></td>
                                        <td>Dispositivos tablet</td>
                                        <td>-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Dica:</strong> O campo "Fornecedor" é importante para mapear equipamentos que precisam ir para manutenção.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manutenções -->
    <div class="tab-pane fade" id="manutencoes">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-tools me-2"></i>
                    Controle de Manutenções
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Tipos de Manutenção:</h6>
                        <ul>
                            <li><strong>Preventiva:</strong> Manutenções programadas para evitar problemas</li>
                            <li><strong>Corretiva:</strong> Manutenções para corrigir problemas existentes</li>
                            <li><strong>Preditiva:</strong> Manutenções baseadas em análise de dados</li>
                        </ul>
                        
                        <h6 class="mt-4">Status de Manutenção:</h6>
                        <ul>
                            <li><span class="badge bg-warning">Agendada</span> - Manutenção programada</li>
                            <li><span class="badge bg-primary">Em Andamento</span> - Sendo executada</li>
                            <li><span class="badge bg-success">Concluída</span> - Finalizada com sucesso</li>
                            <li><span class="badge bg-danger">Cancelada</span> - Cancelada por algum motivo</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Como Agendar uma Manutenção:</h6>
                        <ol>
                            <li>Acesse o menu <strong>Manutenções</strong></li>
                            <li>Clique em <strong>Nova Manutenção</strong></li>
                            <li>Selecione o equipamento</li>
                            <li>Defina o tipo de manutenção</li>
                            <li>Informe a data prevista</li>
                            <li>Adicione descrição e observações</li>
                            <li>Defina o responsável pela execução</li>
                            <li>Clique em <strong>Agendar</strong></li>
                        </ol>
                        
                        <div class="alert alert-success mt-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>Dica:</strong> Use o campo "Fornecedor" do equipamento para identificar rapidamente onde enviar para manutenção externa.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assinaturas -->
    <div class="tab-pane fade" id="assinaturas">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-file-text me-2"></i>
                    Gestão de Assinaturas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Tipos de Assinatura:</h6>
                        <ul>
                            <li><strong>Software:</strong> Licenças de programas e aplicativos</li>
                            <li><strong>Serviços:</strong> Serviços online e cloud</li>
                            <li><strong>Suporte:</strong> Contratos de suporte técnico</li>
                            <li><strong>Hospedagem:</strong> Serviços de hosting e domínios</li>
                        </ul>
                        
                        <h6 class="mt-4">Status de Assinatura:</h6>
                        <ul>
                            <li><span class="badge bg-success">Ativa</span> - Assinatura em vigor</li>
                            <li><span class="badge bg-warning">Vencendo</span> - Próxima do vencimento</li>
                            <li><span class="badge bg-danger">Vencida</span> - Precisa ser renovada</li>
                            <li><span class="badge bg-secondary">Cancelada</span> - Não será renovada</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Como Cadastrar uma Assinatura:</h6>
                        <ol>
                            <li>Acesse o menu <strong>Assinaturas</strong></li>
                            <li>Clique em <strong>Nova Assinatura</strong></li>
                            <li>Preencha os dados básicos:
                                <ul>
                                    <li>Nome do serviço/software</li>
                                    <li>Tipo de assinatura</li>
                                    <li>Valor mensal/anual</li>
                                    <li>Data de início e vencimento</li>
                                </ul>
                            </li>
                            <li>Anexe comprovantes de pagamento</li>
                            <li>Adicione observações se necessário</li>
                            <li>Clique em <strong>Cadastrar</strong></li>
                        </ol>
                        
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Importante:</strong> Sempre anexe os comprovantes de pagamento para controle financeiro.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configurações -->
    <div class="tab-pane fade" id="configuracoes">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>
                    Sistema de Configurações
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>O que são Configurações:</h6>
                        <p>As configurações permitem personalizar listas de opções, campos padrão e comportamentos do sistema sem precisar alterar código.</p>
                        
                        <h6>Tipos de Configuração:</h6>
                        <ul>
                            <li><strong>Texto:</strong> Valores simples como nomes, códigos</li>
                            <li><strong>Número:</strong> Valores numéricos como prazos, quantidades</li>
                            <li><strong>Sim/Não:</strong> Configurações de ativação/desativação</li>
                            <li><strong>Lista:</strong> Opções para campos de seleção</li>
                            <li><strong>Texto Longo:</strong> Descrições e observações padrão</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Exemplos de Configurações Úteis:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Categoria</th>
                                        <th>Chave</th>
                                        <th>Exemplo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>equipamentos</td>
                                        <td>tipos_equipamento</td>
                                        <td>notebook,desktop,impressora</td>
                                    </tr>
                                    <tr>
                                        <td>manutencoes</td>
                                        <td>status_manutencao</td>
                                        <td>agendada,em_andamento,concluida</td>
                                    </tr>
                                    <tr>
                                        <td>sistema</td>
                                        <td>prazo_garantia_padrao</td>
                                        <td>36</td>
                                    </tr>
                                    <tr>
                                        <td>assinaturas</td>
                                        <td>tipos_assinatura</td>
                                        <td>software,servicos,suporte</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Dica:</strong> Use categorias para organizar suas configurações e facilitar a manutenção.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup -->
    <div class="tab-pane fade" id="backup">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-cloud-download me-2"></i>
                    Sistema de Backup
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Funcionalidades de Backup:</h6>
                        <ul>
                            <li><strong>Backup Completo:</strong> Exporta todos os dados do sistema</li>
                            <li><strong>Backup Seletivo:</strong> Exporta apenas módulos específicos</li>
                            <li><strong>Importação:</strong> Restaura dados de backups anteriores</li>
                            <li><strong>Importação Rápida:</strong> Importa equipamentos e assinaturas em lote</li>
                        </ul>
                        
                        <h6 class="mt-4">Frequência Recomendada:</h6>
                        <ul>
                            <li><strong>Diário:</strong> Para ambientes com muitas alterações</li>
                            <li><strong>Semanal:</strong> Para uso moderado</li>
                            <li><strong>Mensal:</strong> Para uso esporádico</li>
                            <li><strong>Antes de atualizações:</strong> Sempre fazer backup</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Como Fazer Backup:</h6>
                        <ol>
                            <li>Acesse o menu <strong>Backup</strong></li>
                            <li>Escolha o tipo de backup:
                                <ul>
                                    <li>Completo (todos os dados)</li>
                                    <li>Seletivo (módulos específicos)</li>
                                </ul>
                            </li>
                            <li>Clique em <strong>Gerar Backup</strong></li>
                            <li>Aguarde o processamento</li>
                            <li>Faça download do arquivo gerado</li>
                            <li>Armazene em local seguro</li>
                        </ol>
                        
                        <h6 class="mt-4">Como Restaurar Backup:</h6>
                        <ol>
                            <li>Acesse o menu <strong>Backup</strong></li>
                            <li>Clique em <strong>Importar Dados</strong></li>
                            <li>Selecione o arquivo de backup</li>
                            <li>Escolha o que deseja restaurar</li>
                            <li>Confirme a importação</li>
                        </ol>
                        
                        <div class="alert alert-danger mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Atenção:</strong> A restauração pode sobrescrever dados existentes. Sempre faça backup antes de restaurar.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-question-circle me-2"></i>
            Perguntas Frequentes (FAQ)
        </h5>
    </div>
    <div class="card-body">
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        Como alterar o código interno de um equipamento?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Acesse o equipamento em modo de edição e altere o campo "Código Interno". O sistema verificará se o novo código não está em uso.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Como anexar comprovantes nas assinaturas?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        No formulário de assinatura, use o campo "Anexos" para fazer upload de comprovantes de pagamento, contratos ou outros documentos relevantes.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        Como configurar novos tipos de equipamento?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Acesse "Configurações", crie uma nova configuração com categoria "equipamentos", chave "tipos_equipamento" e tipo "Lista". Adicione os tipos separados por vírgula.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                        O que fazer se um equipamento for para manutenção externa?
                    </button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Altere o status do equipamento para "Em Manutenção" e crie uma nova manutenção informando o fornecedor responsável. Use o campo "Fornecedor" do equipamento para facilitar o controle.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>

