<?php

declare(strict_types=1);

function formatarMultipleDevicesOffline(array $payload): array
{
    $parametros = is_array($payload['parameters'] ?? null) ? $payload['parameters'] : [];
    $linhas = [];

    adicionarLinha($linhas, 'Host', valorParametro($parametros, 'UNIFIhost'));
    adicionarLinha($linhas, 'Dispositivos offline', valorParametro($parametros, 'UNIFIdeviceList'));
    adicionarLinha($linhas, 'Quantidade', valorParametro($parametros, 'cnt'));
    adicionarLinha($linhas, 'Referência', valorParametro($parametros, 'UNIFIreference'));

    return $linhas;
}
