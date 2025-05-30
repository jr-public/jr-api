<?php
namespace App\Exception;
class NotFoundException extends ApiException {
    public function __construct(string $message = 'Resource not found', $httpStatus = 404) {
        parent::__construct($message, $httpStatus);
    }
}