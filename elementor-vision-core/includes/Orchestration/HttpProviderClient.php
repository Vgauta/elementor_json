<?php

namespace ElementorVisionCore\Orchestration;

class HttpProviderClient {
    public static function post_json(string $url, string $api_key, array $body, string $text_path = ''): array {
        $response = wp_remote_post($url, [
            'timeout' => 60,
            'headers' => ['Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json'],
            'body' => wp_json_encode($body),
        ]);
        if (is_wp_error($response)) { return ['ok' => false, 'error' => $response->get_error_message()]; }
        $raw = wp_remote_retrieve_body($response);
        $json = json_decode($raw, true);
        $status = wp_remote_retrieve_response_code($response);
        if ($status < 200 || $status > 299) { return ['ok' => false, 'error' => 'Provider API error', 'details' => $json ?: $raw]; }
        $text = self::get_path($json, $text_path);
        return ['ok' => true, 'text' => is_string($text) ? $text : wp_json_encode($json), 'raw' => $json];
    }
    private static function get_path(array $data, string $path) {
        if ($path === '') { return null; }
        $cur = $data;
        foreach (explode('.', $path) as $seg) {
            if (is_array($cur) && array_key_exists($seg, $cur)) { $cur = $cur[$seg]; } else { return null; }
        }
        return $cur;
    }
}
