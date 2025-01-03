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

    public function process(IController $controller)
    {
        echo $this->getRequestMethod();
        $this->setCors();
        $mysql = new MySQL();
        $controller->init($mysql);
        $controller->processRequest($this);
        $mysql->dispose();
    }
}