<?php
// error display
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
set_time_limit(0);

date_default_timezone_set('Europe/London');

// server configurations
switch($_SERVER['SERVER_NAME'])
{
	case 'zellisnhr.localhost':
		DEFINE('DB_HOST', 'localhost');
		DEFINE('DB_USERNAME', 'root');
		DEFINE('DB_PASSWORD', '');
		DEFINE('DB_DATABASE', 'zellis_db');
		DEFINE('ENV', 'development');
		DEFINE('SQLSERVER_ENV', 'DEV');
		DEFINE('DISABLE_SSL_REDIRECTION', true);
		DEFINE('SERVER_URL', 'http://'.$_SERVER['SERVER_NAME']);
	break;
	default:
		exit('No site registered');
	break;
}

// Start the session
session_start();

DEFINE('DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);

//system models, views and controllers path
DEFINE('ROUTER_PATH', '/');
DEFINE('RESOURCES_CSS', '/resources/css');
DEFINE('RESOURCES_JS', '/resources/js');
DEFINE('RESOURCES_IMAGES', '/resources/images');
DEFINE('SYSTEM_MODELS', '../app/models');
DEFINE('SYSTEM_VIEWS', '../app/views');
DEFINE('SYSTEM_CONTROLLERS', '../app/controllers');

// define password for encryption
DEFINE('PASSWORD_SALT', 15102014);

// files store attachments
DEFINE('FILES_STORE_ATTACHMENTS', './files-store/attachments');
DEFINE('FILES_STORE_ATTACHMENTS_ABSOLUTE', '/files-store/attachments');


