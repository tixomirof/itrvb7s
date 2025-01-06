<?php

namespace ITRvB\Http;

class ResponseManager
{
    public static function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode([
            'error' => 'Not found with given arguments'
        ]);
        return $response;
    }

    public static function methodNotAllowed()
    {
        $response['status_code_header'] = 'HTTP/1.1 405 Method Not Allowed';
        $response['body'] = json_encode([
            'error' => 'This controller does not support given method'
        ]);
        return $response;
    }

    public static function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    public static function makeBadRequestResponse(string $errorMessage)
    {
        $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
        $response['body'] = json_encode([
            'error' => $errorMessage
        ]);
        return $response;
    }

    public static function unauthorizedResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
        $response['body'] = json_encode([
            'error' => 'Please, fill the Authorization header with your bearer token'
        ]);
        return $response;
    }
}