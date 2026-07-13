<?php

declare(strict_types=1);

function formatarFirewallBlocked(array $payload): array
{
    $parametros = is_array($payload['parameters'] ?? null) ? $payload['parameters'] : [];
    $linhas = [];

    adicionarLinha($linhas, 'Origem', valorParametro($parametros, 'UNIFIsrcClientAlias'));
    adicionarLinha($linhas, 'Hostname', valorParametro($parametros, 'UNIFIsrcClientHostname'));
    adicionarLinha($linhas, 'Tipo/modelo', valorParametro($parametros, 'UNIFIsrcClientModel'));
    adicionarLinha($linhas, 'IP de origem', valorParametro($parametros, 'UNIFIsrcClientIp'));
    adicionarLinha($linhas, 'MAC de origem', valorParametro($parametros, 'UNIFIsrcClientMac'));
    adicionarLinha($linhas, 'IP de destino', valorParametro($parametros, 'dst'));

    adicionarLinha($linhas, 'Protocolo', protocoloComPortas($parametros));
    adicionarLinha($linhas, 'Aplicação', valorParametro($parametros, 'app'));
    adicionarLinha($linhas, 'Política', valorParametro($parametros, 'UNIFIpolicyName') ?? valorParametro($parametros, 'UNIFIfirewallPolicy'));
    adicionarLinha($linhas, 'Risco', valorParametro($parametros, 'UNIFIrisk'));
    adicionarLinha($linhas, 'Direção', valorParametro($parametros, 'UNIFIdirection'));
    adicionarLinha($linhas, 'Zona de origem', valorParametro($parametros, 'UNIFIsrcZone'));
    adicionarLinha($linhas, 'Zona de destino', valorParametro($parametros, 'UNIFIdstZone'));
    adicionarLinha($linhas, 'Pacotes', valorParametro($parametros, 'UNIFIpacketsReceived'));
    adicionarLinha($linhas, 'Bytes', valorParametro($parametros, 'UNIFIbytesReceived'));
    adicionarLinha($linhas, 'Duração do fluxo', valorParametro($parametros, 'UNIFIflowDuration'));
    adicionarLinha($linhas, 'Quantidade de fluxos', valorParametro($parametros, 'UNIFIflowCount'));

    return $linhas;
}
