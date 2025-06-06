<?php

namespace App\Service;

use App\Exception\ApiException;
use App\Service\ConfigService;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Service for handling API responses with consistent formatting
 */
class ResponseService
{
    /**
     * @var bool Debug mode flag
     */
    private bool $debugMode;
    /**
     * Initialize response service with configuration
     *
     * @param ConfigService $config Configuration service instance
     */
    public function __construct(ConfigService $config)
    {
        $this->debugMode = $config->get('debug', false);
    }

    /**
     * Create a successful JSON response
     *
     * @param mixed $data Response data
     * @param int $httpStatus HTTP status code
     * @return JsonResponse
     */
    public function success($data = null, int $httpStatus = 200): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'error' => null
        ], $httpStatus);
    }

    /**
     * Create an error JSON response from exception
     *
     * @param \Throwable $exception Exception to format
     * @return JsonResponse
     */
    public function error(\Throwable $exception): JsonResponse
    {
        $response = [
            'success' => false,
            'data' => null,
            'message' => $this->getErrorCode($exception)
        ];
        if ($this->debugMode) {
            $response['debug'] = [
                'class' => get_class($exception),
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

    /**
     * Extract error code from exception
     *
     * @param \Throwable $exception Exception to process
     * @return string Error code
     */
    private function getErrorCode(\Throwable $exception): string
    {
        if ($exception instanceof ApiException || $this->debugMode) {
            return $exception->getMessage();
        }
        return 'INTERNAL_SERVER_ERROR';
    }
}
