<?php
namespace App\Exception;
class AuthException extends ApiException {
    public function __construct(string $message = 'Authentication failed', int $httpStatus = 401) {
        parent::__construct($message, $httpStatus);
    }
}