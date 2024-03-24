<?php 
$users = new Users;
$u = new Uploader;

//look out for any params
$request = $app->request();
$user_id = is_numeric($args[0]) ? $args[0] : 0; //user_id argument from the URL

//get the status to display success message
$status = $request->get('status', null);

$do = $request->post('do');
$email = $request->post('email');
$where['email'] = $email;
$data = $users->Select($where);
if ($data)
{
	header("location: /dashboard/register/register?status=user_already_exists");
	exit;
}
switch($do)
{
	case 'register':
		//get the post values and assign it to $params
		$params = $request->post();

		unset($params['do']);
		$update_close = $params['update_close'];
		unset($params['update_close']);

		if($user_id == 0)
		{
			$data = $users->Save($params, 'user_id');
			$user_id = $data[0]['user_id'];

			//generate the sha1 value
			$users->Generate_sha1('user_id', $user_id, 'user_sha1');
		}
		else
		{
			//push in the primary key into the array as a where array
			$where = array();
			$where['user_id'] = $user_id;
			$data = $users->Update($params, $where);
			$user_id = $data[0]['user_id'];

			//generate the sha1 value
			$users->Generate_sha1('user_id', $user_id, 'user_sha1');
		}

		header("location: /dashboard/login");
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
$form_elements[] = array('type' => 'password', 'name' => 'password', 'value' => null, 'title' => 'Password');
$form_elements[] = array('type' => 'textbox', 'name' => 'company', 'value' => $data['company'], 'title' => 'Company');
$form_elements[] = array('type' => 'textbox', 'name' => 'telephone', 'value' => $data['telephone'], 'title' => 'Telephone');

$render = new Form_render($form_elements);

$view = 'dashboard/register.html';
require(SYSTEM_VIEWS . '/base.dashboard.html');