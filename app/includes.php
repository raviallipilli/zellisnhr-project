<?php
require_once('config.php');

require_once('functions/date.functions.php');
require_once('functions/phpmailer.functions.php');

// Models
function my_autoloader($model)
{
	$paths = array('models');

	foreach($paths as $path)
	{
		$filepath = '../app/' . $path . '/' . $model . '.php';
		//echo $filepath.'<br />';
		if(is_file($filepath))
		{
			require_once($filepath);
			break;
		}
	}
}
spl_autoload_register('my_autoloader');

require_once(DOC_ROOT.'/../vendor/autoload.php');

//define log capture
$logger = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
	'handlers' => array(
		new \Monolog\Handler\StreamHandler('../app/log/'.date('Y-m-d').'.log'),
	),
));

// Set the current mode with Slim
$app = new \Slim\Slim(array(
	'mode' => ENV,
	'log.writer' => $logger
));