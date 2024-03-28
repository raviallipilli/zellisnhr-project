<?php
class Password_Reset extends Base
{

	public function __construct()
	{
		//parent class __constructed included
		parent::__construct();

		$this->DBTable = 'password_reset';
	}
}