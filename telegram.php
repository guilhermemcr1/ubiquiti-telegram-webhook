<?php

declare(strict_types=1);

function enviarTelegram(array $config, string $mensagem): array
{
    $url = 'https://api.telegram.org/bot' . $config['telegram_bot_token'] . '/sendMessage';
    $payload = json_encode([
        'chat_id' => $config['telegram_chat_id'],
        'text' => $mensagem,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        return ['success' => false, 'status' => 500, 'error' => 'Não foi possível gerar a mensagem do Telegram.'];
    }

    $curl = curl_init($url);

    if ($curl === false) {
        return ['success' => false, 'status' => 500, 'error' => 'Não foi possível iniciar o cURL.'];
    }

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $payload,
    ]);

    $resposta = curl_exec($curl);
    $erroCurl = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($resposta === false) {
        return [
            'success' => false,
            'status' => $status ?: 500,
            'error' => $erroCurl !== '' ? 'Falha de comunicação cURL com o Telegram.' : 'Falha de comunicação com o Telegram.',
        ];
    }

    $json = json_decode($resposta, true);

    if ($status !== 200 || !is_array($json) || ($json['ok'] ?? false) !== true) {
        $descricao = is_array($json) && is_string($json['description'] ?? null)
            ? $json['description']
            : 'O Telegram rejeitou a mensagem.';

        return ['success' => false, 'status' => $status ?: 500, 'error' => $descricao];
    }

    return ['success' => true, 'status' => $status, 'error' => null];
}
