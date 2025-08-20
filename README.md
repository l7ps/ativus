# Ativus - Sistema de Gestão de Ativos

O Ativus é um sistema de gestão de ativos desenvolvido para auxiliar no controle e acompanhamento de equipamentos, assinaturas, pagamentos e manutenções. Ele oferece uma interface intuitiva para gerenciar o ciclo de vida dos ativos, desde a aquisição até a manutenção e descarte.

## Funcionalidades Principais

*   **Gestão de Equipamentos:** Cadastre e gerencie informações detalhadas sobre seus equipamentos.
*   **Controle de Assinaturas:** Acompanhe assinaturas e contratos, com alertas de vencimento.
*   **Registro de Pagamentos:** Mantenha um histórico de pagamentos relacionados aos ativos.
*   **Agendamento de Manutenções:** Programe e registre manutenções preventivas e corretivas.
*   **Relatórios:** Gere relatórios para análise e tomada de decisão.
*   **Backup e Restauração:** Ferramentas para importação e exportação de dados.

## Screenshots

### Dashboard
![Dashboard](/screenshots/dashboard_view.webp)

### Assinaturas
![Assinaturas](/screenshots/assinaturas_view.webp)

### Equipamentos
![Equipamentos](/screenshots/equipamentos_view.webp)

### Manutenções
![Manutenções](/screenshots/manutencoes_view.webp)

## Tecnologias Utilizadas

O sistema Ativus é construído com as seguintes tecnologias:

*   **Backend:** PHP
*   **Frontend:** HTML, CSS, JavaScript
*   **Banco de Dados:** SQLite

## Instalação

Para instalar e configurar o sistema Ativus, siga os passos abaixo:

### Pré-requisitos

Certifique-se de ter os seguintes itens instalados em seu ambiente:

*   Servidor Web (Apache, Nginx, etc.)
*   PHP (versão 7.4 ou superior recomendada)
*   Extensão PHP PDO SQLite habilitada

### Passos para Instalação

1.  **Clone o Repositório:**

    ```bash
    git clone https://github.com/seu-usuario/ativus.git
    cd ativus
    ```

2.  **Configuração do Banco de Dados:**

    O sistema Ativus utiliza SQLite, um banco de dados baseado em arquivo. Não é necessário configurar um servidor de banco de dados separado (como MySQL).

    O arquivo do banco de dados (`database.sqlite`) será criado automaticamente na pasta `db/` quando o `setup.php` for executado pela primeira vez. Certifique-se de que a pasta `db/` tenha permissões de escrita para o servidor web.

3.  **Executar `setup.php`:**

    Acesse `setup.php` no seu navegador para configurar as tabelas iniciais do banco de dados. Este script cria a estrutura básica necessária para o funcionamento do sistema.

    `http://localhost/ativus/setup.php` (ou o caminho correspondente à sua instalação)

4.  **Executar `init_db.php`:**

    Após a execução do `setup.php`, execute `init_db.php` para popular o banco de dados com dados iniciais (usuário administrador, configurações padrão, etc.).

    `http://localhost/ativus/init_db.php` (ou o caminho correspondente à sua instalação)

    **Atenção:** Estes scripts devem ser executados apenas uma vez, durante a primeira instalação. Após a conclusão, é recomendável removê-los ou restringir o acesso para evitar reconfigurações acidentais.

## Como Usar

Após a instalação, acesse a página principal do sistema:

`http://localhost/ativus/index.php` (ou o caminho correspondente à sua instalação)

Faça login com as credenciais padrão (se `init_db.php` foi executado) ou crie um novo usuário. Explore os módulos de gestão de equipamentos, assinaturas, pagamentos e manutenções para começar a utilizar o sistema.

## Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues, enviar pull requests ou sugerir melhorias.

## Licença

Este projeto está licenciado sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## Contato

Para dúvidas ou suporte, entre em contato com Letícia Pereira.

