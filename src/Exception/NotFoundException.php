<?php
namespace App\Exception;

class NotFoundException extends ApiException {
    public function __construct(
        string $message = 'NOT_FOUND_ERROR',
        int $httpStatus = 404,
        ?string $detail = null
    ) {
        parent::__construct($message, $httpStatus, $detail);
    }
}