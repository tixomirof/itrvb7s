<?php

namespace ITRvB\Interfaces;

use ITRvB\Http\Request;
use ITRvB\Repositories\Connection\MySQL;

interface IController
{
    public function init(MySQL $mysql);
    public function processRequest(Request $request);
}