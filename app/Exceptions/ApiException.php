<?php

namespace App\Exceptions;

use Exception;

/**
 * Generic API Exception for handling all custom exceptions
 */
class ApiException extends Exception
{
    protected $statusCode;
    protected $errorData;

    public function __construct(
        string $message = 'An error occurred',
        int $statusCode = 500,
        array $errorData = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorData = $errorData;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render($request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'errors' => $this->errorData ?: null,
            ], $this->statusCode);
        }

        return parent::render($request);
    }

    // Static factory methods for common errors
    public static function notFound(string $resource = 'Resource'): self
    {
        return new self("{$resource} not found", 404);
    }

    public static function validationFailed(array $errors = []): self
    {
        return new self('Validation failed', 422, $errors);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self($message, 403);
    }

    public static function conflict(string $message = 'Resource already exists'): self
    {
        return new self($message, 409);
    }

    public static function serverError(string $message = 'Internal server error'): self
    {
        return new self($message, 500);
    }

    public static function badRequest(string $message = 'Bad request'): self
    {
        return new self($message, 400);
    }
}