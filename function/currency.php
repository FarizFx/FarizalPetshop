<?php

function getExchangeRate($from = 'IDR', $to = 'USD') {
    if ($from === $to) {
        return 1;
    }

    $cacheDir = __DIR__ . '/cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    $cacheFile = $cacheDir . '/exchange_rate.json';
    $cacheTime = 3600; 

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        return $data['data'][$to];
    }

    $apiKey = 'fca_live_p7DJOO0ZWgEsL6qLRO86EuPF9JmA8RNry2io2Z8p';
    $url = "https://api.freecurrencyapi.com/v1/latest?apikey={$apiKey}&base_currency={$from}&currencies={$to}";
    $response = file_get_contents($url);
    if ($response === false) {
        return 0.00007;
    }
    $data = json_decode($response, true);
    if (isset($data['data'][$to])) {
        file_put_contents($cacheFile, json_encode($data));
        return $data['data'][$to];
    }
    return 0.00007;
}

?>
