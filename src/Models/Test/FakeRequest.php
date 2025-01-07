<?php

namespace ITRvB\Models\Test;

use ITRvB\Http\Request;

class FakeRequest extends Request
{
    public string $requestMethod;
    public array $arguments;
    public array $body;
    public array $headers;

    public function __construct(string $requestMethod = "GET")
    {
        $this->requestMethod = $requestMethod;
        $this->arguments = [];
        $this->body = [];
        $this->headers = [];
    }

    public function getArguments() : array
    {
        return $this->arguments;
    }

    public function getRequestMethod() : string
    {
        return $this->requestMethod;
    }

    public function getBody() : array
    {
        return $this->body;
    }

    public function getHeader(string $headerName) : string
    {
        return isset($this->headers[$headerName]) ? $this->headers[$headerName] : 'EMPTY';
    }
}