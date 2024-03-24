<?php 
DEFINE('PAGE_URL', '/dashboard/users/');
DEFINE('PAGE_URL_EDIT', '/dashboard/users-edit/');

$page_name = 'users';

$users = new Users;

//load up the users list
$data = $users->Get_users();
$data_count = count($data);

$view = 'dashboard/users.html';
require_once(SYSTEM_VIEWS . '/base.dashboard.html');