<?php 
$do = isset($args[0]) ? $args[0] : null;

switch($do)
{
	case 'login':
		$user = new Users;

		//take the post values and assign it to the params array
		$param['email'] = $app->request->post('email');

		$user = $user->Auth_user($param);
		$user_count = count($user);

		if($user_count > 0)
		{ //this login has been located, create session
			$_SESSION['is_logged_in'] = true;
			$_SESSION['login_id'] = $user[0]['user_id'];
			$_SESSION['login_email'] = $user[0]['email'];
			$_SESSION['login_firstname'] = $user[0]['firstname'];
			$_SESSION['login_lastname'] = $user[0]['lastname'];

			$redirect = $app->request->post('redirect', '/users');
			header("location: ".$redirect);
		}
		else
		{
			//sorry you not here, die you mofo
			$_SESSION['is_logged_in'] = false;
			header("location: /dashboard/login?error=true");
				exit;
		}
		break;
	case 'logout':
		$_SESSION['is_logged_in'] = false;
		$_SESSION['login_id'] = 0;
		$_SESSION['login_email'] = null;
		$_SESSION['login_firstname'] = null;
		$_SESSION['login_lastname'] = null;
		session_destroy();
	break;
}

if($_SESSION['is_logged_in'])
{
	header("location: /dashboard/users");
	exit;
}

$view = 'dashboard/login.html';
require_once(SYSTEM_VIEWS . '/base.dashboard.html');