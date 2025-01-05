<?php

require_once "../vendor/autoload.php";

use ITRvB\Http\Request;
use ITRvB\Http\Controllers\Auth\RegisterController;

$request = new Request();
$request->process(new RegisterController());