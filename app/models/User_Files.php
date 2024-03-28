<?php
class User_Files extends Base
{

	public function __construct()
	{
		//parent class __constructed included
		parent::__construct();

		$this->DBTable = 'files';
	}

	public function Get_all()
	{	
		$sql= 'select u.email,
		f.id,
		f.title,
		f.original_filename,
		f.createdate 	
		FROM '.$this->DBTable.' f 
		left join users u on u.user_id = f.user_id
		where f.user_id = '.$_SESSION['login_id'].' and
		f.is_deleted = 0
		order by f.title asc';
		
		$this->__pdo->Prepare_query($sql);
		return $data = $this->__pdo->Get_data_array_assoc();
	}
}