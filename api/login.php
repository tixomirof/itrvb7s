<?php

require_once "../vendor/autoload.php";

use ITRvB\Http\Request;
use ITRvB\Http\Controllers\Auth\LoginController;

$request = new Request();
$request->process(new LoginController());