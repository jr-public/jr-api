<?php
namespace App\Exception;

class ValidationException extends ApiException {
    public function __construct(
        string $message = 'VALIDATION_ERROR',
        int $httpStatus = 400,
        ?string $detail = null
    ) {
        parent::__construct($message, $httpStatus, $detail);
    }
}