<?php

declare(strict_types=1);

function formatarHighTraffic(array $payload): array
{
    $parametros = is_array($payload['parameters'] ?? null) ? $payload['parameters'] : [];
    $linhas = linhasClienteWifi($parametros);

    adicionarLinha($linhas, 'Período analisado', valorParametro($parametros, 'UNIFIreportingPeriod'));
    adicionarLinha($linhas, 'Mensagem original', limitarTexto(valorParametro($payload, 'message') ?? '', 2500));

    return $linhas;
}
