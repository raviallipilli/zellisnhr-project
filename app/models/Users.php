<?php
function isUserAuth()
{
	if($_SESSION['is_logged_in'])
	{
		if(!$_SESSION['login_user_id'] > 0){
			header('location: /dashboard');
			//echo 'user_id is not greater then 0';
			exit;
		}
	}else{
		header('location: /dashboard');
		//echo 'admin session is not set';
		exit;
	}
}

class Users
{
	const DBTable = 'users';

	public $user_id;
	public $firstname;
	public $lastname;
	public $email;
	public $password;
	public $telephone;
	public $last_login;
	public $createdate;
	public $updatedate;
	public $is_deleted;
	public $company;

	private $__pdo;

	public function __construct()
	{
		$this->user_id = 0;
		$this->firstname = null;
		$this->lastname = null;
		$this->email = null;
		$this->password = null;
		$rhis->telephone = null;
		$this->last_login = date('Y-m-d H:i:s');
		$this->createdate = date('Y-m-d H:i:s');
		$this->updatedate = date('Y-m-d H:i:s');
		$this->is_deleted = 0;
		$this->company = null;

		$this->__pdo = new DB_PDO;
	}

	public function Save($params = array())
	{
		$params['createdate'] = $this->createdate;
		$params['updatedate'] = $this->updatedate;

		//the password field needs to be sha1
		$params['password'] = sha1($params['password'].PASSWORD_SALT);

		$this->user_id = $this->__pdo->Insert(self::DBTable, $params);

		//output the data that was inserted
		$where['user_id'] = $this->user_id;
		return $this->Select($where);
	}

	public function Update($params = array(), $where = array())
	{
		$params['updatedate'] = $this->updatedate;

		if($params['password'] == '')
		{
			//remove it from the array
			unset($params['password']);
		}
		else
		{
			$params['password'] = sha1($params['password'].PASSWORD_SALT);

		}
		
		$this->__pdo->Update(self::DBTable, $params, $where);

		//output the data that was updated
		return $this->Select($where);
	}

	public function Quick_update($column = null, $value = null)
	{
		$sql = 'update '.self::DBTable.' set
			'.$column.' = :value,
			updatedate = :updatedate
			where user_id = :user_id';
		$this->__pdo->Prepare_query($sql);
			$params['value'] = $value;
			$params['updatedate'] = $this->Updatedate;
			$params['user_id'] = $this->user_id;
		$this->__pdo->Bind_values($params);
		$this->__pdo->Execute();
	}

	public function Get_users($limit = null, $offset = null)
	{
		$sql = 'select u.*
			from '.self::DBTable.' u
			where 
			u.is_deleted = 0
			order by u.firstname asc, u.lastname asc';
		$this->__pdo->Prepare_query($sql);
		return $data = $this->__pdo->Get_data_array_assoc();
	}

	public function Delete($user_id = 0)
	{
		$params['is_deleted'] = 1;
		$where['user_id'] = $user_id;
		$this->__pdo->Update(self::DBTable, $params, $where);
	}

	public function Select($where = array(), $limit = null, $start = null, $order_by = null, $select_cols = null)
	{
		$data = $this->__pdo->Select(self::DBTable, $where, $limit, $start, $order_by, $select_cols);
		return $data;
	}

	public function Auth_user($where = array())
	{
		$data = $this->__pdo->Select(self::DBTable, $where, null, null, $orderby);
		$data_count = count($data);
		if($data_count > 0)
		{
			//update the last login field
			$params['last_login'] = $this->last_login;
			$where['user_id'] = $data[0]['user_id'];

			$this->__pdo->Update(self::DBTable, $params, $where);
		}
		return $data;
	}


	public function Generate_sha1($primary_key_column = null, $primary_key_value = 0, $sha1_column = null)
	{
		//create an sha1
		$sha1_value = substr(sha1($primary_key_value), 0, 15);
		$this->Quick_update($primary_key_column, $primary_key_value, $sha1_column, $sha1_value);
		return $sha1_value;
	}
}
