<?php

require_once "../vendor/autoload.php";

use ITRvB\Http\Request;
use ITRvB\Http\Controllers\LikeController;

$request = new Request();
$request->process(new LikeController());