# 🚀 Guia Rápido de Instalação - Sistema Ativus

## ⚡ Instalação Rápida (XAMPP)

### 1. Pré-requisitos
- ✅ XAMPP instalado e funcionando
- ✅ Apache ativo no XAMPP
- ✅ PHP 8.1+ habilitado

### 2. Instalação
1. **Extrair arquivos**
   - Descompacte o arquivo `Ativus_Sistema_Completo.zip`
   - Copie a pasta `Ativus` para `C:\xampp\htdocs\`

2. **Acessar o sistema**
   - Abra o navegador
   - Acesse: `http://localhost/Ativus/`
   - O sistema redirecionará automaticamente para o setup

3. **Executar setup**
   - Clique em "🔄 Tentar Novamente" se aparecer erro
   - Aguarde a mensagem "✅ Banco de dados criado com sucesso!"
   - Clique em "🏠 Ir para o Dashboard"

### 3. Pronto! 🎉
O sistema está funcionando e pronto para uso.

---

## 🛠️ Instalação Alternativa (Servidor PHP)

Para desenvolvimento ou testes:

```bash
# Extrair arquivos
unzip Ativus_Sistema_Completo.zip

# Entrar na pasta
cd Ativus

# Iniciar servidor
php -S localhost:8080

# Acessar no navegador
# http://localhost:8080
```

---

## 📁 Estrutura Final

Após a instalação, sua estrutura deve estar assim:

```
C:\xampp\htdocs\Ativus\
├── assets/          # CSS, JS e imagens
├── db/              # Banco de dados SQLite (criado automaticamente)
├── includes/        # Arquivos de conexão e funções
├── modules/         # Módulos do sistema
├── templates/       # Templates HTML
├── uploads/         # Arquivos enviados
├── index.php        # Dashboard principal
├── setup.php        # Instalação do sistema
└── README.md        # Documentação completa
```

---

## ❗ Solução de Problemas

### Erro: "Banco de dados não encontrado"
**Solução**: Execute `http://localhost/Ativus/setup.php`

### Erro: "Permissão negada"
**Solução**: Verifique se o XAMPP tem permissões para criar arquivos

### Página em branco
**Solução**: Verifique se o Apache está rodando no XAMPP

---

## 🎯 Primeiros Passos

Após a instalação:

1. **Explore o Dashboard** - Visão geral do sistema
2. **Cadastre uma Assinatura** - Menu Financeiro > Assinaturas
3. **Registre um Pagamento** - Menu Financeiro > Pagamentos
4. **Adicione um Equipamento** - Menu Equipamentos
5. **Registre uma Manutenção** - Menu Equipamentos > Manutenções

---

## 📞 Suporte

- 📖 **Documentação completa**: `README.md`
- 🔧 **Problemas técnicos**: Verifique logs do Apache
- 💡 **Dicas**: Todos os módulos têm seção "Dicas" na lateral

**Sistema Ativus v1.0** - Gestão Empresarial Simplificada

