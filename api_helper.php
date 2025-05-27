<?php
function fetchMovieFromOmdb($searchQuery) {
    $apiKey = 'YOUR_OMDB_API_KEY'; // Replace with your actual OMDb API key
    $url = "http://www.omdbapi.com/?s=" . urlencode($searchQuery) . "&apikey=" . $apiKey;

    $response = @file_get_contents($url); // Use @ to suppress warnings for network issues
    if ($response === FALSE) {
        return ['error' => 'Could not connect to OMDb API.'];
    }

    $data = json_decode($response, true);

    if (isset($data['Response']) && $data['Response'] == 'True') {
        return $data['Search']; // Returns an array of movies
    } elseif (isset($data['Response']) && $data['Response'] == 'False') {
        return ['error' => $data['Error']]; // e.g., "Movie not found!"
    }
    return ['error' => 'Unknown API error.'];
}

function fetchMovieDetailsFromOmdb($imdbId) {
    $apiKey = 'YOUR_OMDB_API_KEY'; // Replace with your actual OMDb API key
    $url = "http://www.omdbapi.com/?i=" . urlencode($imdbId) . "&plot=full&apikey=" . $apiKey;

    $response = @file_get_contents($url);
    if ($response === FALSE) {
        return ['error' => 'Could not connect to OMDb API.'];
    }

    $data = json_decode($response, true);

    if (isset($data['Response']) && $data['Response'] == 'True') {
        return $data; // Returns a single movie's full details
    } elseif (isset($data['Response']) && $data['Response'] == 'False') {
        return ['error' => $data['Error']];
    }
    return ['error' => 'Unknown API error.'];
}
?>