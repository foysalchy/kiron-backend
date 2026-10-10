<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\QueryException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        ApiException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle custom ApiException
        $this->renderable(function (ApiException $e, $request) {
            return $e->render($request);
        });

        // Handle Validation Exception
        $this->renderable(function (ValidationException $e, $request) {
            \Illuminate\Support\Facades\Log::info('Validation Failed', ['errors' => $e->errors()]);
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::validationError(
                    $e->errors(),
                    $e->getMessage()
                );
            }
        });

        // Handle Model Not Found Exception
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $model = class_basename($e->getModel());
                return ResponseHelper::notFound("{$model} not found");
            }
        });

        // Handle 404 Not Found Exception
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::notFound('Endpoint not found');
            }
        });

        // Handle Method Not Allowed Exception
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::error('Method not allowed', 405);
            }
        });

        // Handle Authentication Exception (Unauthenticated users)
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::unauthorized('Unauthenticated. Please provide a valid access token');
            }
        });

        // Handle Authorization Exception (Forbidden)
        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::forbidden('You do not have permission to perform this action');
            }
        });

        // Handle Database Query Exception
        $this->renderable(function (QueryException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $errorCode = $e->errorInfo[1] ?? null;
                
                // Duplicate entry error
                if ($errorCode === 1062) {
                    return ResponseHelper::error('Duplicate entry. Record already exists', 409);
                }

                // Foreign key constraint error
                if ($errorCode === 1451) {
                    return ResponseHelper::error('Cannot delete. Record has dependent data', 409);
                }

                // Generic database error
                return ResponseHelper::serverError(
                    config('app.debug') ? $e->getMessage() : 'Database error occurred'
                );
            }
        });

        // Generic Exception Handler for API routes
        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $statusCode = method_exists($e, 'getStatusCode') 
                    ? $e->getStatusCode() 
                    : 500;

                $message = config('app.debug') 
                    ? $e->getMessage() 
                    : 'An error occurred';

                $errors = config('app.debug') ? [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->take(5)->toArray(),
                ] : null;

                return ResponseHelper::error($message, $statusCode, $errors);
            }
        });
    }
}