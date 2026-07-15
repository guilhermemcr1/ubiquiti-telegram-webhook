# Webhook UniFi para Telegram

> [!WARNING]
> Projeto experimental em desenvolvimento ativo. Os formatos dos eventos foram mapeados a partir de amostras reais e podem mudar entre versões do UniFi Network.

Endpoint simples em PHP para receber webhooks do **UniFi Network Alarm Manager**, autenticar a requisição com Bearer Token, registrar o payload e encaminhar o alerta para um chat ou grupo do Telegram.

## Eventos mapeados

| Classe | Evento |
| --- | --- |
| `107` | Internet restaurada |
| `112` | Alta latência detectada |
| `515` | Múltiplos dispositivos offline |
| `201` | Tentativa de intrusão detectada e bloqueada |
| `203` | Conexão bloqueada pelo firewall |
| `400` | Cliente Wi-Fi conectado |
| `401` | Cliente Wi-Fi desconectado |
| `407` | Alto tráfego de Internet detectado |

Eventos ainda não mapeados usam uma mensagem genérica, enviam o payload junto ao alerta do Telegram e são salvos integralmente no log. Isso permite observar os campos enviados pela Ubiquiti e aumentar a precisão dos formatadores. Se o payload ultrapassar o limite do Telegram, a mensagem será reduzida, mas o log continuará contendo os dados completos.

## Requisitos

- PHP 8.1 ou superior;
- extensões JSON e cURL; mbstring é recomendada para limites Unicode mais precisos;
- servidor web com HTTPS;
- bot e chat ou grupo do Telegram.

O projeto não usa Composer, framework, banco de dados ou `.env`.

## Instalação

Clone o repositório no servidor e crie a configuração local:

```bash
git clone https://github.com/SEU-USUARIO/ubiquiti-telegram-webhook.git
cd ubiquiti-telegram-webhook
cp config.example.php config.php
```

Edite `config.php`:

```php
<?php

declare(strict_types=1);

return [
    'telegram_bot_token' => 'TOKEN_DO_BOT',
    'telegram_chat_id' => 'CHAT_ID_DO_TELEGRAM',
    'bearer_token' => 'UMA_CHAVE_LONGA_E_ALEATORIA',
    'timezone' => 'America/Sao_Paulo',
    'log_file' => __DIR__ . '/logs/unifi-webhook.log',
];
```

Garanta que o usuário do PHP possa gravar em `logs/`:

```bash
chmod 775 logs
```

`config.php` e os arquivos `.log` já estão ignorados pelo Git.

## Configuração do Telegram

1. Converse com `@BotFather` no Telegram, crie um bot e copie o token.
2. Adicione o bot ao chat ou grupo que receberá os alertas.
3. Envie uma mensagem no chat e consulte `https://api.telegram.org/botSEU_TOKEN/getUpdates` para localizar o `chat.id`.
4. Preencha `telegram_bot_token` e `telegram_chat_id` no `config.php`.

Em grupos, o ID normalmente é negativo.

## Configuração do UniFi

No **UniFi Network > Alarm Manager**, configure o webhook:

```text
Method: POST
Authentication: Bearer
Token: apenas a chave configurada em bearer_token
Content: Default Content
Content-Type: application/json
URL: https://SEU-DOMINIO/ubiquiti-telegram-webhook/webhook.php
```

Não inclua a palavra `Bearer` no campo do token. O UniFi monta o header `Authorization: Bearer TOKEN`.

Se o Apache não encaminhar esse header ao PHP, adicione uma das opções abaixo ao `.htaccess`:

```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

ou:

```apache
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.+)
RewriteRule .* - [E=HTTP_AUTHORIZATION:%1]
```

## Teste manual

Com o servidor local ativo em `php -S 127.0.0.1:8000`, execute:

```bash
curl -i -X POST \
  "http://127.0.0.1:8000/webhook.php" \
  -H "Authorization: Bearer SUA_CHAVE" \
  -H "Content-Type: application/json" \
  -d '{
    "app": "network",
    "deviceEventClassId": "400",
    "message": "Cliente de teste conectado.",
    "name": "WiFi Client Connected",
    "parameters": {
      "UNIFIclientAlias": "Telefone de teste",
      "UNIFIclientHostname": "telefone-teste",
      "UNIFIclientIp": "192.0.2.10",
      "UNIFIclientMac": "02:00:00:00:00:10",
      "UNIFIwifiName": "Wi-Fi de teste",
      "UNIFIconnectedToDeviceName": "Gateway de teste",
      "UNIFIconnectedToDeviceIp": "192.0.2.1",
      "UNIFIconnectedToDeviceModel": "UX7",
      "UNIFIWiFiRssi": "-54",
      "UNIFIwifiChannel": "64",
      "UNIFIwifiChannelWidth": "160",
      "UNIFIauthMethod": "wpapsk",
      "UNIFInetworkName": "Rede de teste",
      "UNIFInetworkVlan": "20",
      "UNIFIutcTime": "2026-07-10T18:51:30.157Z"
    },
    "severity": 1,
    "version": "10.4.57"
  }'
```

Resposta esperada:

```json
{
  "success": true,
  "message": "Webhook recebido e enviado ao Telegram."
}
```

## Logs e privacidade

Cada tentativa gera uma linha JSON em `logs/unifi-webhook.log`. Payloads válidos são armazenados completos para ajudar no mapeamento de novos eventos.

Esses dados podem conter IPs, endereços MAC, hostnames e nomes de dispositivos. Portanto:

- não publique o arquivo de log;
- anonimize payloads antes de abrir uma issue ou pull request;
- limpe ou arquive o log manualmente quando ele crescer demais;
- mantenha `config.php` fora do controle de versão.

Tokens e o header `Authorization` nunca são registrados pelo código.

## Adicionando um evento

1. Crie o formatador em `events/` retornando um array de linhas.
2. Carregue o arquivo com `require_once` em `events/index.php`.
3. Adicione a classe ao `match`, ao nome e ao ícone em `formatarEvento()`.
4. Adicione um payload anonimizado ao `test.php`.
5. Execute os testes e a análise sintática.

```bash
php test.php
find . -name '*.php' -exec php -l {} \;
```

## Estrutura

```text
├── webhook.php
├── config.example.php
├── helpers.php
├── telegram.php
├── test.php
├── events/
│   ├── index.php
│   ├── common.php
│   ├── wifi-connected.php
│   ├── wifi-disconnected.php
│   ├── high-traffic.php
│   ├── internet-restored.php
│   ├── high-latency.php
│   ├── multiple-devices-offline.php
│   ├── firewall-blocked.php
│   ├── threat-blocked.php
│   └── default.php
└── logs/
    └── .gitkeep
```

## Segurança

- publique o endpoint somente via HTTPS;
- use um Bearer Token longo, aleatório e diferente do token do Telegram;
- não versione `config.php` nem logs;
- mantenha o PHP e o UniFi Network atualizados.

## Licença

Distribuído sob a licença [MIT](LICENSE).
