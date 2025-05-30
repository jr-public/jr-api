<?php
namespace App\Exception;
class BusinessException extends ApiException {
    public function __construct(string $message = 'Business rule violation', $httpStatus = 422) {
        parent::__construct($message, $httpStatus);
    }
}