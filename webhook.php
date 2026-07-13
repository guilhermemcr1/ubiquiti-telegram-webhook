<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/telegram.php';
require_once __DIR__ . '/events/index.php';

$configuracao = __DIR__ . '/config.php';

if (!is_file($configuracao)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Arquivo config.php não encontrado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$config = require $configuracao;
$chavesObrigatorias = ['telegram_bot_token', 'telegram_chat_id', 'bearer_token', 'timezone', 'log_file'];
$configValida = is_array($config) && array_diff($chavesObrigatorias, array_keys($config)) === [];

foreach ($chavesObrigatorias as $chave) {
    $configValida = $configValida
        && is_string($config[$chave] ?? null)
        && trim($config[$chave]) !== '';
}

if (!$configValida) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Configuração inválida.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    new DateTimeZone($config['timezone']);
    date_default_timezone_set($config['timezone']);
} catch (Throwable) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Timezone inválido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$authorization = obterAuthorization();
$log = [
    'recebido_em' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'metodo' => $_SERVER['REQUEST_METHOD'] ?? null,
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
    'authorization_recebido' => $authorization !== '',
];

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    finalizarWebhook($config['log_file'], $log, false, 'Método não permitido.', 405);
}

if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $resultado)) {
    $log['autenticacao'] = 'ausente ou inválida';
    finalizarWebhook($config['log_file'], $log, false, 'Bearer Token ausente ou inválido.', 401);
}

if (!hash_equals((string) $config['bearer_token'], $resultado[1])) {
    $log['autenticacao'] = 'recusada';
    finalizarWebhook($config['log_file'], $log, false, 'Bearer Token ausente ou inválido.', 401);
}

$log['autenticacao'] = 'aceita';
$conteudo = file_get_contents('php://input');

if ($conteudo === false || trim($conteudo) === '') {
    finalizarWebhook($config['log_file'], $log, false, 'Conteúdo da requisição vazio.', 400);
}

try {
    $payload = json_decode($conteudo, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    finalizarWebhook($config['log_file'], $log, false, 'JSON inválido.', 400);
}

if (!is_array($payload)) {
    finalizarWebhook($config['log_file'], $log, false, 'O JSON deve conter um objeto.', 400);
}

$log['payload'] = $payload;

try {
    $telegram = enviarTelegram($config, formatarEvento($payload));
} catch (Throwable $erro) {
    $log['erro_interno'] = $erro->getMessage();
    finalizarWebhook($config['log_file'], $log, false, 'Erro interno ao processar o webhook.', 500);
}

$log['telegram_status'] = $telegram['status'];

if (!$telegram['success']) {
    $log['telegram_erro'] = $telegram['error'];
    finalizarWebhook($config['log_file'], $log, false, 'Falha ao enviar a mensagem ao Telegram.', 502);
}

finalizarWebhook($config['log_file'], $log, true, 'Webhook recebido e enviado ao Telegram.', 200);
