<?php
namespace App;
class ApiException extends \RuntimeException {
    public int $status;
    public array $payload;
    public function __construct(string $message, int $status = 502, array $payload = []) {
        parent::__construct($message);
        $this->status = $status;
        $this->payload = $payload;
    }
}
