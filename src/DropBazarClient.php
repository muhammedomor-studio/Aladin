<?php
namespace App;

class DropBazarClient {
    private string $base;
    private string $token;
    public function __construct() {
        $this->base = rtrim(\env('DROPBAZAR_API_URL', 'https://dropbazarbd.com/api/v1'), '/');
        $this->token = \env('DROPBAZAR_API_TOKEN', '');
        if (!$this->token) \response_json(['success'=>false,'message'=>'Backend is missing DROPBAZAR_API_TOKEN.'], 503);
    }
    public function get(string $path, array $query = []): array { return $this->request('GET', $path, $query, null); }
    public function post(string $path, array $body): array { return $this->request('POST', $path, [], $body); }
    private function request(string $method, string $path, array $query, ?array $body): array {
        $url = $this->base . '/' . ltrim($path, '/');
        if ($query) $url .= '?' . http_build_query($query);
        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Accept: application/json',
            'User-Agent: BloggerDropBazarBackend/1.0'
        ];
        $opts = ['http'=>[
            'method'=>$method,
            'header'=>implode("\r\n", $headers),
            'timeout'=>25,
            'ignore_errors'=>true
        ]];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            $opts['http']['header'] = implode("\r\n", $headers);
            $opts['http']['content'] = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $context = stream_context_create($opts);
        $raw = @file_get_contents($url, false, $context);
        $status = 0;
        foreach ($http_response_header ?? [] as $h) if (preg_match('#HTTP/\S+\s+(\d+)#', $h, $m)) $status=(int)$m[1];
        // Status is parsed from the response headers populated by PHP's HTTP wrapper.
        $responseHeaders = $http_response_header ?? [];
        foreach ($responseHeaders as $h) if (preg_match('#HTTP/\S+\s+(\d+)#', $h, $m)) $status=(int)$m[1];
        if ($raw === false) throw new \RuntimeException('Could not connect to DropBazarBD API.');
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) throw new \RuntimeException('DropBazarBD returned an invalid response.');
        if ($status >= 400 || (($decoded['success'] ?? true) === false)) {
            $msg = $decoded['message'] ?? $decoded['error'] ?? 'DropBazarBD API request failed.';
            throw new ApiException((string)$msg, $status ?: 502, $decoded);
        }
        return $decoded;
    }
}
