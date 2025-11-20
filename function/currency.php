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
    $cacheTime = 3600; // 1 hour cache

    // Check if cache exists and is still valid
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if (isset($data['data'][$to])) {
            return $data['data'][$to];
        }
    }

    // Fetch new data from API
    $apiKey = 'fca_live_p7DJOO0ZWgEsL6qLRO86EuPF9JmA8RNry2io2Z8p';
    $url = "https://api.freecurrencyapi.com/v1/latest?apikey={$apiKey}&base_currency={$from}&currencies={$to}";
    $response = file_get_contents($url);

    if ($response === false) {
        // API call failed, return fallback rate
        return 0.00007;
    }

    $data = json_decode($response, true);
    if (isset($data['data'][$to])) {
        // Cache the successful response
        file_put_contents($cacheFile, json_encode($data));
        return $data['data'][$to];
    }

    // API returned invalid data, return fallback rate
    return 0.00007;
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

?>
