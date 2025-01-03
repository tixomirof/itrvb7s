<?php

namespace ITRvB\Http;

use ITRvB\Interfaces\IController;
use ITRvB\Repositories\Connection\MySQL;

class Request
{
    public function setCors() : void
    {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,DELETE");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }

    public function getArguments() : array
    {
        $params = [];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        if ($uri) {
            parse_str($uri, $params);
        }
        return $params;
    }

    public function getRequestMethod() : string
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    public function getBody() : array
    {
        return (array) json_decode(file_get_contents('php://input'), TRUE);
    }

    public function process(IController $controller)
    {
        $this->setCors();
        $mysql = new MySQL();
        $controller->init($mysql);

        $response = $controller->processRequest($this);
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }

        $mysql->dispose();
    }
}