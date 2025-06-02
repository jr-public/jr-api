<?php
namespace App\Exception;

class BusinessException extends ApiException {
    public function __construct(
        string $message = 'BUSINESS_ERROR',
        int $httpStatus = 422,
        ?string $detail = null
    ) {
        parent::__construct($message, $httpStatus, $detail);
    }
}