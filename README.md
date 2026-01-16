# Teste Técnico - Desenvolvedor Acessórias (2026)

Sistema web para gerenciamento de lançamentos financeiros (créditos e débitos)

## Sobre o Projeto

Aplicação desenvolvida em PHP puro com MySQL, utilizando jQuery e Bootstrap para o frontend. O sistema permite cadastrar, listar e excluir lançamentos financeiros, com validação automática de saldo negativo.

## Tecnologias Utilizadas

- **Backend:** PHP 8.2+ (MySQLi, BCMath)
- **Frontend:** HTML5, CSS3, JavaScript (jQuery 3.7.1)
- **Framework CSS:** Bootstrap 5.3.8
- **Banco de Dados:** MySQL 8.0+ / MariaDB 10.5+
- **Containerização:** Docker & Docker Compose (opcional)


## Requisitos

### Instalação Local
- PHP 8.2 ou superior
- MySQL 8.0+ ou MariaDB 10.5+
- Apache/Nginx ou PHP built-in server

### Instalação Docker
- Docker 20.10+
- Docker Compose 2.0+

##  Instalação

### Opção 1: Execução local (Apache + PHP + MySQL)

A aplicação pode ser executada em qualquer ambiente com Apache, PHP e MySQL/MariaDB.
Ferramentas como XAMPP ou WAMP, etc

1. **Clonar o repositório:**
   ```bash
   git clone https://github.com/kevinwallace-dev/teste-dev-acessorias.git
   cd "teste-dev-acessorias"
   ```

2. **Importar o banco de dados:**
   ```bash
   mysql -u root -p < database.sql
   ```
   O script cria automaticamente o database `financeiro` e a tabela necessária.

3. **Configurar conexão (se necessário):**
   Edite o arquivo `config.php` e ajuste as credenciais do banco de dados:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'financeiro');
   ```

4. **Acessar a aplicação:**
   - Coloque o projeto na pasta pública do servidor (ex.: `htdocs`)
   - Acesse: `http://localhost/www/teste-dev-acessorias/`

### Opção 2: Docker

1. **Subir os containers:**
   ```bash
   docker compose up --build
   ```

2. **Acessar a aplicação:**
   ```
   http://localhost:8080
   ```

3. **Recriar o banco (se necessário):**
   ```bash
   docker compose down -v
   docker compose up --build
   ```

