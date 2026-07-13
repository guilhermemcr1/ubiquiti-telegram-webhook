<?php

declare(strict_types=1);

function formatarInternetRestored(array $payload): array
{
    $parametros = is_array($payload['parameters'] ?? null) ? $payload['parameters'] : [];
    $linhas = linhasConexaoWan($parametros);

    adicionarLinha($linhas, 'Tempo indisponível', valorParametro($parametros, 'UNIFIreportedDuration'));

    return [...$linhas, ...linhasEquipamentoUnifi($parametros)];
}
