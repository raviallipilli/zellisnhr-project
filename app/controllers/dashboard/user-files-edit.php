<?php 
DEFINE('PAGE_URL', '/dashboard/user-files/');
DEFINE('PAGE_URL_EDIT', '/dashboard/user-files-edit/');

$page_name_main = 'user-files';
$page_name = 'user-files';

$f = new User_Files;
$u = new Uploader;

//look out for any params
$request = $app->request();
$file_id = is_numeric($args[0]) ? $args[0] : 0; //id argument from the URL
$do = $request->post('do');

//get the status to display success message
$status = $request->get('status', null);

switch($do)
{
	case 'edit':

		//get the post values and assign it to $params
		$params = $request->post();
		$u->Directory = FILES_STORE_ATTACHMENTS;
		$u->Allowed_extensions_bespoke = array('docx', 'doc','jpeg','jpg','txt', 'pdf', 'rtf','csv'); //this is optional if you want to define something bespoke

		//upload the file
		$u->Filename = $_FILES['original_filename']['name'];
		$u->Filename_temp = $_FILES['original_filename']['tmp_name'];
		$file_array = $u->Upload();
		if($file_array['status'] == 200)
		{
			$params['original_filename'] = $file_array['filename'];
		}
		else
		{
			$params['original_filename'] = $request->post('original_filename');
		}

		$params['user_id'] = $_SESSION['login_id'];
		//remove the do param from the array
		unset($params['do']);
		$update_close = $params['update_close'];
		unset($params['update_close']);
		if($file_id == 0)
		{
			$data = $f->Save($params, 'id');
			$file_id = $data[0]['id'];
		}
		else
		{
			//push in the primary key into the array as a where array
			$where = array();
			$where['id'] = $file_id;
			$data = $f->Update($params, $where);
		}

		if(isset($update_close))
		{
			header('location: '.PAGE_URL);
			exit;
		}
		header('location: '.PAGE_URL_EDIT.$file_id.'?status=updated');
		exit;
	break;
}

//load up the jobs-applications information
$where = array();
$where['id'] = $file_id;
$data = $f->Select($where);
$data = $data[0];

//what form elements does this page need?
$form_elements[] = array('type' => 'textbox', 'name' => 'title', 'value' => $data['title'], 'title' => 'Title', 'attributes' => array('required' => 'required'));
$form_elements[] = array('type' => 'file', 'name' => 'original_filename', 'value' => $data['original_filename'], 'title' => 'Filename', 'data' => '/'.FILES_STORE_ATTACHMENTS, 'attributes' => array('required' => 'required'));

$render = new Form_render($form_elements);

$view = 'dashboard/user-files-edit.html';
require(SYSTEM_VIEWS . '/base.dashboard.html');