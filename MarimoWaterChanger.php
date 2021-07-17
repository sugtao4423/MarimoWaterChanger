<?php

declare(strict_types=1);

define('MARIMO_COOKIE', getenv('RINNA_MARIMO_COOKIE'));

define('MARIMO_UA', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36');
define('MARIMO_ORIGIN', 'https://himitsu.rinna.jp');
define('MARIMO_REFERER', 'https://himitsu.rinna.jp/features/marimo');

if (MARIMO_COOKIE === false) {
    echo "Set `RINNA_MARIMO_COOKIE`\n";
    exit(1);
}

changeWater();
$result = isWaterChanged();
if (!$result) {
    echo 'Failed change water.';
}


function changeWater()
{
    $url = 'https://himitsu.rinna.jp/api/features/marimo/water';
    $method = 'POST';
    $header = [
        'User-Agent: ' . MARIMO_UA,
        'Origin: ' . MARIMO_ORIGIN,
        'Referer: ' . MARIMO_REFERER,
        'Content-Length: 0',
        'Cookie: ' . MARIMO_COOKIE,
    ];
    $context = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $header),
        ]
    ];

    for ($i = 0; $i < 5; $i++) {
        $result = file_get_contents($url, false, stream_context_create($context));
        if ($result === false) {
            sleep(2);
        } else {
            break;
        }
    }
}

function isWaterChanged(): bool
{
    $url = 'https://himitsu.rinna.jp/api/features/marimo/status';
    $method = 'GET';
    $header = [
        'User-Agent: ' . MARIMO_UA,
        'Referer: ' . MARIMO_REFERER,
        'Cookie: ' . MARIMO_COOKIE,
    ];
    $context = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $header),
        ]
    ];

    $response = file_get_contents($url, false, stream_context_create($context));
    if ($response === false) {
        return false;
    }

    $json = json_decode($response, true);
    $waterChangedTime = intval($json['UserState']['Water']['RefreshedTime']);

    return (time() - $waterChangedTime) < 60;
}
