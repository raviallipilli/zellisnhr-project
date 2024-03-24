<?php
require('../app/includes.php');

DEFINE('CONTROLLER_PATH', '../app/controllers/');

require_once('../app/routes/dashboard.php');

$app->map('/:controllers(/:action?)', function($c = 'home', $a = 'index') use ($app)
{
	$controller = $c;
	$action = $a;

	$controllerPath = CONTROLLER_PATH . $controller . '/' . $action . '.php';
	include_file($app, $controllerPath);
})->via('GET', 'POST');

$app->map('/', function() use ($app)
{
	$controller = 'dashboard';
	$action = 'login';

	$controllerPath = CONTROLLER_PATH . $controller . '/' . $action . '.php';
	include_file($app, $controllerPath);
})->via('GET');

$app->notFound(function() 
{
	header('HTTP/1.0 404 Not Found');
	$controllerPath = CONTROLLER_PATH.'errors/404.php';
	require_once($controllerPath);
	exit;
});

function include_file($app, $controllerPath = null, $args = array())
{
	//echo $controllerPath;
	if(file_exists($controllerPath))
	{
		require_once($controllerPath);
	}
	else
	{
		header('HTTP/1.0 404 Not Found');
		$controllerPath = CONTROLLER_PATH.'errors/404.php';
		require_once($controllerPath);
		exit;
	}
}

$app->run();