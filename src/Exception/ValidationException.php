<?php
namespace App\Exception;
class ValidationException extends ApiException {
    public function __construct(string $message = 'Validation failed', $httpStatus = 400) {
        parent::__construct($message, $httpStatus);
    }
}