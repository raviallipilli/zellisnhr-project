<?php 
$users = new Users;
$pr = new Password_Reset;

//look out for any params
$request = $app->request();

//get the status to display success message
$status = $request->get('status', null);

$do = $request->post('do');
$requestEmail = $request->post('email');
$where['email'] = $requestEmail;
$data = $users->Select($where);
$email = $data[0]['email'];

switch($do)
{
	case 'forgot':
		//get the post values and assign it to $params
		$params = $request->post();

		unset($params['do']);

		if($params['email'])
		{
			$expFormat = mktime(date("H"), date("i"), date("s"), date("m") ,date("d")+1, date("Y"));
			$expDate = date("Y-m-d H:i:s",$expFormat);
			$token = md5(2418*2+is_numeric($params['email']));
			$addtoken = substr(md5(uniqid(rand(),1)),3,10);
			$token = $token . $addtoken;

			$where = array();
			$where['email'] = $params['email'];
			$where['token'] = $token;
			$where['expDate'] = $expDate;
			
			$data = $pr->Insert($where);
		}

		$href='http://'.$_SERVER['HTTP_HOST'].'/dashboard/reset-password/reset/?
		token='.$token.'&email='.$email.'&action=reset';
		$output = '<p>Dear User,</p>';
		$output .= '<p>Please click on the following link to reset your password.</p>';
		$output .= '<p>-------------------------------------------------------------</p>';
		$output .= '<p><a href="'.$href.'" target="_blank">Reset Password</a></p>'; 
		$output .= '<p>-------------------------------------------------------------</p>';
		$output .= '<p>Please be sure to copy the entire link into your browser.
		The link will expire after 1 day for security reason.</p>';
		$output .= '<p>If you did not request this forgotten password email, no action 
		is needed, your password will not be reset. However, you may want to log into 
		your account and change your security password as someone may have guessed it.</p>';   
		$output .= '<p>Thanks,</p>';
		$output .= '<p>Support Team</p>';
		$subject = "Password Recovery ";

		$to = $email;
		$from = 'luckyravi17@gmail.com';
		$fromName = 'Password Recovery';
		$subject = $subject;
		$message = $output.PHP_EOL;
		$sent = send_email($to, $from, $fromName, $subject, $message, null);
		if(!$sent)
		{
			header('location: /dashboard/forgot-password/?status=failed');
			exit;		}
		else
		{
			header('location: /dashboard/login/?status=email-sent');
			exit;
		}

	break;
}

//load up the users information
$where = array();
$where['email'] = $email;
$data = $pr->Select($where);
$data = $data[0];

//what form elements does this page need?
$form_elements[] = array('type' => 'textbox', 'name' => 'email', 'value' => $data['email'], 'title' => 'Email', 'attributes' => array('required' => 'required'));

$render = new Form_render($form_elements);

$view = 'dashboard/forgot-password.html';
require(SYSTEM_VIEWS . '/base.dashboard.html');