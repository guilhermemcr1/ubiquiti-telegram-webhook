<?php

declare(strict_types=1);

function adicionarLinha(array &$linhas, string $rotulo, ?string $valor): void
{
    if ($valor !== null && $valor !== '') {
        $linhas[] = '<b>' . escaparTelegram($rotulo) . ':</b> ' . escaparTelegram($valor);
    }
}

function linhasClienteWifi(array $parametros): array
{
    $linhas = [];

    adicionarLinha($linhas, 'Cliente', valorParametro($parametros, 'UNIFIclientAlias'));
    adicionarLinha($linhas, 'Hostname', valorParametro($parametros, 'UNIFIclientHostname'));
    adicionarLinha($linhas, 'IP', valorParametro($parametros, 'UNIFIclientIp'));
    adicionarLinha($linhas, 'MAC', valorParametro($parametros, 'UNIFIclientMac'));
    adicionarLinha($linhas, 'Wi-Fi', valorParametro($parametros, 'UNIFIwifiName'));

    return $linhas;
}

function linhasRedeWifi(array $parametros): array
{
    $linhas = [];
    $rede = valorParametro($parametros, 'UNIFInetworkName');
    $vlan = valorParametro($parametros, 'UNIFInetworkVlan');

    if ($vlan !== null) {
        $rede = $rede !== null ? "{$rede} — VLAN {$vlan}" : "VLAN {$vlan}";
    }

    adicionarLinha($linhas, 'Rede', $rede);
    adicionarLinha($linhas, 'Sub-rede', valorParametro($parametros, 'UNIFInetworkSubnet'));

    return $linhas;
}

function linhasConexaoWan(array $parametros): array
{
    $linhas = [];

    adicionarLinha($linhas, 'Conexão WAN', valorParametro($parametros, 'UNIFIwanName'));
    adicionarLinha($linhas, 'Identificador WAN', valorParametro($parametros, 'UNIFIwanId'));
    adicionarLinha($linhas, 'Porta', valorParametro($parametros, 'UNIFIwanPort'));
    adicionarLinha($linhas, 'Provedor', valorParametro($parametros, 'UNIFIwanIsp'));
    adicionarLinha($linhas, 'Sub-rede WAN', valorParametro($parametros, 'UNIFIwanSubnet'));
    adicionarLinha($linhas, 'SLA', valorParametro($parametros, 'UNIFIwanSla'));

    return $linhas;
}

function linhasEquipamentoUnifi(array $parametros): array
{
    $linhas = [];

    adicionarLinha($linhas, 'Equipamento', valorParametro($parametros, 'UNIFIdeviceName'));
    adicionarLinha($linhas, 'IP do equipamento', valorParametro($parametros, 'UNIFIdeviceIp'));
    adicionarLinha($linhas, 'Modelo', valorParametro($parametros, 'UNIFIdeviceModel'));
    adicionarLinha($linhas, 'MAC do equipamento', valorParametro($parametros, 'UNIFIdeviceMac'));
    adicionarLinha($linhas, 'Firmware', valorParametro($parametros, 'UNIFIdeviceVersion'));

    return $linhas;
}

function valorDiferenteDeZero(array $parametros, string $nome): ?string
{
    $valor = valorParametro($parametros, $nome);

    return $valor === '0' ? null : $valor;
}

function protocoloComPortas(array $parametros): ?string
{
    $protocolo = valorParametro($parametros, 'proto');
    $portaOrigem = valorParametro($parametros, 'spt');
    $portaDestino = valorParametro($parametros, 'dpt');

    if ($protocolo === null || ($portaOrigem === null && $portaDestino === null)) {
        return $protocolo;
    }

    return $protocolo . ' — origem ' . ($portaOrigem ?? '?') . ' → destino ' . ($portaDestino ?? '?');
}
