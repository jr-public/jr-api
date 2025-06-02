<?php
namespace App\Exception;

class AuthException extends ApiException {
    public function __construct(
        string $message = 'AUTH_ERROR',
        int $httpStatus = 401,
        ?string $detail = null
    ) {
        parent::__construct($message, $httpStatus, $detail);
    }
}
