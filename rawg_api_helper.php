<?php
// rawg_api_helper.php

function fetchGamesFromRawg($searchQuery) {
    $apiKey = '596a8a3f5c194d188b260381abe86354'; // <-- REPLACE THIS WITH YOUR ACTUAL RAWG API KEY
    $url = "https://api.rawg.io/api/games?key=" . urlencode($apiKey) . "&search=" . urlencode($searchQuery) . "&page_size=20"; // Limit to 20 results

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName/1.0 (your-email@example.com)'); // RAWG recommends a user agent

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === FALSE) {
        return ['error' => 'cURL Error: ' . $curlError];
    }

    if ($httpCode != 200) {
        return ['error' => 'API Error (HTTP ' . $httpCode . '): ' . $response];
    }

    $data = json_decode($response, true);

    if (isset($data['results'])) {
        return $data['results']; // Returns an array of games
    }
    return ['error' => 'No games found or unexpected API response.'];
}

function fetchGameDetailsFromRawg($rawgId) {
    $apiKey = '596a8a3f5c194d188b260381abe86354'; // <-- REPLACE THIS WITH YOUR ACTUAL RAWG API KEY
    $url = "https://api.rawg.io/api/games/" . urlencode($rawgId) . "?key=" . urlencode($apiKey);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName/1.0 (your-email@example.com)'); // RAWG recommends a user agent

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === FALSE) {
        return ['error' => 'cURL Error: ' . $curlError];
    }

    if ($httpCode != 200) {
        return ['error' => 'API Error (HTTP ' . $httpCode . '): ' . $response];
    }

    $data = json_decode($response, true);

    if (isset($data['id'])) { // If 'id' is present, it's a valid game detail
        return $data; // Returns a single game's full details
    }
    return ['error' => 'Game details not found or unexpected API response.'];
}
?>