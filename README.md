# Vitrine PDV — Sistema de Vendas em MVC

Sistema de **Ponto de Venda (PDV)** em **PHP + SQLite**, organizado em **MVC** e com **Design Patterns**. É a continuação do PDV da aula: o formulário "Cadastro de Produtos" foi levado para o MVC e foram criadas as telas de **Clientes**, **Carrinho de Vendas** e **Relatórios de Vendas**.

---

## 1. Arquitetura MVC

**MVC** (Model – View – Controller) separa o sistema em três partes, cada uma com uma função. Pense em um restaurante:

| Restaurante | MVC | Pasta no projeto |
|---|---|---|
| Garçom: recebe o pedido e leva à cozinha | **Controller** | `app/Controllers/` |
| Cozinha e despensa: regras e ingredientes | **Model** | `app/Models/`, `app/Repositories/`, `app/Services/` |
| Prato servido na mesa | **View** | `resources/views/` |

**Caminho de um clique** (ex.: salvar um cliente):

1. O navegador envia o formulário para `public/index.php` (porta de entrada única).
2. O `Router` escolhe o Controller certo (`CustomerController`).
3. O Controller pede ao **Model** (`Customer`) para validar os dados.
4. O **Repository** grava no banco SQLite.
5. O Controller entrega os dados à **View**, que mostra a tela atualizada.

```text
app/Controllers/   → CustomerController, ProductController, CartController, ReportController
app/Models/        → Customer, Product, Cart (validações e regras)
app/Repositories/  → acesso ao banco (SQL)
app/Services/      → SaleService (vendas) e PaymentMethodFactory (pagamentos)
resources/views/   → telas HTML
public/index.php   → porta de entrada e rotas
```

---

## 2. Design Patterns adotados

| Padrão | Onde | Para que serve |
|---|---|---|
| **Singleton** | `app/Core/Database.php` | Uma única conexão com o banco, reaproveitada por todo o sistema. |
| **Repository** | `app/Repositories/` | Único lugar com SQL; os Controllers só pedem `all()`, `find()`, `save()`, `delete()`. |
| **Factory** | `app/Services/PaymentMethodFactory.php` | Cria o objeto de pagamento certo (Dinheiro, Crédito, Débito, PIX) a partir do nome escolhido. |
| **Service Layer** | `app/Services/SaleService.php` | Regras da venda: confere estoque e validade, grava e baixa o estoque em uma transação; cancela devolvendo o estoque. |
| **Front Controller** | `public/index.php` + `app/Core/Router.php` | Toda requisição entra por um único arquivo, que verifica a segurança e direciona ao Controller. |

---

## 3. CRUDs das telas

**CRUD** = **C**riar, **L**er, **A**tualizar e **E**xcluir.

| Tela | Criar | Ler | Atualizar | Excluir |
|---|---|---|---|---|
| **1 - Clientes** | + Novo cliente | Lista de clientes | Editar | Excluir |
| **2 - Carrinho** | Adicionar produto (+) | Painel "Seu carrinho" | Alterar quantidade | Remover item / Esvaziar |
| **3 - Relatórios** | Venda criada ao finalizar o carrinho | Lista, filtros, detalhes e exportar CSV | Cancelar venda (devolve estoque) | Excluir venda cancelada |
| **Produtos** (aula) | + Novo produto (com imagem) | Lista de produtos | Editar | Excluir |

---

## 4. POP — Instalação do sistema

**Objetivo:** colocar o sistema para funcionar no computador.
**Necessário:** PHP 8.2 ou superior (com `pdo_sqlite` e `fileinfo`). Não precisa instalar banco de dados.

### Passo 1 — Instalar o PHP

- **Windows:** instale o **XAMPP** (<https://www.apachefriends.org>). Abra `C:\xampp\php\php.ini` e confira se estas linhas **não** começam com `;`:
  ```ini
  extension=pdo_sqlite
  extension=sqlite3
  extension=fileinfo
  ```
- **Linux:** `sudo apt install php-cli php-sqlite3 git`
- **macOS:** `brew install php git`

Confirme no terminal: `php -v` (deve mostrar 8.2 ou maior).

### Passo 2 — Baixar o projeto

```bash
git clone https://github.com/pedro2donatodecampos-glitch/sistema-vendas-mvc.git
cd sistema-vendas-mvc
```
Ou, no GitHub, clique em **Code → Download ZIP** e extraia.

### Passo 3 — Iniciar o sistema

Dentro da pasta do projeto:
```bash
php -S localhost:8000 -t public public/index.php
```
No Windows com XAMPP:
```bat
C:\xampp\php\php.exe -S localhost:8000 -t public public/index.php
```
Deixe a janela aberta. Para desligar: `Ctrl + C`.

### Passo 4 — Acessar

Abra <http://localhost:8000>. Na primeira vez, o banco `storage/database.sqlite` é criado sozinho com 3 produtos de exemplo.

✅ **Pronto:** o menu mostra **Carrinho, Clientes, Produtos e Relatórios**.

### Problemas comuns

| Erro | Solução |
|---|---|
| `could not find driver` | Ative `extension=pdo_sqlite` no `php.ini`. |
| `Address already in use` | Use outra porta: `php -S localhost:8080 -t public public/index.php` |
| Erro ao salvar banco/imagem (Linux/macOS) | `chmod 775 storage public/uploads` |
