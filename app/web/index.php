<?php

ini_set('max_execution_time', 30000);
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Session\Session;

require_once __DIR__.'/../vendor/autoload.php';

Debug::enable();

$app = require_once __DIR__.'/../src/bootstrap.php';

$session = new Session();
$session->start();

$app->run();

