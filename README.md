# Funil de Vendas Simples para Consultoria Financeira

Este projeto implementa um funil de vendas básico para captação de leads interessados em consultoria financeira, oferecendo uma isca digital (guia gratuito) e um quiz interativo.

🚀 **Funcionalidades**
* **Página de Captação (Landing Page):** Coleta informações de contato em troca de um guia gratuito.
* **Página de Agradecimento:** Confirma o envio do guia e oferece próximos passos.
* **Processamento de Leads:** Valida, salva dados no banco de dados e envia e-mails.
* **Quiz de Diagnóstico Financeiro:** Permite ao usuário autoavaliar a saúde financeira do negócio, com resultados salvos no banco de dados.

🛠️ **Tecnologias Utilizadas**
* **Front-end:** HTML5, CSS3, JavaScript (Fetch API para requisições assíncronas).
* **Back-end:** PHP 8.1 (para processamento de formulários e interação com DB).
* **Banco de Dados:** MySQL (gerenciado via PhpMyAdmin).
* **E-mails:** PHPMailer (para envio de e-mails via SMTP).

📦 **Estrutura do Projeto**

.
├── css/                      # Arquivos CSS para estilização
├── favicon/                  # Ícones do site (favicons)
├── images/                   # Imagens utilizadas no site
├── js/                       # Arquivos JavaScript (lógica do quiz, etc.)
├── legal/                    # Páginas de políticas (privacidade, termos de uso)
│   ├── politica_privacidade.html
│   └── termos_uso.html
├── pdf/                      # Arquivos PDF (como o guia gratuito)
├── php/
│   ├── config.php            # Configurações de banco de dados e e-mail (EXCLUÍDO DO GIT!)
│   ├── processa_lead.php     # Processa formulário da Landing Page (salva lead, envia e-mail)
│   └── save_quiz_data.php    # Salva dados do quiz no banco de dados
├── sql/                      # Scripts SQL para criação do banco de dados e tabelas
├── templates/                # Templates para e-mails ou outras finalidades
│   └── email_marketing.html
├── vendor/                   # Bibliotecas de terceiros (como PHPMailer)
│   └── PHPMailer-master/     # Contém a biblioteca PHPMailer
├── index.html                # Landing Page principal
├── obrigado.html             # Página de Agradecimento após submissão de formulário
├── quiz.html                 # Página do Quiz de Diagnóstico Financeiro
└── Readme.md                 # Este arquivo


⚙️ **Como Configurar e Rodar**

1.  **Clone o Repositório:**

    ```bash
    git clone [URL_DO_SEU_REPOSITORIO]
    cd funilVendas
    ```

2.  **Configurar o Banco de Dados MySQL:**
    * Crie um banco de dados chamado `funilVendas`.
    * **Opção A (Recomendada):** Se você tiver um arquivo `.sql` dentro da pasta `sql/` (ex: `sql/schema.sql`), importe-o usando o PhpMyAdmin ou via linha de comando (`mysql -u seu_usuario -p funilVendas < sql/schema.sql`).
    * **Opção B (Manual):** Crie as tabelas `leads` e `respostas_quiz` manualmente. Exemplos de sintaxe (adapte conforme necessário):

        ```sql
        -- Tabela para Leads da Landing Page
        CREATE TABLE leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            telefone VARCHAR(20),
            data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Tabela para Respostas do Quiz
        CREATE TABLE respostas_quiz (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            origem VARCHAR(100),
            porte_negocio INT,
            tempo_operacao INT,
            setor_negocio VARCHAR(100),
            todas_respostas LONGTEXT, -- Armazena JSON das respostas, use JSON para MySQL 5.7+
            score INT,
            qualificacao VARCHAR(255),
            data_submissao DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        ```

3.  **Configurar Credenciais (php/config.php):**
    * O arquivo `php/config.php` **NÃO está incluído no repositório GitHub** por conter informações sensíveis.
    * Na pasta `php/`, crie um novo arquivo chamado `config.php`.
    * Adicione o seguinte conteúdo, substituindo os valores pelos seus dados reais:

        ```php
        <?php
        // Configurações do Banco de Dados
        define('DB_SERVER', 'localhost');
        define('DB_USERNAME', 'seu_usuario_mysql'); // Ex: root
        define('DB_PASSWORD', 'sua_senha_mysql');   // Ex: (sua senha do MySQL)
        define('DB_NAME', 'funilVendas');

        // Configurações do PHPMailer (para o envio de e-mails via SMTP)
        define('MAIL_HOST', 'smtp.seuservidor.com'); // Ex: smtp.gmail.com, smtp.office365.com
        define('MAIL_USERNAME', 'seu_email@dominio.com'); // O endereço de e-mail que enviará as mensagens
        define('MAIL_PASSWORD', 'sua_senha_email');      // A senha do e-mail
        define('MAIL_PORT', 587); // Porta SMTP (comum: 587 para TLS/STARTTLS, 465 para SSL)
        define('MAIL_FROM_EMAIL', 'seu_email@dominio.com'); // Mesmo que MAIL_USERNAME ou outro e-mail
        define('MAIL_FROM_NAME', 'Sua Empresa'); // Nome que aparecerá como remetente
        ```

4.  **PHPMailer (Dependência de E-mail):**
    * A biblioteca PHPMailer já está incluída neste repositório na pasta `vendor/PHPMailer-master/`. Nenhuma instalação manual adicional é necessária após clonar o projeto.

5.  **Iniciar o Servidor PHP:**
    * Navegue até a pasta raiz do projeto (`funilVendas`) no seu terminal.
    * Execute o comando para iniciar o servidor de desenvolvimento PHP:
        ```bash
        php -S 0.0.0.0:8000
        ```

6.  **Acessar o Projeto:**
    * Abra seu navegador e vá para:
        * `http://localhost:8000/index.html` (para acessar do seu próprio computador)
        * `http://[SEU_IP_NA_REDE_LOCAL]:8000/index.html` (para acessar de outros dispositivos na mesma rede, ex: `http://192.168.0.59:8000/index.html`)
    * Você também pode acessar diretamente a página do quiz: `http://localhost:8000/quiz.html` ou `http://[SEU_IP_NA_REDE_LOCAL]:8000/quiz.html`.

📈 **Próximas Implementações (Sugestões de Melhoria)**
* **Otimização do Banco de Dados:** Migrar `todas_respostas` para o tipo `JSON` (MySQL 5.7+), adicionar índices e refinar tipos de dados.
* **Validação de Formulários:** Implementar validação de entrada mais robusta no lado do servidor (além da validação JS).
* **Gerenciamento de Versões do Quiz:** Adicionar um campo `quiz_version` na tabela para rastrear mudanças nas perguntas ao longo do tempo.
* **Dashboards de Análise:** Criar painéis ou integrar com ferramentas de BI para visualizar os dados coletados e as conversões do funil.
* **Melhoria do Envio de E-mail:** Implementar templates de e-mail mais dinâmicos e personalizar conteúdo.
* **Segurança:** Adicionar medidas de segurança como proteção contra CSRF (Cross-Site Request Forgery) e SQL Injection (usar prepared statements com PDO ou MySQLi).
* **Configuração de Ambiente:** Utilizar variáveis de ambiente (`.env` files) para configurações mais complexas e seguras.
## Demonstração do Fluxo

Veja abaixo o fluxo completo de interação do funil de vendas:

![Fluxo Completo do Funil]
---
