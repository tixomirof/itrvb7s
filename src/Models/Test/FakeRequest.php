<?php

namespace ITRvB\Models\Test;

use ITRvB\Http\Request;

class FakeRequest extends Request
{
    public string $requestMethod;
    public ?string $uuid;
    public array $body;

    public function __construct(string $requestMethod = "GET", ?string $uuid = null)
    {
        $this->requestMethod = $requestMethod;
        $this->uuid = $uuid;
        $this->body = [];
    }

    public function getArguments() : array
    {
        if (is_null($this->uuid)) return array();
        return [
            'uuid' => $this->uuid
        ];
    }

    public function getRequestMethod() : string
    {
        return $this->requestMethod;
    }

    public function getBody() : array
    {
        return $this->body;
    }
}