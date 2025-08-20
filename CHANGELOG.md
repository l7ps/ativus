# Changelog - Sistema Ativus

## Versão 2.6 (2025-08-20)

**Melhorias e Correções:**
- **Favicon Atualizado:**
  - O favicon do sistema foi atualizado para o ícone `alocacao.ico` fornecido, melhorando a personalização visual.

**Instruções de Atualização:**
1.  **Faça backup** do seu sistema e banco de dados atuais.
2.  **Substitua** todos os arquivos do seu sistema Ativus pelos arquivos desta nova versão.
3.  **Acesse `http://localhost/Ativus/init_db.php`** (ou o caminho correspondente no seu servidor web) no seu navegador para inicializar/atualizar o banco de dados. Este script garantirá que todas as tabelas e colunas necessárias estejam presentes e que as alterações sejam aplicadas de forma segura.
4.  **Verifique as permissões de escrita** para os diretórios `uploads/assinaturas/` e `backups/`.
5.  **Teste** todas as funcionalidades para garantir que tudo esteja operando corretamente.

---

## Versão 2.5 (2025-08-20)

**Melhorias e Correções:**
- **Modelos de Importação CSV Otimizados:**
  - Os modelos de arquivo CSV para importação de equipamentos (`modules/backup/modelo_importacao_equipamentos.csv`) e assinaturas (`modules/backup/modelo_importacao_assinaturas.csv`) foram revisados e otimizados.
  - Agora incluem cabeçalhos claros e exemplos de dados para facilitar o preenchimento e reduzir erros de importação.
  - As instruções nos scripts de importação (`import_equipamentos.php` e `import_assinaturas.php`) foram atualizadas para refletir os novos formatos e tratar a criação automática de fornecedores para equipamentos.

**Instruções de Atualização:**
1.  **Faça backup** do seu sistema e banco de dados atuais.
2.  **Substitua** todos os arquivos do seu sistema Ativus pelos arquivos desta nova versão.
3.  **Acesse `http://localhost/Ativus/init_db.php`** (ou o caminho correspondente no seu servidor web) no seu navegador para inicializar/atualizar o banco de dados. Este script garantirá que todas as tabelas e colunas necessárias estejam presentes e que as alterações sejam aplicadas de forma segura.
4.  **Verifique as permissões de escrita** para os diretórios `uploads/assinaturas/` e `backups/`.
5.  **Teste** todas as funcionalidades para garantir que tudo esteja operando corretamente.

---

## Versão 2.4 (2025-08-20)

**Melhorias e Correções:**
- **Correção de Erro de Sintaxe PHP:**
  - Corrigido `Parse error: syntax error, unexpected identifier "badge"` em `modules/manutencoes/form.php` na linha 422.
  - Este erro estava relacionado a uma concatenação incorreta de strings dentro de um bloco JavaScript, que foi devidamente ajustada.

**Instruções de Atualização:**
1.  **Faça backup** do seu sistema e banco de dados atuais.
2.  **Substitua** todos os arquivos do seu sistema Ativus pelos arquivos desta nova versão.
3.  **Acesse `http://localhost/Ativus/init_db.php`** (ou o caminho correspondente no seu servidor web) no seu navegador para inicializar/atualizar o banco de dados. Este script garantirá que todas as tabelas e colunas necessárias estejam presentes e que as alterações sejam aplicadas de forma segura.
4.  **Verifique as permissões de escrita** para os diretórios `uploads/assinaturas/` e `backups/`.
5.  **Teste** todas as funcionalidades para garantir que tudo esteja operando corretamente.

---

## Versão 2.3 (2025-08-20)

**Melhorias e Correções:**
- **Correção de Erro de Duplicação de Coluna:**
  - O script `init_db.php` foi aprimorado para lidar de forma mais robusta com a criação de colunas, verificando a existência antes de tentar adicioná-las, resolvendo o erro `duplicate column name: fornecedor_id`.
  - Isso garante que o `init_db.php` possa ser executado múltiplas vezes sem causar erros, facilitando a atualização da estrutura do banco de dados.
- **Instruções de Inicialização Simplificadas:**
  - O processo de configuração do banco de dados agora é mais resiliente, minimizando a necessidade de intervenções manuais.

**Instruções de Atualização:**
1.  **Faça backup** do seu sistema e banco de dados atuais.
2.  **Substitua** todos os arquivos do seu sistema Ativus pelos arquivos desta nova versão.
3.  **Acesse `http://localhost/Ativus/init_db.php`** (ou o caminho correspondente no seu servidor web) no seu navegador para inicializar/atualizar o banco de dados. Este script garantirá que todas as tabelas e colunas necessárias estejam presentes e que as alterações sejam aplicadas de forma segura.
4.  **Verifique as permissões de escrita** para os diretórios `uploads/assinaturas/` e `backups/`.
5.  **Teste** todas as funcionalidades para garantir que tudo esteja operando corretamente.

---

## Versão 2.2 (2025-08-20)

**Melhorias e Correções:**
- **Correção de Erro de Sintaxe PHP:**
  - Corrigido `Parse error: syntax error, unexpected token "?"` em `modules/manutencoes/index.php` na linha 344.
- **Correção de Erro de Chave Estrangeira (FOREIGN KEY):**
  - Ajustado o tratamento do campo `fornecedor_id` nos formulários de equipamentos (`modules/equipamentos/form.php`) e manutenções (`modules/manutencoes/form.php`) para permitir valores `NULL` quando nenhum fornecedor é selecionado.
  - O script `db/updates_v2.sql` foi revisado para garantir que as colunas `fornecedor_id` nas tabelas `equipamentos` e `manutencoes` sejam criadas como `NULLABLE`.
  - Isso deve resolver o erro `SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed` ao tentar salvar registros sem um fornecedor associado.

**Instruções de Atualização:**
1.  **Faça backup** do seu sistema e banco de dados atuais.
2.  **Substitua** todos os arquivos do seu sistema Ativus pelos arquivos desta nova versão.
3.  **Acesse `http://localhost/Ativus/init_db.php`** (ou o caminho correspondente no seu servidor web) no seu navegador para inicializar/atualizar o banco de dados. Este script garantirá que todas as tabelas e colunas necessárias estejam presentes e que as alterações de `NULLABLE` sejam aplicadas.
4.  **Verifique as permissões de escrita** para os diretórios `uploads/assinaturas/` e `backups/`.
5.  **Teste** todas as funcionalidades para garantir que tudo esteja operando corretamente.

---

## Versão 2.1 (2025-08-20)

**Novas Funcionalidades:**
- **Módulo de Fornecedores:**
  - Adicionado módulo completo para cadastro, listagem, edição e exclusão de fornecedores.
  - Fornecedores podem ser vinculados a equipamentos e manutenções.
- **Campo Fornecedor em Equipamentos:**
  - Campo de seleção de fornecedor adicionado ao formulário de equipamentos.
  - Exibição do nome do fornecedor na listagem de equipamentos.
- **Campo Fornecedor em Manutenções:**
  - Campo de seleção de fornecedor adicionado ao formulário de manutenções.
  - Exibição do nome do fornecedor na listagem de manutenções.
- **Script de Inicialização/Atualização do Banco de Dados (`init_db.php`):**
  - Novo script para facilitar a configuração inicial e atualizações do banco de dados.
  - Garante que todas as tabelas e colunas necessárias (incluindo `fornecedores`, `configuracoes`, `anexos_assinaturas`, `fornecedor_id` em `equipamentos` e `manutencoes`) sejam criadas ou atualizadas corretamente.

**Melhorias e Correções:**
- **Correção de Erro Fatal `PDOException: no such table`:**
  - Resolvido o problema de tabelas ausentes (`configuracoes`, `anexos_assinaturas`) através da atualização do `updates_v2.sql` e da criação do `init_db.php`.
  - O `init_db.php` agora garante que a estrutura do banco de dados esteja correta antes de iniciar o sistema.
- **Correção de Erro `table equipamentos has no column named fornecedor`:**
  - A coluna `fornecedor_id` agora é criada corretamente na tabela `equipamentos` durante a inicialização do banco de dados.
- **Módulo de Configurações:**
  - Funcionalidade restaurada e testada após a correção do banco de dados.
- **Módulo de Backup e Importação:**
  - Funcionalidade restaurada e testada após a correção do banco de dados.
- **Sistema de Anexos (Assinaturas):**
  - Funcionalidade restaurada e testada após a correção do banco de dados.
- **Navegação (Navbar):**
  - Adicionado link para o novo módulo de Fornecedores no menu de navegação.

**Instruções de Atualização:**
1.  **Faça backup** do seu sistema e banco de dados atuais.
2.  **Substitua** todos os arquivos do novo sistema.
3.  **Execute** o script `db/updates_v2.sql` no banco de dados.
4.  **Configure permissões** para os diretórios: `uploads/assinaturas/` e `backups/`.
5.  **Teste** todas as funcionalidades.

---

## Versão 2.0 (2025-08-20)

**Novas Funcionalidades:**
- **Módulo de Configurações:** Campo de configurações para gerenciar campos e opções de lista de seleção de todo o sistema.
- **Aba de Ajuda (Help):** Nova aba com orientações sobre o sistema.
- **Módulo de Backup do Banco de Dados:** Opção para criar backup e importar o banco de dados.
- **Importação Rápida:** Opções para importar equipamentos e assinaturas de forma mais rápida.
- **Campo Datacard para Impressoras:** Checkbox específico para impressoras modelo Datacard.

**Melhorias e Correções:**
- **Erro no form.php de equipamentos:** Corrigido erro de sintaxe e JavaScript.
- **Sistema de Anexos:** Implementado upload, visualização e exclusão de anexos para assinaturas.
- **Campo Fornecedor em Equipamentos:** Adicionado campo para mapear fornecedores.

**Instruções de Atualização:**
1.  **Faça backup** do sistema atual.
2.  **Substitua** os arquivos pelo novo sistema.
3.  **Execute** o script `db/updates_v2.sql` no banco de dados.
4.  **Configure permissões** para os diretórios: `uploads/assinaturas/` e `backups/`.
5.  **Teste** todas as funcionalidades.

---

**Data de Lançamento**: Dezembro 2024  
**Desenvolvido por**: Equipe Ativus  
**Compatibilidade**: PHP 7.4+, MySQL 5.7+


