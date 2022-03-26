<?php

namespace Tests\Unit\Exceptions;

use Tests\TestCase;
use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;


class HandlerTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_it_converts_an_exception_into_a_json_api_spec_error_response()
    {
        $handler = app(Handler::class);
        $request = Request::create('/test', 'GET');
        $request->headers->set('accept', 'application/vnd.api+json');

        $exception = new \Exception('Test exception');
        
        $response = $handler->render($request, $exception);
        TestResponse::fromBaseResponse($response)->assertJson([
            'errors' => [
                [
                    'title' => 'Exception',
                    'details' => 'Test exception',
                ]
            ]
        ])->assertStatus(500);
    }
    
    public function test_it_converts_a_http_exception_into_a_json_api_spec_error_response()
    {
        $handler = app(Handler::class);
        $request = Request::create('/test', 'GET');
        $request->headers->set('accept', 'application/vnd.api+json');

        $exception = new HttpException(404, 'Not Found');
        
        $response = $handler->render($request, $exception);
        TestResponse::fromBaseResponse($response)->assertJson([
            'errors' => [
                [
                    'title' => 'Http Exception',
                    'details' => 'Not Found',
                ]
            ]
        ])->assertStatus(404);
    }
    
    public function test_it_converts_an_authentication_exception_into_a_json_api_spec_error_response()
    {
        $handler = app(Handler::class);
        $request = Request::create('/test', 'GET');
        $request->headers->set('accept', 'application/vnd.api+json');

        $exception = new AuthenticationException();
        
        $response = $handler->render($request, $exception);
        TestResponse::fromBaseResponse($response)->assertJson([
            'errors' => [
                [
                    'title' => 'Unauthenticated',
                    'details' => 'You are not authenticated',
                ]
            ]
        ]);
    }
}
