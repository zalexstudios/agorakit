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
    public function render($request, Throwable $e)
    {
        if (method_exists($e, 'render') && $response = $e->render($request)) {
            return Router::toResponse($request, $response);
        } elseif ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        $e = $this->prepareException($e);

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        } elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        }

        if ($request->hasHeader('X-Up-Target')) {
            return $this->prepareResponse($request, $e);
        }

        return $request->expectsJson()
        ? $this->prepareJsonResponse($request, $e)
        : $this->prepareResponse($request, $e);
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
