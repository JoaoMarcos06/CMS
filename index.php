<?php 
session_start();
require_once("vendor/autoload.php");

error_reporting(E_ALL);
ini_set("display_errors","true");

date_default_timezone_set("America/Sao_Paulo");
setlocale(LC_ALL, 'pt_BR');

//phpinfo();

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\Model\User;


$app = new Slim();

$app->config('debug', true);

require_once 'helpers/functions.php';
require_once("routes/routes.php");

$app->run();

 ?>