<?php

declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/wifi-connected.php';
require_once __DIR__ . '/wifi-disconnected.php';
require_once __DIR__ . '/high-traffic.php';
require_once __DIR__ . '/internet-restored.php';
require_once __DIR__ . '/high-latency.php';
require_once __DIR__ . '/multiple-devices-offline.php';
require_once __DIR__ . '/firewall-blocked.php';
require_once __DIR__ . '/threat-blocked.php';
require_once __DIR__ . '/default.php';

function formatarEvento(array $payload): string
{
    $classe = valorParametro($payload, 'deviceEventClassId') ?? 'desconhecida';
    $parametros = is_array($payload['parameters'] ?? null) ? $payload['parameters'] : [];
    $nomes = [
        '203' => 'Conexão bloqueada pelo firewall',
        '201' => 'Tentativa de intrusão detectada e bloqueada',
        '107' => 'Internet restaurada',
        '112' => 'Alta latência detectada',
        '515' => 'Múltiplos dispositivos offline',
        '400' => 'Cliente Wi-Fi conectado',
        '401' => 'Cliente Wi-Fi desconectado',
        '407' => 'Alto tráfego de Internet detectado',
    ];
    $icones = ['107' => '🟢', '112' => '⚠️', '201' => '🚨', '203' => '🛡️', '400' => '🟢', '401' => '🔴', '407' => '⚠️', '515' => '🚨'];

    $detalhes = match ($classe) {
        '203' => formatarFirewallBlocked($payload),
        '201' => formatarThreatBlocked($payload),
        '107' => formatarInternetRestored($payload),
        '112' => formatarHighLatency($payload),
        '515' => formatarMultipleDevicesOffline($payload),
        '400' => formatarWifiConnected($payload),
        '401' => formatarWifiDisconnected($payload),
        '407' => formatarHighTraffic($payload),
        default => formatarEventoGenerico($payload),
    };

    $nome = $nomes[$classe] ?? valorParametro($payload, 'name') ?? 'Evento não mapeado';
    $linhas = [
        ($icones[$classe] ?? 'ℹ️') . ' <b>ALERTA UNIFI</b>',
        '',
        '<b>Evento:</b> ' . escaparTelegram($nome),
        '',
        ...$detalhes,
    ];

    $horario = formatarDataUniFi(valorParametro($parametros, 'UNIFIutcTime'));
    if ($horario !== null) {
        $linhas[] = '';
        adicionarLinha($linhas, 'Horário', $horario);
    }

    $linhas[] = '';
    $linhas[] = '<b>Dados técnicos:</b>';
    adicionarLinha($linhas, 'Classe', $classe);
    adicionarLinha($linhas, 'Severidade', valorParametro($payload, 'severity'));
    adicionarLinha($linhas, 'UniFi Network', valorParametro($payload, 'version'));

    $mensagem = implode("\n", $linhas);

    if (limitarTexto($mensagem, 4000) === $mensagem) {
        return $mensagem;
    }

    $texto = html_entity_decode(strip_tags($mensagem), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return escaparTelegram(limitarTexto($texto, 4000));
}
