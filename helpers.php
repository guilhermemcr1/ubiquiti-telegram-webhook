<?php

declare(strict_types=1);

function obterAuthorization(): string
{
    foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'] as $chave) {
        if (isset($_SERVER[$chave]) && is_string($_SERVER[$chave])) {
            return trim($_SERVER[$chave]);
        }
    }

    if (!function_exists('getallheaders')) {
        return '';
    }

    foreach (getallheaders() as $nome => $valor) {
        if (strcasecmp((string) $nome, 'Authorization') === 0 && is_string($valor)) {
            return trim($valor);
        }
    }

    return '';
}
function valorParametro(array $parametros, string $nome): ?string
{
    if (!array_key_exists($nome, $parametros) || !is_scalar($parametros[$nome])) {
        return null;
    }

    $valor = trim((string) $parametros[$nome]);

    return $valor === '' || strtolower($valor) === 'null' ? null : $valor;
}

function escaparTelegram(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function limitarTexto(string $texto, int $limite): string
{
    if ($limite <= 0) {
        return '';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($texto, 'UTF-8') <= $limite ? $texto : mb_substr($texto, 0, $limite, 'UTF-8');
    }

    return strlen($texto) <= $limite ? $texto : substr($texto, 0, $limite);
}

function formatarDataUniFi(?string $data): ?string
{
    if ($data === null || trim($data) === '') {
        return null;
    }

    try {
        return (new DateTimeImmutable($data))
            ->setTimezone(new DateTimeZone('America/Sao_Paulo'))
            ->format('d/m/Y H:i:s');
    } catch (Throwable) {
        return null;
    }
}

function traduzirAutenticacao(?string $metodo): ?string
{
    if ($metodo === null) {
        return null;
    }

    return match (strtolower($metodo)) {
        'wpapsk' => 'WPA-PSK',
        'wpaeap' => 'WPA-Enterprise',
        'open' => 'Rede aberta',
        default => $metodo,
    };
}

function gravarLog(string $arquivo, array $dados): void
{
    $diretorio = dirname($arquivo);

    if (!is_dir($diretorio) && !mkdir($diretorio, 0775, true) && !is_dir($diretorio)) {
        error_log("Não foi possível criar o diretório de log: {$diretorio}");
        return;
    }

    $json = json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );

    if ($json === false || file_put_contents($arquivo, $json . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
        error_log("Não foi possível gravar o log: {$arquivo}");
    }
}

function finalizarWebhook(
    string $arquivoLog,
    array $log,
    bool $sucesso,
    string $mensagem,
    int $statusHttp
): never {
    $log['status'] = $statusHttp;
    $log['resultado'] = $mensagem;
    $log['finalizado_em'] = date('Y-m-d H:i:s');

    gravarLog($arquivoLog, $log);
    http_response_code($statusHttp);

    echo json_encode(
        ['success' => $sucesso, 'message' => $mensagem],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}
