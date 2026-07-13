<?php

declare(strict_types=1);

function formatarEventoGenerico(array $payload): array
{
    $linhas = [];
    adicionarLinha($linhas, 'Mensagem do UniFi', limitarTexto(valorParametro($payload, 'message') ?? 'Sem mensagem.', 600));

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );

    if ($json !== false) {
        $payloadTelegram = limitarTexto($json, 2800);
        $linhas[] = '';
        $linhas[] = '<b>Payload recebido:</b>';
        $linhas[] = '<pre>' . escaparTelegram($payloadTelegram) . '</pre>';
    }

    return $linhas;
}
