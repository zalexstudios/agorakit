<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;

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
        'password',
        'password_confirmation',
    ];

    /**
    * Report or log an exception.
    *
    * @param  \Throwable  $exception
    * @return void
    *
    * @throws \Exception
    */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
    * Render an exception into an HTTP response.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Throwable  $exception
    * @return \Symfony\Component\HttpFoundation\Response
    *
    * @throws \Throwable
    */
    public function render($request, Throwable $exception)
    {
        if ($request->hasHeader('X-Up-Target')) {
            if (method_exists($exception, 'render') && $response = $exception->render($request)) {
                return Router::toResponse($request, $response)->header('Content-Type', 'text/html');
            } elseif ($exception instanceof Responsable) {
                return $exception->toResponse($request)->header('Content-Type', 'text/html');
            }

            $exception = $this->prepareException($exception);

            if ($exception instanceof HttpResponseException) {
                return $exception->getResponse();
            } elseif ($exception instanceof AuthenticationException) {
                return $this->unauthenticated($request, $exception);
            } elseif ($exception instanceof ValidationException) {
                return $this->convertValidationExceptionToResponse($exception, $request);
            }

            return $this->prepareResponse($request, $exception);
        }

        return parent::render($request, $exception);
    }


    /**
    * This ensures that when an request is made with unpoly, a correct error type is reported.
    * See : https://github.com/webstronauts/php-unpoly#validation-errors
    * Whenever a form is submitted through Unpoly, the response is returned as JSON by default.
    * This is because Laravel returns JSON formatted response for any request
    * with the header X-Requested-With set to  XMLHttpRequest.
    */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        return $request->expectsJson() && ! $request->hasHeader('X-Up-Target')
        ? $this->invalidJson($request, $e)
        : $this->invalid($request, $e);
    }
}
