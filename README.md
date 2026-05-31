# DistriChat 💬

Sistema de chat em tempo real desenvolvido como Trabalho Final para a disciplina de **Sistemas Distribuídos** no **CEFET-MG**, sob a orientação da **Professora Michelle Hanne (2026/1)**. O projeto demonstra na prática conceitos fundamentais de sistemas distribuídos modernos, com foco em alta disponibilidade, isolamento de escopo, escalabilidade horizontal e comunicação assíncrona baseada em eventos.

---

## 🎯 Objetivos do Projeto

O propósito central do DistriChat é construir uma plataforma de comunicação confiável e eficiente, mitigando gargalos comuns de infraestrutura por meio de uma arquitetura distribuída projetada para garantir:
* **Alta Disponibilidade**: Garantir a continuidade do serviço de mensagens e autenticação mesmo se uma ou mais instâncias falharem no cluster.
* **Comunicação em Tempo Real**: Garantir baixa latência na entrega das mensagens entre os utilizadores.
* **Escalabilidade Horizontal**: Permitir que novas instâncias do servidor de aplicação e de WebSockets sejam adicionadas dinamicamente para suportar picos de carga de utilizadores simultâneos.

---

## 🏗️ Estrutura de Sistemas Distribuídos (Arquitetura)

O ecossistema do DistriChat foi desacoplado seguindo o modelo de **Microsserviços**, quebrando a aplicação em dois domínios totalmente independentes e isolados:

1. **`auth-service`**: Microsserviço isolado responsável pelo ciclo de vida do utilizador, efetuando o registo, validação de credenciais de login e emissão de tokens de acesso.
2. **`chat-service`**: Microsserviço responsável pela lógica de negócio do chat (envio, recebimento, armazenamento do histórico de mensagens e difusão em tempo real).

### Componentes de Infraestrutura Distribuída
* **Banco de Dados Isolado**: Cada microsserviço possui o seu próprio banco de dados, evitando acoplamento na camada de dados e otimizando a persistência das tabelas relacionais de utilizadores e mensagens.
* **Laravel Reverb (Servidor WebSocket)**: Servidor de alto desempenho embutido que gerencia conexões persistentes bidirecionais (*stateful*) diretamente com o Front-end.
* **Redis como Broker (Pub/Sub)**: Funciona como o intermediário distribuído (*Horizontal Scaling Broker*). Quando múltiplas instâncias do `chat-service` e do Reverb estão no ar, o Redis replica todos os eventos de mensagens entre os nós do cluster, permitindo que utilizadores conectados em servidores físicos diferentes conversem perfeitamente.

---

## 🔄 Fluxos de Comunicação do Sistema

### 1. Fluxo de Autenticação Remota Inter-Serviços (HTTP Síncrono)
Para manter o `chat-service` totalmente independente, ele não consulta a tabela de utilizadores. O fluxo funciona assim:
* O Front-end dispara uma requisição HTTP para o `chat-service` enviando um `Bearer Token`.
* O `RemoteAuthMiddleware` intercepta a requisição e faz uma chamada síncrona `GET /api/user` para o `auth-service`.
* O `auth-service` valida o token e retorna os dados do utilizador autenticado (`id`, `name`, `email`).
* O Middleware injeta esses dados na requisição atual e permite o prosseguimento do fluxo. Se o `auth-service` estiver offline, o sistema responde automaticamente com `503 Service Unavailable`.

### 2. Fluxo de Envio e Transmissão em Tempo Real (Híbrido)
O sistema suporta mensagens privadas (1:1) e mensagens em salas públicas (1:N). O ciclo de vida de uma mensagem compreende:
* **Entrada**: O utilizador envia uma mensagem HTTP POST para o endpoint `/api/messages`.
* **Persistência**: O `MessageController` valida os dados e guarda a mensagem na base de dados para manter o histórico.
* **Broadcast**: O backend dispara o evento distribuído `MessageSent`.
* **Propagação Local e Global**: O Laravel publica esse evento no canal correspondente do **Redis**. Todas as instâncias do *Laravel Reverb* escutam o Redis e repassam o pacote instantaneamente através da conexão WebSocket aberta para o Front-end dos utilizadores destinatários.

---

## 🛠️ Stack Tecnológica

* **Linguagem de Programação**: PHP 8.2+ (Framework Laravel 11).
* **Servidor de WebSockets**: Laravel Reverb.
* **Broker Distribuído**: Redis (Mecanismo Pub/Sub).
* **Base de Dados**: SQLite / PostgreSQL (Persistência relacional).
* **Framework de Testes**: Pest PHP (Suite automatizada de testes unitários e de integração).

---

## 🚀 Como Executar o Projeto Localmente

### Pré-requisitos
* PHP 8.2 ou superior instalado localmente.
* Composer (Gerenciador de dependências do PHP).
* Servidor Redis ativo (via gerenciador de pacotes ou Docker: `docker run -d -p 6379:6379 redis`).

### Configuração do `chat-service`

1. Entre no diretório correspondente:
```bash
cd DistriChat/chat-service
```

2. Instale as dependências de backend e o driver do Redis:
```bash
composer install
```


3. Crie e configure o arquivo `.env`:
```bash
cp .env.example .env
```


Certifique-se de ativar o escalonamento horizontal via Redis configurando as seguintes chaves dentro do seu `.env`:
```env
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

REVERB_SCALING_ENABLED=true
REVERB_SCALING_DRIVER=redis

```


4. Execute as migrações para preparar o banco de dados de mensagens:


```bash
php artisan migrate

```



---

## 👥 Simulando a Escalabilidade Horizontal (Cluster de WebSockets)

Para testar o comportamento de escalabilidade e alta disponibilidade exigido nos critérios do projeto, você pode inicializar múltiplos servidores WebSocket paralelos apontando para o mesmo Broker (Redis):

* **Instância do Servidor HTTP da API:**
```bash
php artisan serve --port=8000

```


* **Instância 01 do Servidor WebSocket (Porta 8080):**
```bash
php artisan reverb:start --port=8080

```


* **Instância 02 do Servidor WebSocket (Porta 8081):**
```bash
php artisan reverb:start --port=8081

```



Mesmo que o Utilizador A esteja conectado na Porta 8080 e o Utilizador B na Porta 8081, a troca de mensagens ocorrerá com baixa latência em tempo real, pois os servidores realizam a sincronia mútua via Redis Pub/Sub.

---

## 🧪 Suite de Testes Automatizados

O sistema foi blindado seguindo a pirâmide de testes recomendada no plano de testes oficial:

* 
**Testes Unitários**: Validam o isolamento das regras de negócio do domínio e a persistência correta de modelos.


* 
**Testes de Integração**: Simulam as requisições HTTP falsificando a comunicação (*HTTP Mocking*) com o `auth-service` para atestar a segurança e a resposta do middleware.



Para rodar todos os testes implementados e garantir a integridade da aplicação, execute:

```bash
php artisan test

```
