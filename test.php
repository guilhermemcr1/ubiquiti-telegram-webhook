<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/events/index.php';

function verificar(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

$base = [
    'parameters' => [
        'UNIFIclientAlias' => 'Cliente & Teste',
        'UNIFIclientHostname' => 'telefone-teste',
        'UNIFIclientIp' => '192.0.2.10',
        'UNIFIclientMac' => '02:00:00:00:00:10',
        'UNIFIwifiName' => 'Wi-Fi <Teste>',
        'UNIFIutcTime' => '2026-07-10T18:51:30.157Z',
    ],
    'severity' => 1,
    'version' => '10.4.57',
];

$eventos = [
    '201' => ['name' => 'Threat Detected and Blocked', 'parameters' => [
        'proto' => 'UDP',
        'spt' => '27915',
        'dpt' => '1110',
        'app' => 'Other',
        'UNIFIrisk' => 'high',
        'UNIFIpolicyName' => 'P2P',
        'UNIFIpolicyType' => 'IDS/IPS',
        'UNIFIdirection' => 'outgoing',
        'deviceInboundInterface' => 'Rede de teste',
        'deviceOutboundInterface' => 'Link de teste',
        'dst' => '198.51.100.50',
        'UNIFIsrcClientAlias' => 'Notebook de teste',
        'UNIFIsrcClientHostname' => 'notebook-teste',
        'UNIFIsrcClientMac' => '02:00:00:00:00:20',
        'UNIFIsrcClientModel' => 'Windows PC',
        'UNIFIsrcZone' => 'Internal',
        'UNIFIdstRegion' => 'RU',
        'UNIFIdstZone' => 'External',
        'UNIFItotalBytes' => '14727',
        'UNIFItotalPackets' => '13',
        'UNIFIipsSignature' => 'ET P2P BitTorrent DHT announce_peers request',
        'UNIFIipsSignatureId' => '2008585',
    ]],
    '107' => ['name' => 'Internet Restored', 'parameters' => [
        'UNIFIwanName' => 'Link de teste',
        'UNIFIwanId' => 'WAN1',
        'UNIFIwanPort' => '2',
        'UNIFIwanIsp' => 'Provedor de teste',
        'UNIFIwanSubnet' => '203.0.113.10/32',
        'UNIFIwanSla' => 'Auto',
        'UNIFIreportedDuration' => '6m',
        'UNIFIdeviceName' => 'Gateway de teste',
        'UNIFIdeviceIp' => '192.0.2.1',
        'UNIFIdeviceModel' => 'UX7',
        'UNIFIdeviceMac' => '02:00:00:00:00:01',
        'UNIFIdeviceVersion' => '5.1.19',
    ]],
    '112' => ['name' => 'High Latency Detected', 'parameters' => [
        'UNIFIwanName' => 'Link de teste',
        'UNIFIwanId' => 'WAN1',
        'UNIFIwanPort' => '2',
        'UNIFIwanIsp' => 'Provedor de teste',
        'UNIFIwanSubnet' => '203.0.113.10/32',
        'UNIFIwanSla' => 'Auto',
        'UNIFIwanLatency' => '55',
        'UNIFIdeviceName' => 'Gateway de teste',
        'UNIFIdeviceIp' => '192.0.2.1',
        'UNIFIdeviceModel' => 'UX7',
        'UNIFIdeviceMac' => '02:00:00:00:00:01',
        'UNIFIdeviceVersion' => '5.1.19',
    ]],
    '203' => ['name' => 'Blocked by Firewall', 'parameters' => [
        'UNIFIsrcClientAlias' => 'Notebook de teste',
        'UNIFIsrcClientIp' => '192.0.2.20',
        'dst' => '198.51.100.30',
        'proto' => 'UDP',
        'spt' => '50000',
        'dpt' => '161',
    ]],
    '400' => ['name' => 'WiFi Client Connected'],
    '401' => ['name' => 'WiFi Client Disconnected'],
    '407' => ['name' => 'High Internet Traffic Detected', 'message' => 'Tráfego acima do limite.'],
];

foreach ($eventos as $classe => $dados) {
    $payload = array_replace_recursive($base, $dados, ['deviceEventClassId' => $classe]);
    $mensagem = formatarEvento($payload);

    verificar(str_contains($mensagem, "<b>Classe:</b> {$classe}"), "Evento {$classe} não foi formatado.");
    verificar(mb_strlen($mensagem, 'UTF-8') <= 4000, "Evento {$classe} ultrapassou o limite.");
}

$internetRestaurada = formatarEvento(array_replace_recursive($base, $eventos['107'], ['deviceEventClassId' => '107']));
verificar(str_contains($internetRestaurada, '<b>Tempo indisponível:</b> 6m'), 'Tempo de indisponibilidade não foi formatado.');
verificar(str_contains($internetRestaurada, '<b>Provedor:</b> Provedor de teste'), 'Provedor WAN não foi formatado.');

$latenciaAlta = formatarEvento(array_replace_recursive($base, $eventos['112'], ['deviceEventClassId' => '112']));
verificar(str_contains($latenciaAlta, '<b>Latência:</b> 55 ms'), 'Latência WAN não foi formatada.');

$ameacaBloqueada = formatarEvento(array_replace_recursive($base, $eventos['201'], ['deviceEventClassId' => '201']));
verificar(str_contains($ameacaBloqueada, '<b>Assinatura IDS/IPS:</b> ET P2P BitTorrent DHT announce_peers request'), 'Assinatura IDS/IPS não foi formatada.');
verificar(str_contains($ameacaBloqueada, '<b>Protocolo:</b> UDP — origem 27915 → destino 1110'), 'Portas da ameaça não foram formatadas.');

$generico = formatarEvento([
    'deviceEventClassId' => '999',
    'name' => 'Evento <novo>',
    'message' => 'Mensagem & detalhes',
]);

verificar(str_contains($generico, 'Evento &lt;novo&gt;'), 'Nome do evento não foi escapado.');
verificar(str_contains($generico, 'Mensagem &amp; detalhes'), 'Mensagem genérica não foi escapada.');
verificar(str_contains($generico, '<b>Payload recebido:</b>'), 'Payload desconhecido não foi incluído.');
verificar(str_contains($generico, '&quot;deviceEventClassId&quot;:&quot;999&quot;'), 'JSON do payload desconhecido não foi incluído.');
verificar(valorParametro(['zero' => 0], 'zero') === '0', 'O valor zero deve ser aceito.');
verificar(valorParametro(['nulo' => 'null'], 'nulo') === null, 'A string null deve ser ignorada.');
verificar(formatarDataUniFi('2026-07-10T18:51:30.157Z') === '10/07/2026 15:51:30', 'Data UTC não foi convertida.');
verificar(mb_strlen(limitarTexto(str_repeat('á', 10), 5), 'UTF-8') === 5, 'Texto multibyte não foi limitado.');

$firewallLongo = formatarEvento([
    'deviceEventClassId' => '203',
    'parameters' => array_fill_keys([
        'UNIFIsrcClientAlias',
        'UNIFIsrcClientHostname',
        'UNIFIsrcClientModel',
        'UNIFIsrcClientIp',
        'UNIFIsrcClientMac',
        'dst',
        'app',
        'UNIFIpolicyName',
        'UNIFIrisk',
        'UNIFIdirection',
        'UNIFIsrcZone',
        'UNIFIdstZone',
        'UNIFIpacketsReceived',
        'UNIFIbytesReceived',
        'UNIFIflowDuration',
        'UNIFIflowCount',
    ], str_repeat('<dado&>', 100)),
]);

$textoVisivel = html_entity_decode(strip_tags($firewallLongo), ENT_QUOTES | ENT_HTML5, 'UTF-8');
verificar(mb_strlen($textoVisivel, 'UTF-8') <= 4000, 'Fallback de mensagem longa falhou.');
verificar(substr_count($firewallLongo, '<b>') === substr_count($firewallLongo, '</b>'), 'Mensagem longa contém HTML incompleto.');

echo "Testes concluídos com sucesso.\n";
