<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        $errors = (new Collection($exception->validator->errors()))
            ->map(function ($error, $key) {
                return [
                    'title' => 'Validation Error',
                    'details' => $error[0],
                    'source' => [
                        'pointer' => '/' . str_replace('.', '/', $key),
                    ],
                ];
            })
            ->values();

        return response()->json([
            'errors' => $errors,
        ], $exception->status);
    }

    protected function prepareJsonResponse($request, Throwable $e)
    {
        return response()->json([
            'errors' => [
                [
                    'title' => Str::title(Str::snake(class_basename($e), ' ')),
                    'details' => $e->getMessage(),
                ],
            ],
        ], $this->isHttpException($e) ? $e->getStatusCode() : 500);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['errors' => [
                [
                    'title' => 'Unauthenticated',
                    'details' => 'You are not authenticated',
                ],
            ]], 403);
        }

        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof QueryException || $exception instanceof ModelNotFoundException) {
            $exception = new NotFoundHttpException('Given resource not found');
        }

        return parent::render($request, $exception);
    }
}
