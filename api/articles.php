<?php

require_once "../vendor/autoload.php";

use ITRvB\Http\Request;
use ITRvB\Http\Controllers\ArticleController;

$request = new Request();
$request->process(new ArticleController());