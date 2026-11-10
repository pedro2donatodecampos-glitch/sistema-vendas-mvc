# Vitrine PDV — Sistema de Vendas

Aplicação de ponto de venda em PHP 8.2+ com SQLite, arquitetura MVC e interface responsiva em português. O sistema oferece cadastro de clientes, carrinho de vendas com controle de estoque, histórico de vendas, cancelamento com reposição de estoque e exportação de relatórios CSV.

## Requisitos

- PHP 8.2 ou superior
- Extensões PDO e PDO_SQLite habilitadas
- Git (opcional, para clonar o projeto)

Não é necessário Composer, servidor de banco de dados ou configuração de credenciais.

## Instalação e execução (POP)

1. Instale o PHP e confirme as extensões: `php -m | grep -E 'PDO|pdo_sqlite'`.
2. Clone o projeto e entre na pasta:

   ```bash
   git clone https://github.com/pedro2donatodecampos-glitch/sistema-vendas-mvc.git
   cd sistema-vendas-mvc
   ```

3. Dê permissão de escrita ao usuário do PHP no diretório `storage/` (na primeira execução ele será criado automaticamente):

   ```bash
   mkdir -p storage
   chmod 775 storage
   ```

4. Inicie o servidor local:

   ```bash
   php -S localhost:8000 -t public public/index.php
   ```

5. Acesse <http://localhost:8000>. As tabelas SQLite e três produtos de demonstração são criados automaticamente em `storage/database.sqlite`.
6. Para implantação em hospedagem, configure o document root para a pasta `public/`, use PHP 8.2+ com PDO_SQLite e garanta permissão de escrita em `storage/`. Em produção, use HTTPS e um servidor web configurado para encaminhar as rotas ao `public/index.php`.

O banco e os dados ficam em `storage/database.sqlite`. Faça backup desse arquivo periodicamente; não o exponha pelo servidor web.

## Funcionalidades

- **Clientes:** listar, cadastrar, editar e excluir; nome obrigatório e validação de e-mail.
- **Carrinho:** adicionar produtos, alterar quantidades, remover itens, esvaziar e finalizar a venda. A finalização registra os itens e atualiza o estoque em uma transação atômica.
- **Vendas e relatórios:** consultar detalhes, filtrar por período e situação, ver totais, exportar CSV compatível com planilhas e cancelar uma venda, devolvendo os itens ao estoque.
- **Segurança básica:** consultas preparadas, escape de saída HTML, token CSRF nos formulários e proteção contra fórmulas executáveis no CSV.

## Arquitetura MVC

- **Model/Repository:** `app/Repositories/` concentra o acesso e a persistência de clientes, produtos e vendas no SQLite.
- **View:** `resources/views/` contém somente a apresentação PHP/HTML; o layout e o CSS comum estão em `resources/views/layout.php` e `public/assets/app.css`.
- **Controller:** `app/Controllers/` recebe as requisições, valida entradas e coordena repositórios e serviços.
- **Front Controller:** `public/index.php` é a única entrada HTTP, valida CSRF nas requisições POST e encaminha as rotas para os controllers por meio de `app/Core/Router.php`.
- **Service Layer:** `app/Services/SaleService.php` aplica as regras de finalização e cancelamento de vendas, mantendo operações de estoque e persistência dentro de transações.

## Padrões de projeto adotados

- **Singleton — `App\Core\Database`:** mantém uma única conexão PDO durante a requisição e centraliza a criação do banco e do esquema.
- **Repository — `app/Repositories/`:** separa consultas e gravações SQL dos controllers, oferecendo operações próprias para cada entidade.
- **Factory — `PaymentMethodFactory`:** valida e fornece as formas de pagamento permitidas antes que a venda seja persistida.
- **MVC e Front Controller:** organizam apresentação, coordenação de requisições e entrada HTTP, complementando os padrões de criação e persistência acima.

## Estrutura

```text
app/
  Controllers/      # Fluxo de clientes, vendas e relatórios
  Core/             # Banco, roteador, renderização e helpers
  Repositories/     # Persistência por entidade
  Services/         # Regras de negócio e fábrica de pagamento
public/
  assets/           # CSS e arquivos públicos
  index.php         # Front Controller
resources/views/    # Telas e layout
storage/            # Banco SQLite local (não versionado)
```
