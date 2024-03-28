<?php 
$users = new Users;
$pr = new Password_Reset;

//look out for any params
$request = $app->request();

//get the status to display success message
$status = $request->get('status', null);

$do = $request->post('do');
$email = $request->params('email');
$where = array();
$where['email'] = $email;
$data = $users->Select($where);
$user_id = $data[0]['user_id'];

switch($do)
{
	case 'reset':
		//get the post values and assign it to $params
		$params = $request->params();

		unset($params['do']);

		if(isset($params["email"]))
		{
			$password = $params["password"];
			$confirm_password = $params["confirm-password"];
			$email = $params["email"];
			if ($password != $confirm_password)
			{
				header("location: /dashboard/reset-password/?status=error");
				exit;
			}
			if($password == "" || $confirm_password == "")
			{
				header("location: /dashboard/reset-password/?status=empty");
				exit;			
			} 
			else 
			{
				// update the users with new password
				$param = array();
				$param['password'] = $password;
				$where = array();
				$where['user_id'] = $user_id;
				$data = $users->Update($param, $where);
				$email = $data[0]['email'];

				// and remove the entry in password reset 
				$where = array();
				$where['email'] = $email;
				$prData = $pr->Select($where);
				$email = $prData[0]['email'];
				$pr->Delete('email', $email);
			}
		}

		header("location: /dashboard/login/?status=password-reset");
		exit;
	break;
}

//load up the users information
$where = array();
$where['email'] = $email;
$data = $pr->Select($where);
$data = $data[0];

$expDate = $data['expDate'] ? $data['expDate'] : date("Y-m-d H:i:s");
$curDate = date("Y-m-d H:i:s");

//what form elements does this page need?
$form_elements[] = array('type' => 'hidden', 'name' => 'email', 'value' => $data['email']);
$form_elements[] = array('type' => 'password', 'name' => 'password', 'value' => null, 'title' => 'New Password', 'attributes' => array('required' => 'required'), 'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}');
$form_elements[] = array('type' => 'password', 'name' => 'confirm-password', 'value' => null, 'title' => 'Confirm Password', 'attributes' => array('required' => 'required'), 'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}');
$render = new Form_render($form_elements);

$view = 'dashboard/reset-password.html';
require(SYSTEM_VIEWS . '/base.dashboard.html');