<?php
namespace App\Service;

use App\Exception\ApiException;
use App\Service\ConfigService;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseService {
    
    private bool $debugMode;
    
    public function __construct(
		ConfigService $config
	) {
        $this->debugMode = $config->get('debug', false);
    }
    
    public function success($data = null, int $httpStatus = 200): JsonResponse {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'error' => null
        ], $httpStatus);
    }
    
    public function error(\Throwable $exception): JsonResponse {
        $response = [
            'success' => false,
            'data' => null,
            'message' => $this->getErrorCode($exception)
        ];
        
        if ($this->debugMode) {
            $response['debug'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'timestamp' => date('c')
            ];
            
            if ($exception instanceof ApiException && $exception->getDetail()) {
                $response['debug']['detail'] = $exception->getDetail();
            }
        }
        
        $httpStatus = $exception instanceof ApiException 
            ? $exception->getHttpStatus() 
            : 500;
            
        return new JsonResponse($response, $httpStatus);
    }
    
    private function getErrorCode(\Throwable $exception): string {
        if ($exception instanceof ApiException || $this->debugMode) {
            return $exception->getMessage();
        }
        return 'INTERNAL_SERVER_ERROR';
    }
}