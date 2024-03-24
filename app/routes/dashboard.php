<?php
$app->map('/dashboard/:action/:args+', function($a = 'login', $args = array()) use ($app)
{
	$controller = 'dashboard';
	$action = $a;

	$controllerPath = CONTROLLER_PATH . $controller . '/'. $action . '.php';
	include_file($app, $controllerPath, $args);
})->via('GET', 'POST');

$app->map('/dashboard(/:action)/', function($a = 'login') use ($app)
{
	$controller = 'dashboard';
	$action = $a;

	$controllerPath = CONTROLLER_PATH . $controller . '/' . $action . '.php';
	include_file($app, $controllerPath);
})->via('GET', 'POST');