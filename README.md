# 🦖 Dino's Auto Peças - E-commerce MVC

![Status](https://img.shields.io/badge/Status-Concluído-success)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1)
![Bootstrap](https://img.shields.io/badge/Frontend-Bootstrap%205-7952B3)

Um sistema de e-commerce completo e robusto desenvolvido em **PHP Puro (Sem Frameworks)**, utilizando a arquitetura **MVC (Model-View-Controller)**. O projeto simula uma loja de autopeças com funcionalidades avançadas de gestão de estoque, carrinho de compras persistente e painel administrativo com indicadores de BI.


## 🚀 Funcionalidades

### 🛒 Área do Cliente (Frontend)
* **Vitrine de Produtos:** Listagem dinâmica com imagens, preços e status de estoque.
* **Navegação:** Filtro por categorias e Barra de Pesquisa funcional.
* **Carrinho Inteligente:**
    * Adicionar/Remover itens.
    * Ajuste de quantidade (respeitando o limite de estoque).
    * **Persistência:** O carrinho é salvo no banco de dados. Se o cliente sair e voltar, os itens são recuperados.
    * **Mesclagem:** Itens adicionados como visitante são unidos à conta ao fazer login.
* **Gestão de Conta:**
    * Cadastro e Login seguro (hash de senha).
    * Gestão de múltiplos Endereços de Entrega.
    * Alteração de Senha segura.
    * Histórico de Pedidos com status.

### ⚙️ Área Administrativa (Backend)
* **Dashboard (Tech Forge):** Integração com **Metabase** para visualização de KPIs (Vendas, Estoque Crítico, etc.).
* **Gestão de Produtos:** CRUD completo (Criar, Ler, Atualizar, Excluir) com upload de imagens.
* **Gestão de Usuários:** Listagem de clientes e controle de permissões (Promover a Admin / Rebaixar).
* **Segurança:** Rotas protegidas acessíveis apenas por administradores.

---

## 🗄️ Banco de Dados Avançado

Este projeto vai além do básico, implementando regras de negócio diretamente no MySQL:

* **Triggers:** Auditoria automática de alteração de preços (`historico_precos`).
* **Stored Procedures:** Geração de massa de dados para testes de carga (`gerar_vendas_teste`).
* **Functions:** Verificação de disponibilidade de estoque a nível de banco.
* **Índices:** Otimização de consultas nas colunas de busca e chaves estrangeiras.
* **Transações (ACID):** O fechamento do pedido garante que o estoque só é baixado se o pedido for gravado com sucesso.

---

## 🛠️ Tecnologias Utilizadas

* **Backend:** PHP 8+ (PDO, Orientação a Objetos, MVC).
* **Frontend:** HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons.
* **Banco de Dados:** MySQL / MariaDB.
* **Servidor:** Apache (XAMPP).
* **Ferramentas:** DBeaver (Gestão de Banco), Metabase (BI/Dashboard).

---

## 📦 Como Instalar e Rodar

### Pré-requisitos
* [XAMPP](https://www.apachefriends.org/) instalado.
* Navegador Web moderno.

### Passo a Passo

1.  **Clonar o Repositório:**
    Baixe ou clone este projeto para dentro da pasta `htdocs` do seu XAMPP.
    ```bash
    C:\xampp\htdocs\ecommerce-auto-pecas
    ```

2.  **Configurar o Banco de Dados:**
    * Abra o seu gestor SQL (phpMyAdmin ou DBeaver).
    * Crie um banco de dados chamado `ecommerce_projeto`.
    * Importe o arquivo `script_banco.sql` (ou execute os scripts de criação das tabelas).
    * *(Opcional)* Execute a Procedure para popular o banco: `CALL gerar_vendas_teste(50);`

3.  **Configurar a Conexão:**
    Verifique o arquivo `config/Conexao.php` e garanta que as credenciais batem com as suas:
    ```php
    private static $host = "localhost";
    private static $user = "root";
    private static $pass = ""; // Sua senha (geralmente vazia no XAMPP)
    private static $db   = "ecommerce_projeto";
    ```

4.  **Configurar o Apache (Importante!):**
    Para que as URLs amigáveis (ex: `/produto/listar`) funcionem, o `mod_rewrite` deve estar ativo.
    * No painel do XAMPP, vá em Config -> `httpd.conf`.
    * Descomente a linha (tire o #): `LoadModule rewrite_module modules/mod_rewrite.so`.
    * Garanta que `AllowOverride` esteja como `All` para a pasta `htdocs`.
    * **Reinicie o Apache.**

5.  **Acessar:**
    Abra o navegador e acesse:
    [http://localhost/ecommerce-auto-pecas/public/home](http://localhost/ecommerce-auto-pecas/public/home)

---

## 📂 Estrutura de Pastas (MVC)

ecommerce-auto-pecas/ ├── config/ # Configurações de Banco de Dados ├── controllers/ # Lógica de Negócio (PHP) ├── models/ # Acesso a Dados e SQL (PHP) ├── views/ # Telas e HTML (.phtml) │ ├── admin/ # Telas do Painel Administrativo │ ├── carrinho/ # Telas do Carrinho │ ├── cliente/ # Área do Cliente │ ├── login/ # Telas de Acesso │ ├── menu/ # Home e Institucional │ ├── partials/ # Cabeçalho e Rodapé reutilizáveis │ └── produto/ # Catálogo e Detalhes └── public/ # Ponto de Entrada (Acessível pelo navegador) ├── css/ # Estilos ├── img/ # Imagens e Uploads ├── index.php # Roteador Principal └── .htaccess # Regras de URL


---

## 🤝 Autores

* **[Alexandre de Jesus Gonçalves]** - *Desenvolvedor Backend & Banco de Dados*
* **[João Mateus Alcântara dos Santos]** - *Desenvolvedor Frontend & Design*

---

## 📄 Licença

Este projeto foi desenvolvido para fins acadêmicos.