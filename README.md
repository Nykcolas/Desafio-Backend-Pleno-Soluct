
# Desafio Backend Pleno - Soluct API

Esta é uma API RESTful completa para gerenciamento de tarefas, desenvolvida como parte do desafio técnico para a vaga de Desenvolvedor Backend Pleno na Soluct. A aplicação foi construída com Laravel, utilizando um ambiente de desenvolvimento containerizado com Docker e Laravel Sail.

## 🚀 Funcionalidades

  - ✅ **Autenticação de usuários:** Sistema completo com registro, login e logout via Laravel Sanctum.
  - ✅ **CRUD de Tarefas:** Gerenciamento completo (Criar, Ler, Atualizar, Deletar) de tarefas.
  - ✅ **Política de Posse:** Um usuário só pode visualizar e gerenciar as tarefas que ele mesmo criou.
  - ✅ **Histórico de Alterações:** Todas as alterações em uma tarefa são registradas automaticamente em uma tabela de histórico (via `Observer`).
  - ✅ **Webhooks Assíncronos:** O sistema dispara um webhook para uma URL externa a cada alteração em uma tarefa, sem impactar a performance da requisição principal (via `Jobs` e `Queues`).
  - ✅ **Filtragem Avançada:** O endpoint de listagem de tarefas suporta filtros complexos (por status, título, intervalo de datas), ordenação e paginação.
  - ✅ **Testes Automatizados:** Suíte de testes de feature (`Feature Tests`) com PHPUnit para garantir a qualidade e o funcionamento correto das principais funcionalidades.
  - ✅ **Documentação Interativa:** A API é totalmente documentada seguindo o padrão OpenAPI (Swagger).

## 🛠️ Tecnologias Utilizadas

  - **Backend:** Laravel
  - **Linguagem:** PHP
  - **Banco de Dados:** PostgreSQL
  - **Filas (Queues):** Redis (configurado no ambiente, driver de DB utilizado)
  - **Ambiente de Desenvolvimento:** Docker com Laravel Sail
  - **Testes:** PHPUnit
  - **Documentação:** OpenAPI (Swagger) via `l5-swagger`

## ⚙️ Pré-requisitos

Para rodar este projeto localmente, você precisará ter instalado na sua máquina:

  - Docker
  - Docker Compose

## 🚀 Como Rodar Localmente (Passo a Passo)

Siga os passos abaixo para configurar e executar a aplicação no seu ambiente.

**1. Clonar o Repositório**

```bash
git clone https://github.com/Nykcolas/Desafio-Backend-Pleno-Soluct.git
cd Desafio-Backend-Pleno-Soluct
```

**2. Configurar o Ambiente**
Copie o arquivo de ambiente de exemplo.

```bash
cp .env.example .env
```

*Abra o arquivo `.env` e certifique-se de que as variáveis `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` estão preenchidas. O Laravel Sail usará estes valores para configurar o container do PostgreSQL.*

**3. Instalar as Dependências**
Execute o Composer dentro do container para instalar as dependências do PHP.

```bash
composer install
```

**4. Iniciar os Containers**
Use o Laravel Sail para construir e iniciar os containers Docker definidos no `docker-compose.yml`.

```bash
sail up -d
```

**5. Gerar a Chave da Aplicação**

```bash
sail artisan key:generate
```

## 🗄️ Configuração do Banco de Dados

Para criar as tabelas do banco de dados e populá-las com dados de teste (usuários e tarefas), execute o seguinte comando:

```bash
sail artisan migrate:fresh --seed
```

Este comando irá:

1.  Apagar todas as tabelas existentes.
2.  Executar todas as `migrations` para recriar a estrutura do banco.
3.  Executar todos os `seeders` para popular o banco com dados falsos.

## 🔄 Executando a Fila de Jobs (Para Webhooks)

A funcionalidade de webhook é assíncrona e depende de um "queue worker" para processar os jobs. Para iniciar o worker, execute o seguinte comando em um **novo terminal**:

```bash
sail artisan queue:work
```

Deixe este terminal aberto para ver os jobs sendo processados em tempo real sempre que você criar ou atualizar uma tarefa.

## ✅ Executando os Testes Automatizados

Para garantir que toda a aplicação está funcionando corretamente, você pode rodar a suíte de testes automatizados com o seguinte comando:

```bash
sail artisan test
```

## 📖 Documentação dos Endpoints (Swagger)

A API possui uma documentação interativa completa gerada com Swagger.

**1. Gere a Documentação**
Caso precise atualizar a documentação após alguma alteração no código, rode:

```bash
sail artisan l5-swagger:generate
```

**2. Acesse a Documentação**
Com a aplicação rodando, acesse a seguinte URL no seu navegador:
[http://localhost/api/documentation](http://localhost/api/documentation)

Na interface do Swagger, você poderá ver todos os endpoints, seus parâmetros, corpos de requisição e respostas de exemplo. com isso você tem tudo pra testar toda a aplicação. Divirta-se 😉.

**Nota sobre Webhooks**
-----

> **Nota sobre Webhooks:** Este projeto dispara webhooks para uma URL externa sempre que tarefas são criadas ou atualizadas. Para testar esta funcionalidade, você **precisa** definir a variável `WEBHOOK_TARGET_URL` no seu arquivo `.env`.
>
> Uma forma fácil de obter uma URL de teste gratuita é acessando o site [webhook.site](https://webhook.site/).
>
> Se esta variável não for definida, a aplicação funcionará normalmente, mas os webhooks não serão enviados.
>
> **Exemplo de linha a ser adicionada no `.env`:**
>
> ```dotenv
> WEBHOOK_TARGET_URL=https://webhook.site/SUA-URL-DE-TESTE-AQUI
> ```
