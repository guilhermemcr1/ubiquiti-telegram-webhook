<?php

declare(strict_types=1);

function formatarThreatBlocked(array $payload): array
{
    $parametros = is_array($payload['parameters'] ?? null) ? $payload['parameters'] : [];
    $linhas = [];

    adicionarLinha($linhas, 'Origem', valorParametro($parametros, 'UNIFIsrcClientAlias'));
    adicionarLinha($linhas, 'Hostname', valorParametro($parametros, 'UNIFIsrcClientHostname'));
    adicionarLinha($linhas, 'Tipo/modelo', valorParametro($parametros, 'UNIFIsrcClientModel'));
    adicionarLinha($linhas, 'MAC de origem', valorParametro($parametros, 'UNIFIsrcClientMac'));
    adicionarLinha($linhas, 'IP de destino', valorParametro($parametros, 'dst'));
    adicionarLinha($linhas, 'Região de destino', valorParametro($parametros, 'UNIFIdstRegion'));
    adicionarLinha($linhas, 'Protocolo', protocoloComPortas($parametros));
    adicionarLinha($linhas, 'Aplicação', valorParametro($parametros, 'app'));
    adicionarLinha($linhas, 'Política', valorParametro($parametros, 'UNIFIpolicyName'));
    adicionarLinha($linhas, 'Tipo de política', valorParametro($parametros, 'UNIFIpolicyType'));
    adicionarLinha($linhas, 'Risco', valorParametro($parametros, 'UNIFIrisk'));
    adicionarLinha($linhas, 'Direção', valorParametro($parametros, 'UNIFIdirection'));
    adicionarLinha($linhas, 'Interface de origem', valorParametro($parametros, 'deviceInboundInterface'));
    adicionarLinha($linhas, 'Interface de destino', valorParametro($parametros, 'deviceOutboundInterface'));
    adicionarLinha($linhas, 'Zona de origem', valorParametro($parametros, 'UNIFIsrcZone'));
    adicionarLinha($linhas, 'Zona de destino', valorParametro($parametros, 'UNIFIdstZone'));
    adicionarLinha($linhas, 'Assinatura IDS/IPS', valorParametro($parametros, 'UNIFIipsSignature'));
    adicionarLinha($linhas, 'ID da assinatura', valorParametro($parametros, 'UNIFIipsSignatureId'));
    adicionarLinha($linhas, 'Total de pacotes', valorParametro($parametros, 'UNIFItotalPackets'));
    adicionarLinha($linhas, 'Total de bytes', valorParametro($parametros, 'UNIFItotalBytes'));
    adicionarLinha($linhas, 'Pacotes recebidos', valorParametro($parametros, 'UNIFIpacketsReceived'));
    adicionarLinha($linhas, 'Pacotes enviados', valorParametro($parametros, 'UNIFIpacketsSent'));
    adicionarLinha($linhas, 'Bytes recebidos', valorParametro($parametros, 'UNIFIbytesReceived'));
    adicionarLinha($linhas, 'Bytes enviados', valorParametro($parametros, 'UNIFIbytesSent'));
    adicionarLinha($linhas, 'Quantidade de fluxos', valorParametro($parametros, 'UNIFIflowCount'));

    return $linhas;
}
