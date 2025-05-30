<?php
namespace App\Exception;
class ApiException extends \Exception {
    public function __construct(string $message, int $httpStatus = 500) {
        parent::__construct($message);
        $this->code = $httpStatus;
    }
    
    public function getHttpStatus(): int {
        return $this->code;
    }
}