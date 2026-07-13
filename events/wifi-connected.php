<?php

declare(strict_types=1);

function formatarWifiConnected(array $payload): array
{
    $parametros = is_array($payload['parameters'] ?? null) ? $payload['parameters'] : [];
    $linhas = [...linhasClienteWifi($parametros), ...linhasRedeWifi($parametros)];

    adicionarLinha($linhas, 'Conectado em', valorParametro($parametros, 'UNIFIconnectedToDeviceName'));
    adicionarLinha($linhas, 'IP do equipamento', valorParametro($parametros, 'UNIFIconnectedToDeviceIp'));
    adicionarLinha($linhas, 'Modelo do equipamento', valorParametro($parametros, 'UNIFIconnectedToDeviceModel'));

    $sinal = valorDiferenteDeZero($parametros, 'UNIFIWiFiRssi');
    adicionarLinha($linhas, 'Sinal', $sinal !== null ? "{$sinal} dBm" : null);

    $canal = valorDiferenteDeZero($parametros, 'UNIFIwifiChannel');
    $largura = valorDiferenteDeZero($parametros, 'UNIFIwifiChannelWidth');
    adicionarLinha($linhas, 'Canal/largura', $canal !== null ? $canal . ($largura !== null ? " / {$largura} MHz" : '') : null);
    adicionarLinha($linhas, 'Autenticação', traduzirAutenticacao(valorParametro($parametros, 'UNIFIauthMethod')));
    adicionarLinha($linhas, 'Firmware', valorParametro($parametros, 'UNIFIconnectedToDeviceVersion'));

    return $linhas;
}
