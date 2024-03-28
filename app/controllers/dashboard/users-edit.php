<?php 
DEFINE('PAGE_URL', '/dashboard/users/');
DEFINE('PAGE_URL_EDIT', '/dashboard/users-edit/');

$page_name_main = '';
$page_name = 'users';

$users = new Users;

//look out for any params
$request = $app->request();
$user_id = is_numeric($args[0]) ? $args[0] : 0; //user_id argument from the URL

//get the status to display success message
$status = $request->get('status', null);

$do = $request->post('do');

switch($do)
{
	case 'edit':
		//get the post values and assign it to $params
		$params = $request->post();

		unset($params['do']);
		$update_close = $params['update_close'];
		unset($params['update_close']);

		//push in the primary key into the array as a where array
		$where = array();
		$where['user_id'] = $user_id;
		$where['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
		$data = $users->Update($params, $where);
		$user_id = $data[0]['user_id'];

		if(isset($update_close))
		{
			header('location: '.PAGE_URL);
			exit;
		}
		header('location: '.PAGE_URL_EDIT.$user_id.'?status=updated');
		exit;
	break;
}

//load up the users information
$where = array();
$where['user_id'] = $user_id;
$data = $users->Select($where);
$data = $data[0];

//what form elements does this page need?
$form_elements[] = array('type' => 'textbox', 'name' => 'firstname', 'value' => $data['firstname'], 'title' => 'Firstname', 'attributes' => array('required' => 'required'));
$form_elements[] = array('type' => 'textbox', 'name' => 'lastname', 'value' => $data['lastname'], 'title' => 'Lastname', 'attributes' => array('required' => 'required'));
$form_elements[] = array('type' => 'textbox', 'name' => 'email', 'value' => $data['email'], 'title' => 'Email', 'attributes' => array('required' => 'required'));
$form_elements[] = array('type' => 'password', 'name' => 'password', 'value' => null, 'title' => 'Password', 'attributes' => array('disabled' => 'disabled'));
$form_elements[] = array('type' => 'textbox', 'name' => 'company', 'value' => $data['company'], 'title' => 'Company', 'attributes' => array('required' => 'required'));
$form_elements[] = array('type' => 'textbox', 'name' => 'telephone', 'value' => $data['telephone'], 'title' => 'Telephone');

$render = new Form_render($form_elements);

$view = 'dashboard/users-edit.html';
require(SYSTEM_VIEWS . '/base.dashboard.html');