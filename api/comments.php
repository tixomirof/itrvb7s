<?php

require_once "../vendor/autoload.php";

use ITRvB\Http\Request;
use ITRvB\Http\Controllers\CommentController;

$request = new Request();
$request->process(new CommentController());