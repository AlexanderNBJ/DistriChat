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


## 🧩 Princípios de Sistemas Distribuídos Aplicados
* **Transparência de Localização:** O front-end interage com endpoints lógicos, sem conhecimento da topologia física dos serviços ou de onde os dados estão armazenados.
* **Tolerância a Falhas (Fault Tolerance):** O sistema utiliza *fail-fast* no `RemoteAuthMiddleware`. Se o serviço de autenticação estiver indisponível, o sistema de chat interrompe a operação de escrita para garantir a integridade, retornando um erro controlado (HTTP 503).
* **Desacoplamento e Independência:** Cada serviço possui seu próprio ciclo de vida, banco de dados e escala, evitando que um erro em um domínio derrube o ecossistema inteiro.

```text
       [ Cliente / Front-end ]
              |
      (1) HTTP Auth / (3) WS Real-time
              v
    +-----------------------+          +-----------------------+
    |     auth-service      | <--(2)-- |     chat-service      |
    | (Port: 8000)          |  (HTTP)  | (Port: 8001 / 8080)   |
    +----------+------------+          +-----------+-----------+
               |                                   |
        [ DB districhat_auth ]             [ DB districhat_chat ]
                                                   |
                                            [ Redis Broker ]
```

---

## 🔄 Fluxos de Comunicação do Sistema

### 1. Fluxo de Autenticação Remota Inter-Serviços (HTTP Síncrono)
Para manter o `chat-service` totalmente independente, ele não consulta a tabela de utilizadores. O fluxo funciona assim:
* O Front-end dispara uma requisição HTTP para o `chat-service` enviando um `Bearer Token`.
* O `RemoteAuthMiddleware` intercepta a requisição e faz uma chamada síncrona `GET /api/user` utilizando a URL interna configurada para o contêiner do `auth-service`.
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
* **Base de Dados**: PostgreSQL (Persistência relacional em ambiente conteinerizado).
* **Ambiente**: Docker & Docker Compose.
* **Framework de Testes**: Pest PHP (Suite automatizada de testes unitários e de integração).
* **Testes de Carga**: Grafana k6.

---

## 🚀 Como Executar o Projeto via Docker Compose

Todo o ecossistema (serviços, base de dados PostgreSQL, broker Redis e servidor de WebSockets) está conteinerizado e configurado para subir em conjunto.

### Pré-requisitos
* Docker e Docker Compose instalados.

### Passos para Inicialização

1. Instale as dependências e configure os arquivos `.env` em ambos os serviços (`auth-service` e `chat-service`) apontando as conexões de banco para `postgres` e Redis para `redis`.

2. Suba o cluster a partir da raiz do projeto:
```bash
docker compose up -d --build
```

3. Execute as migrações e seeders para estruturar e popular os bancos de dados automaticamente:
```bash
docker exec -it districhat_auth php artisan migrate:fresh --seed --force
docker exec -it districhat_chat_api php artisan migrate:fresh --seed --force
```

A API do `auth-service` estará disponível na porta `8000` e a do `chat-service` na porta `8001`.

4. Prepare a interface do usuário (Frontend):
```bash
cd frontend
npm install
npm run build
```

---

## 🌐 Acesso ao Sistema

Após a inicialização, o ecossistema distribuído estará disponível nos seguintes endereços:

*   **Interface Web (Frontend):** [http://localhost:8002](http://localhost:8002)
*   **Microsserviço de Autenticação:** `http://localhost:8000`
*   **Microsserviço de Chat:** `http://localhost:8001`
*   **Servidor WebSocket (Reverb):** `ws://localhost:8080`

---

## 👥 Simulando a Escalabilidade Horizontal (Cluster de WebSockets)

Para testar o comportamento de escalabilidade e alta disponibilidade exigido nos critérios do projeto, a infraestrutura via Docker Compose já inicializa os serviços necessários apontando para o mesmo Broker (Redis).

Mesmo que os clientes estejam conectados em instâncias de servidores diferentes do cluster, a troca de mensagens ocorre com baixa latência em tempo real, pois os nós realizam a sincronia mútua via Redis Pub/Sub de forma transparente.

---

## 🧪 Suite de Testes Automatizados

O sistema foi blindado seguindo a pirâmide de testes recomendada:
* **Testes Unitários**: Validam o isolamento das regras de negócio do domínio e a persistência correta de modelos.
* **Testes de Integração**: Simulam as requisições HTTP falsificando a comunicação (*HTTP Mocking*) com o `auth-service` para atestar a segurança e a resposta do middleware.

Para rodar todos os testes implementados dentro do ambiente conteinerizado, execute:
```bash
docker exec -it districhat_chat_api php artisan test
```

---

## 📊 Testes de Carga e Estresse (k6)

Para validar a resiliência e a estabilidade da arquitetura sob concorrência, foi realizada uma bateria de testes de carga simulando múltiplos usuários virtuais (`VUs`) disparando mensagens simultaneamente através do **Grafana k6**.

Para reproduzir os testes integrados à rede do Docker, execute o comando abaixo a partir da raiz do projeto:
```bash
docker run --rm --network districhat_districhat_network -i grafana/k6 run - < bateria-testes.js
```

### Resultados Obtidos
* **Taxa de Sucesso**: 100% das requisições completadas com sucesso (HTTP 201 Created).
* **Falhas de Conexão**: 0.00% de erro de requisição (`http_req_failed`).
* **Estabilidade**: O desacoplamento e a validação remota via Middleware suportaram a concorrência sem degradação ou interrupção dos serviços, comprovando a robustez da arquitetura proposta.