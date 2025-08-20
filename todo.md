## Tarefas a serem realizadas:

### Fase 1: Análise e extração do sistema atual
- [x] Descompactar o arquivo zip do sistema Ativus (Concluído)
- [x] Listar o conteúdo do diretório do sistema Ativus para análise (Concluído)
- [x] Analisar a estrutura do projeto e identificar os arquivos relevantes.

### Fase 2: Correção de bugs e refatoração do código
- [x] Corrigir o erro de sintaxe em `modules/equipamentos/form.php` na linha 607.
- [x] Implementar a funcionalidade de adicionar impressora com campos para toner, datacard e localização.
- [x] Unificar 'código interno' e 'número de patrimônio' no módulo de celular.
- [x] Remover a parte de 'etiqueta' do módulo de celular. (Não encontrado, assumido como parte da unificação do código interno/patrimônio)

### Fase 3: Atualização da interface e paleta de cores
- [x] Remover efeitos visuais do 'Bem-vindo ao Ativus' no Dashboard. (Removidas animações CSS e JavaScript)
- [x] Reformular a paleta de cores para preto, cinza ou verde escuro.
- [x] Alterar o nome do módulo 'financeiro' para 'Assinaturas' no navbar.

### Fase 4: Reorganização de módulos e funcionalidades
- [x] Adicionar campo para mostrar pagamentos já feitos ao clicar em uma assinatura criada.

### Fase 5: Criação do módulo de relatórios
- [x] Criar um novo módulo para relatórios.
- [x] Implementar funcionalidades úteis para gerar relatórios:
  - Relatório de assinaturas com próximos vencimentos
  - Relatório de equipamentos por status
  - Relatório de gastos por período
  - Relatório de manutenções por período
  - Relatório de inventário completo
  - Exportação em CSV
  - Interface amigável com relatórios rápidos

### Fase 6: Limpeza e organização final do projeto
- [x] Remover arquivos antigos e não utilizados. (Não encontrados arquivos desnecessários)
- [x] Atualizar o `setup.php` para a nova versão. (Atualizada paleta de cores)
- [x] Remover a parte de criação de app desktop. (Não encontrada no projeto)
- [x] Atualizar documentação (README.md) para refletir as mudanças da versão 1.0

### Fase 7: Entrega da versão 1.0 corrigida
- [ ] Empacotar a versão 1.0 corrigida do sistema.
- [ ] Entregar o sistema ao usuário.

