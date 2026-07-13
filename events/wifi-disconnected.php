<?php

declare(strict_types=1);

function formatarWifiDisconnected(array $payload): array
{
    $parametros = is_array($payload['parameters'] ?? null) ? $payload['parameters'] : [];
    $linhas = [...linhasClienteWifi($parametros), ...linhasRedeWifi($parametros)];

    adicionarLinha($linhas, 'Último equipamento', valorParametro($parametros, 'UNIFIlastConnectedToDeviceName'));
    adicionarLinha($linhas, 'IP do equipamento', valorParametro($parametros, 'UNIFIlastConnectedToDeviceIp'));
    adicionarLinha($linhas, 'Modelo', valorParametro($parametros, 'UNIFIlastConnectedToDeviceModel'));

    $sinal = valorDiferenteDeZero($parametros, 'UNIFIlastConnectedToWiFiRssi');
    adicionarLinha($linhas, 'Último sinal', $sinal !== null ? "{$sinal} dBm" : null);

    $canal = valorDiferenteDeZero($parametros, 'UNIFIwifiChannel');
    $largura = valorDiferenteDeZero($parametros, 'UNIFIwifiChannelWidth');
    adicionarLinha($linhas, 'Canal/largura', $canal !== null ? $canal . ($largura !== null ? " / {$largura} MHz" : '') : null);
    adicionarLinha($linhas, 'Tempo conectado', valorParametro($parametros, 'UNIFIduration'));
    adicionarLinha($linhas, 'Download', valorParametro($parametros, 'UNIFIusageDown'));
    adicionarLinha($linhas, 'Upload', valorParametro($parametros, 'UNIFIusageUp'));

    $utilizacao = valorParametro($parametros, 'UNIFIwifiAirtimeUtilization');
    $interferencia = valorParametro($parametros, 'UNIFIwifiInterference');
    adicionarLinha($linhas, 'Utilização do canal', $utilizacao !== null ? "{$utilizacao}%" : null);
    adicionarLinha($linhas, 'Interferência', $interferencia !== null ? "{$interferencia}%" : null);
    adicionarLinha($linhas, 'Firmware', valorParametro($parametros, 'UNIFIlastConnectedToDeviceVersion'));

    return $linhas;
}
