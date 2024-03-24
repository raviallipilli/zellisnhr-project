<?php 
DEFINE('PAGE_URL', '/dashboard/user-files/');
DEFINE('PAGE_URL_EDIT', '/dashboard/user-files-edit/');

$page_name_main = 'files';
$page_name = 'user-files';

$f = new User_Files;

//look out for any params
$request = $app->request();
$files_id = isset($args[0]) ? $args[0] : null;

$status = $request->params('status', null);
$data = $f->Get_all();
$data_count = count($data);

switch($status)
{
	case 'delete':
		$f->Delete('id', $files_id);
		header('location: '.PAGE_URL.$files_id.'?status=deleted');
			exit;
	break;
}

$view = 'dashboard/user-files.html';
require_once(SYSTEM_VIEWS . '/base.dashboard.html');