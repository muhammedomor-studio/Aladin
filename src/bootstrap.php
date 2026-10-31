<?php
declare(strict_types=1);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) require $file;
});

function env(string $key, ?string $default = null): ?string {
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}
function response_json(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function request_json(): array {
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) response_json(['success'=>false,'message'=>'Invalid JSON body.'], 400);
    return $data;
}

$allowed = array_filter(array_map('trim', explode(',', env('ALLOWED_ORIGINS', '*'))));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array('*', $allowed, true)) {
    header('Access-Control-Allow-Origin: *');
} elseif ($origin && in_array($origin, $allowed, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204); exit;
}
