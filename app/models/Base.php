<?php
class Base
{
	public function __construct()
	{
		$this->__pdo = new DB_PDO;

		$this->is_deleted = 0;
		$this->createdate = date('Y-m-d H:i:s');
		$this->updatedate = date('Y-m-d H:i:s');
	}

	public function Save($params = array(), $primary_key_column = null)
	{
		$params['createdate'] = $this->createdate;
		$params['updatedate'] = $this->updatedate;

		$primary_key = $this->__pdo->Insert($this->DBTable, $params);

		//output the data that was inserted
		if(!is_null($primary_key_column))
		{
			$where = array();
			$where[$primary_key_column] = $primary_key;
			return $this->Select($where);
		}
		return array();
	}

	public function Update($params = array(), $where = array())
	{
		$params['updatedate'] = $this->updatedate;

		$this->__pdo->Update($this->DBTable, $params, $where);

		//output the data that was updated
		return $this->Select($where);
	}

	public function Quick_update($primary_key_column = null, $primary_key_value = null, $column = null, $value = null)
	{
		$sql = 'update '.$this->DBTable.' set
			'.$column.' = :value,
			updatedate = :updatedate
			where '.$primary_key_column.' = :primary_key_value';
		//echo $sql.'<br >';
		$this->__pdo->Prepare_query($sql);
			$params = array();
			$params['value'] = $value;
			$params['updatedate'] = $this->updatedate;
			$params['primary_key_value'] = $primary_key_value;
		$this->__pdo->Bind_values($params);
		//pprint_r($params);
		$this->__pdo->Execute();
	}

	public function Delete($primary_key_column = null, $primary_key_value = 0)
	{
		$where = array();
		$where[$primary_key_column] = $primary_key_value;
		$this->__pdo->Delete($this->DBTable, $where);
	}

	/*dont actually delete the record, but mark it as deleted*/
	public function Delete_soft($primary_key_column = null, $primary_key_value = 0)
	{
		$params = array();
		$params['is_deleted'] = 1;

		$where = array();
		$where[$primary_key_column] = $primary_key_value;

		$this->__pdo->Update($this->DBTable, $params, $where);
	}

	public function Select($where = array(), $limit = null, $start = null, $order_by = null, $select_cols = null)
	{
		$data = $this->__pdo->Select($this->DBTable, $where, $limit, $start, $order_by, $select_cols);
		return $data;
	}

	public function Insert($where = array(), $limit = null, $start = null, $select_cols = null)
	{
		$data = $this->__pdo->Insert($this->DBTable, $where, $limit, $start, $select_cols);
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