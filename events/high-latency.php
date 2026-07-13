<?php

declare(strict_types=1);

function formatarHighLatency(array $payload): array
{
    $parametros = is_array($payload['parameters'] ?? null) ? $payload['parameters'] : [];
    $linhas = linhasConexaoWan($parametros);
    $latencia = valorParametro($parametros, 'UNIFIwanLatency');

    adicionarLinha($linhas, 'Latência', $latencia !== null && is_numeric($latencia) ? "{$latencia} ms" : $latencia);

    return [...$linhas, ...linhasEquipamentoUnifi($parametros)];
}
