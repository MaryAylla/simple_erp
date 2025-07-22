
# 🚀 ERP Simples: Sistema de Gestão Empresarial (PHP & MySQL)

Este projeto é um sistema web dinâmico, seguro e modular de **Gestão Empresarial (ERP - Enterprise Resource Planning)**, desenvolvido com **PHP** no back-end e **HTML/CSS/JavaScript** no front-end. Ele representa a evolução de um projeto CRUD básico para uma aplicação completa, capaz de gerenciar diversas áreas de uma empresa e demonstrar conceitos avançados de desenvolvimento web.

Construído com foco em **modularidade, segurança e usabilidade**, este ERP oferece uma base sólida para a gestão de clientes, produtos, vendas, finanças e usuários do sistema, com relatórios detalhados e um design moderno.

Este projeto é uma **excelente oportunidade para aprofundar conhecimentos e aplicar conceitos em:**

  * **PHP (Orientado a Procedimentos & Segurança):**
      * **Programação Procedural:** Estrutura de código organizada e modular.
      * **Manipulação de Requisições:** Gerenciamento de dados via `GET` e `POST`.
      * **Superglobais Essenciais:** Uso de `$_GET`, `$_POST`, `$_SERVER` e, fundamentalmente, `$_SESSION` para gerenciamento de estado e mensagens "flash".
      * **Sessões PHP:** Implementação de um sistema de autenticação robusto (Login, Logout).
      * **Segurança Criptográfica:** Armazenamento seguro de senhas com `password_hash()` e verificação com `password_verify()`.
      * **Prevenção de XSS:** Uso de `htmlspecialchars()` para sanitização de saída.
      * **Manipulação de Arquivos:** Lógica para upload e gerenciamento de fotos de produtos.
  * **Banco de Dados (MySQL & PDO):**
      * **Modelagem de Dados Relacionais:** Design de um esquema de banco de dados com múltiplas tabelas (`clientes`, `produtos`, `categorias`, `vendas`, `itens_venda`, `fluxo_caixa`, `usuarios`).
      * **Chaves Estrangeiras:** Definição e gestão de relacionamentos entre tabelas (`FOREIGN KEY`).
      * **Operações CRUD Avançadas:** Consultas SQL (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) para todos os módulos.
      * **PDO (PHP Data Objects):** Conexão segura e eficiente com o MySQL.
      * **Prepared Statements:** Uso de placeholders (`?` e nomeados `:param`) para prevenir **SQL Injection**, garantindo a integridade e segurança das transações no banco.
      * **Transações de Banco de Dados:** Implementação de `beginTransaction()`, `commit()`, `rollBack()` para garantir a atomicidade de operações complexas (ex: registro de venda e atualização de estoque).
  * **HTML, CSS & JavaScript (Front-end):**
      * **Estruturação Semântica:** HTML5 para a criação de formulários interativos, tabelas dinâmicas e layouts responsivos.
      * **Estilização Profissional (CSS3):** Design moderno e visualmente agradável com uma paleta de cores consistente (tons de roxo e lilás), tipografia personalizada (Google Fonts - Poppins) e sombreamento sutil.
      * **Bootstrap 5:** Integração para um framework de UI que acelera o desenvolvimento de interfaces responsivas e acessíveis.
      * **JavaScript:** Interações dinâmicas no front-end (ex: adição de itens de venda, cálculo de total, controle de estoque em tempo real nos formulários).
      * **Chart.js:** Biblioteca JavaScript para visualização de dados em gráficos interativos (ex: fluxo de caixa).
  * **Arquitetura de Aplicações Web:**
      * **Modularização e Reusabilidade:** Código PHP organizado em módulos e funções dedicadas (`includes/`, `modules/`).
      * **Separação de Preocupações:** Claro distinção entre lógica de negócio (PHP), apresentação (HTML/CSS) e persistência de dados.
      * **Controle de Acesso (Autorização):** Restrição de funcionalidades e páginas com base em perfis de usuário (`admin`, `vendedor`, `financeiro`).
      * **Geração de Documentos:** Criação de PDFs dinâmicos com a biblioteca **Dompdf**.

-----

## ✨ Módulos e Funcionalidades Principais

O sistema ERP é composto pelos seguintes módulos interligados:

1.  **Módulo de Autenticação:**
      * **Registro de Usuários do Sistema:** Cadastros de novos usuários com definição de perfil (`admin`, `vendedor`, `financeiro`) e status ativo/inativo.
      * **Login & Logout:** Autenticação segura de usuários e encerramento de sessão.
      * **Proteção de Páginas:** Controle de acesso a módulos e funcionalidades com base no status de login e perfil do usuário.
2.  **Módulo de Clientes:**
      * **CRUD Completo:** Cadastro, listagem (com busca e paginação), edição e exclusão de clientes (Nome, CPF/CNPJ, Telefone, E-mail, Endereço).
      * **Validação de Duplicidade:** Verifica e-mail e CPF/CNPJ únicos durante cadastro e edição.
3.  **Módulo de Produtos:**
      * **CRUD Completo:** Cadastro, listagem (com busca, filtros por categoria e ordenação), edição e exclusão de produtos (Nome, Descrição, Preço, Quantidade em Estoque, Foto, Categoria).
      * **Upload de Imagens:** Gerenciamento de fotos de produtos no servidor.
      * **Controle de Estoque:** `quantidade_estoque` integrada, atualizada automaticamente pelas vendas.
      * **Categorias:** Produtos podem ser associados a categorias.
4.  **Módulo de Vendas:**
      * **Registro de Pedidos:** Seleção de cliente, adição dinâmica de múltiplos produtos ao pedido, cálculo automático de valor total.
      * **Atualização Automática de Estoque:** Diminui a quantidade de produtos ao registrar uma venda e a repõe ao excluir uma venda.
      * **Histórico de Vendas:** Visualização de todos os pedidos, com filtros por cliente.
      * **Detalhes do Pedido:** Página dedicada para visualizar todos os itens de uma venda específica.
      * **Geração de Recibos/Faturas em PDF:** Emissão de documentos detalhados de vendas com a biblioteca **Dompdf**.
5.  **Módulo Financeiro (Fluxo de Caixa):**
      * **Registro de Movimentações:** Cadastro de entradas (receitas) e saídas (despesas).
      * **Saldo Atual:** Cálculo e exibição do lucro/prejuízo atual.
      * **Visualização de Movimentações:** Lista detalhada de todas as transações, com filtros por tipo e mês/ano.
      * **Relatórios Gráficos:** Visualização do fluxo de caixa mensal com gráficos de barras e linha (Chart.js).
6.  **Módulo de Relatórios Avançados:**
      * **Relatórios Consolidados:** Exibição de:
          * Clientes que mais compram (Top X).
          * Produtos mais vendidos (Top X).
          * Vendas consolidadas por mês e por produto.
      * **Exportação em PDF:** Geração de documentos PDF para todos os relatórios financeiros e de vendas.

-----

## 🛠️ Tecnologias Utilizadas

  * **PHP (5.6.x+):** Linguagem de programação back-end.
  * **MySQL:** Sistema de Gerenciamento de Banco de Dados Relacional.
  * **PDO (PHP Data Objects):** Extensão PHP para acesso a banco de dados.
  * **HTML5:** Estrutura e marcação de conteúdo.
  * **CSS3:** Estilização e design.
  * **JavaScript:** Interatividade client-side.
  * **Bootstrap 5:** Framework CSS para design responsivo.
  * **Google Fonts:** Poppins (para tipografia).
  * **Chart.js:** Biblioteca JavaScript para gráficos.
  * **Dompdf:** Biblioteca PHP para geração de PDFs a partir de HTML.

-----

## 🚀 Como Executar Localmente

Para rodar este projeto em sua máquina, você precisará de um ambiente de servidor web com suporte a **PHP (versão 7.1.0 ou superior para Dompdf)** e **MySQL** (como XAMPP, WAMP, EasyPHP ou Docker).

1.  **Clone este repositório:**

    ```bash
    git clone https://github.com/MaryAylla/simple_erp.git
    ```

2.  **Acesse a pasta do projeto:**

    ```bash
    cd simple_erp
    ```

    (Mova esta pasta para o diretório de documentos raiz do seu servidor web, ex: `htdocs` do XAMPP).

3.  **Configuração do Banco de Dados MySQL:**

      * **Inicie o serviço MySQL** do seu ambiente (XAMPP, EasyPHP, etc.).

      * Acesse o **phpMyAdmin** (geralmente em `http://localhost/phpmyadmin`).

      * **Crie um novo banco de dados** chamado `erp_simples` (collation: `utf8mb4_unicode_ci`).

      * **Execute os comandos SQL para criar as tabelas:**
        Na aba "SQL" do banco de dados `erp_simples`, execute as seguintes queries (uma por uma ou todas juntas):

        ```sql
        -- Tabela de Clientes
        CREATE TABLE clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            cpf_cnpj VARCHAR(20) UNIQUE,
            telefone VARCHAR(20),
            email VARCHAR(191) UNIQUE,
            endereco TEXT,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Tabela de Categorias (para Produtos)
        CREATE TABLE categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            descricao TEXT
        );

        -- Tabela de Produtos
        CREATE TABLE produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            descricao TEXT,
            preco DECIMAL(10, 2) NOT NULL,
            quantidade_estoque INT NOT NULL DEFAULT 0,
            categoria_id INT NULL,
            foto_url VARCHAR(255) NULL,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
        );

        -- Tabela de Vendas
        CREATE TABLE vendas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            valor_total DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT
        );

        -- Tabela de Itens de Venda
        CREATE TABLE itens_venda (
            id INT AUTO_INCREMENT PRIMARY KEY,
            venda_id INT NOT NULL,
            produto_id INT NOT NULL,
            quantidade INT NOT NULL,
            preco_unitario DECIMAL(10, 2) NOT NULL,
            subtotal DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
            FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT
        );

        -- Tabela de Fluxo de Caixa (Financeiro)
        CREATE TABLE fluxo_caixa (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo ENUM('entrada', 'saida') NOT NULL,
            descricao VARCHAR(255) NOT NULL,
            valor DECIMAL(10, 2) NOT NULL,
            data_movimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Tabela de Usuários do Sistema (Autenticação e Permissões)
        CREATE TABLE usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(191) NOT NULL UNIQUE,
            senha_hash VARCHAR(255) NOT NULL,
            perfil ENUM('admin', 'vendedor', 'financeiro') DEFAULT 'vendedor',
            ativo BOOLEAN DEFAULT TRUE,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ```

      * **Atualize o arquivo `includes/database.php`** com as credenciais do seu banco de dados MySQL (host, nome do banco, usuário e senha). Exemplo:

        ```php
        $db_name = 'erp_simples'; // Nome do DB que você criou
        $db_user = 'root';        // Seu usuário do MySQL (geralmente 'root')
        $db_pass = '';            // Sua senha do MySQL (geralmente vazia para 'root')
        ```

4.  **Instalação do Dompdf:**

      * Baixe a biblioteca **Dompdf** (arquivo `.zip` da versão mais recente/estável) de [https://github.com/dompdf/dompdf/releases](https://github.com/dompdf/dompdf/releases).
      * Extraia o conteúdo e **renomeie a pasta extraída para `dompdf`**.
      * Mova esta pasta `dompdf` para o diretório `erp_simples/includes/` do seu projeto.

5.  **Habilitar Extensões PHP:**

      * No seu arquivo `php.ini` (localizado na pasta da sua versão PHP ativa no seu ambiente, ex: XAMPP/EasyPHP), procure e **descomente** as seguintes linhas (removendo o `;` no início):
        ```ini
        extension=php_pdo_mysql.dll
        extension=php_gd2.dll   ; Necessária para Dompdf e processamento de imagens
        extension=php_mbstring.dll ; Necessária para manipulação de strings (UTF-8) e Dompdf
        extension=php_openssl.dll ; Pode ser necessária para requisições HTTPS por algumas libs
        ```
      * **Reinicie o servidor web completamente** (Apache/Nginx e PHP) para que as alterações no `php.ini` tenham efeito.

6.  **Acesse o Projeto:**

      * Abra seu navegador e navegue até a URL da sua página de login:
        ```
        http://localhost/erp_simples/login.php
        ```
        (Ajuste `/erp_simples/` se seu projeto estiver em outra subpasta ou direto na raiz do servidor).

7.  **Primeiro Acesso e Cadastro (Usuário Admin):**

      * Na primeira vez, o sistema não terá usuários. Vá para a página de registro: `http://localhost/erp_simples/register.php`.
      * Cadastre o primeiro usuário (será o administrador inicial). Lembre-se do e-mail e da senha\!
      * Após o cadastro, faça login com as credenciais criadas.

8.  **Teste as Funcionalidades:**

      * Explore o Dashboard.
      * Acesse e teste o CRUD completo dos **Clientes**, **Produtos**, **Vendas** e **Gestão de Usuários**.
      * Registre movimentações no **Módulo Financeiro** e visualize os **Relatórios Avançados** e gráficos.
      * Gere recibos de vendas e relatórios em PDF.

-----
