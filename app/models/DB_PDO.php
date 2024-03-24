<?php
class DB_PDO
{
	static private $__dbh; //to prevent the object always being recreated
	private $__dsn;
	private $__username;
	private $__password;
	private $__stmt;
	private $__memcache;

	public function __construct()
	{
		$this->__dsn = "mysql:charset=utf8;dbname=".DB_DATABASE.";host=".DB_HOST;
		$this->__username = DB_USERNAME;
		$this->__password = DB_PASSWORD;

		// Connection to the database
		if(!self::$__dbh) 
		{ 
			try {
				self::$__dbh = new PDO($this->__dsn, $this->__username, $this->__password, array(
					PDO::ATTR_PERSISTENT => false, 
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'", 
					PDO::MYSQL_ATTR_LOCAL_INFILE => true)
				);
				self::$__dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			} catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
				exit;
			}
		}
		return self::$__dbh;
	}

	/**
	 * method select.
	 * 	- retrieve information from the database, as an array
	 *
	 * @param string $table - the name of the db table we are retreiving the rows from
	 * @param array $params - associative array representing the WHERE clause filters
	 * @param int $limit (optional) - the amount of rows to return
	 * @param int $start (optional) - the row to start on, indexed by zero
	 * @param array $order_by (optional) - an array with order by clause
	 * @return mixed - associate representing the fetched table row, false on failure
	* @return mixed - associate representing the fetched table row, false on failure
	*/
	public function Select($table, $where = array(), $limit = null, $start = null, $order_by = array(), $select_cols = array())
	{
		//check to see if any bespoke columns have been 
		if($select_cols != null && count($select_cols) > 0)
		{
			$sql_cols = implode(', ', $select_cols);
		}
		else
		{
			$sql_cols = '*';
		}
	
		// building query string
		$sql = 'select '.$sql_cols.' from '.$table;
		// append WHERE if necessary
		$sql .= ( count($where) > 0 ? ' where ' : '' );

		$add_and = false;
		// add each clause using parameter array
		if (count($where) > 0)
		{
			foreach ($where as $key => $val)
			{
				// only add AND after the first clause item has been appended
				if ($add_and) 
				{
					$sql .= ' AND ';
				} else 
				{
					$add_and = true;
				}

				// append clause item
				$sql .= $key .' = :'.$key.'';
			}
		}

		// add the order by clause if we have one
		if (is_countable($order_by) > 0) {
			$sql .= ' order by ';
			$add_comma = false;
			foreach ($order_by as $column => $order)
			{
				if ($add_comma) 
				{
					$sql .= ', ';
				}
				else 
				{
					$add_comma = true;
				}
				$sql .= $column.' '.$order;
			}
		}

		// add the limit clause if we have one
		if (!is_null($limit))
		{
			$sql .= ' limit '.(!is_null($start) ? $start.', ': '') . $limit;
		}

		// now we attempt to retrieve the row using the sql string
		try 
		{
			$this->Prepare_query($sql);

			// bind each parameter in the array			
			foreach ($where as $key => $val) 
			{
				$this->__stmt->bindValue(':'.$key, $val);
			}

			$this->Execute();
			$data = $this->__stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}
		catch (PDOException $e) 
		{
			echo 'Select Query failed: ' . $e->getMessage().' - '.$e->getCode(). ' - '. $sql.' - '.print_r($where).'<br />';
			exit;
		}
	}

	/**
	 * method insert.
	 * 	- adds a row to the specified table
	 *
	 * @param string $table - the name of the db table we are adding row to
	 * @param array $params - associative array representing the columns and their respective values
	 * @return mixed - new primary key of inserted table, false on failure
	 */
	public function Insert($table, $params = array(), $ignore_param = false)
	{
		// first we build the sql query string
		$columns_str = '(';
		$values_str = 'VALUES (';
		$add_comma = false;

		// add each parameter into the query string
		foreach ($params as $key => $val) 
		{
			// only add comma after the first parameter has been appended
			if ($add_comma) 
			{
				$columns_str .= ', ';
				$values_str .= ', ';
			}
			else 
			{
				$add_comma = true;
			}

			// now append the parameter
			$columns_str .= $key;
			$values_str .= ':'.$key;
		}

		// close the builder strings
		$columns_str .= ') ';
		$values_str .= ')';

		$ignore = ($ignore_param) ? ' ignore ' : '';

		// build final insert string
		$sql = 'insert '.$ignore.' into '.$table.' '.$columns_str.' '.$values_str;
		/*echo $sql."<br />";
		pprint_r($params, false);*/

		// now we attempt to write this row into the database
		try {
			$this->Prepare_query($sql);

			$this->Bind_values($params);
			$this->Execute();
			return $this->Get_insert_id();
		}
		catch (PDOException $e) {
			echo 'Insert Query failed: ' . $e->getMessage().' - '.$e->getCode(). ' - '. $sql.'<br />';
			exit;
		}
	}

	/**
	 * method update.
	 * 	- updates a row to the specified table
	 *
	 * @param string $table - the name of the db table we are adding row to
	 * @param array $params - associative array representing the columns and their respective values to update
	 * @param array $where (Optional) - the where clause of the query
	 * @return int|bool - the amount of rows updated, false on failure
	 */
	public function Update($table, $params = array(), $where = array()) 
	{
		// build the set part of the update query by
		// adding each parameter into the set query string
		$add_comma = false;
		$set_string = '';
		foreach ($params as $key => $val) {
			// only add comma after the first parameter has been appended
			if ($add_comma) {
				$set_string .= ', ';
			} else {
				$add_comma = true;
			}

			// now append the parameter
			$set_string .= $key. ' = :'.$key;
		}

		// lets add our where clause if we have one
		$where_string = '';
		if(count($where) > 0) {
			// load each key value pair, and implode them with an AND
			$where_array = array();
			foreach($where as $key => $val) {
				$where_array[] = $key. ' = :where_'.$key;
			}
			// build the final where string
			$where_string = 'where '.implode(' AND ', $where_array);
		}

		// build final update string
		$sql = 'update '.$table.' SET '.$set_string.' '.$where_string;
		/*echo $sql."<br />";
		pprint_r($params, false);*/

		// now we attempt to write this row into the database
		try 
		{
			$this->Prepare_query($sql);

			// bind each parameter in the array			
			$this->Bind_values($params);

			// bind each where item in the array			
			foreach ($where as $key => $val) {
				$this->__stmt->bindValue(':where_'.$key, $val);
			}

			// execute the update query
			$this->Execute();
		}
		catch (PDOException $e) 
		{
			echo 'Update Query failed: ' . $e->getMessage().' - '.$e->getCode(). ' - '. $sql.'<br />';
			exit;
		}
	}

	/**
	 * method delete.
	 * 	- deletes rows from a table based on the parameters
	 *
	 * @param table - the name of the db table we are deleting the rows from
	 * @param params - associative array representing the WHERE clause filters
	 * @return bool - associate representing the fetched table row, false on failure
	 */
	public function Delete($table, $params = array()) 
	{
		$params_count = count($params);
	
		// building query string
		$sql = 'delete from '.$table;
		// append WHERE if necessary
		$sql .= ( count($params) > 0 ? ' WHERE ' : '' );

		$add_and = false;
		// add each clause using parameter array
		foreach ($params as $key => $val) {
			// only add AND after the first clause item has been appended
			if ($add_and) {
				$sql .= ' AND ';
			} else {
				$add_and = true;
			}

			// append clause item
			$sql .= $key.' = :'.$key;
		}

		// now we attempt to retrieve the row using the sql string
		try 
		{
			if($params_count > 0)
			{
				$this->Prepare_query($sql);

				// bind each parameter in the array			
				foreach ($params as $key => $val) {
					$this->__stmt->bindValue(':'.$key, $val);
				}

				// execute the delete query
				$successful_delete = $this->Execute();

				// if we were successful, return the amount of rows updated, otherwise return false
				return ($successful_delete == true) ? $this->__stmt->rowCount() : false;
			}
			else
			{
				return false;
			}
		}
		catch (PDOException $e) 
		{
			echo 'Delete Query failed: ' . $e->getMessage().' - '.$e->getCode(). ' - '. $sql.'<br />';
			exit;
		}
	}
	
	public function Prepare_query($query) 
	{
        try {
			$this->__stmt = self::$__dbh->prepare($query);
			return $this;
		} catch (PDOException $e) {
			echo 'Prepare query failed: ' . $e->getMessage().' - '.$e->getCode(). ' - '. $sql.'<br />';
			exit;
		}
    }

	 public function Bind_values($query_params = array()) 
	{
		if(count($query_params) > 0)
		{
			foreach($query_params as $key => &$value) // the $value is a reference value i.e. &$value (http://www.php.net/manual/en/pdostatement.bindparam.php)
			{			
				switch(true)
				{
					case is_float($value):
						$type = PDO::PARAM_STR;
					break;
					case is_int($value):
					case is_numeric($value):
						$type = PDO::PARAM_INT;
					break;
					case is_bool($value):
						$type = PDO::PARAM_BOOL;
					break;
					case is_null($value):
						$type = PDO::PARAM_NULL;
					break;
					default:
						$type = PDO::PARAM_STR;
					break;
				}
				
				//echo $key." - ".$value." - ".$type."<br />";
				$bind_response = $this->__stmt->bindParam(':'.$key, trim($value), $type);
			}
		}
        return $this;
    }

	public function Get_insert_id()
	{
		return self::$__dbh->lastInsertId(); 
	}

	public function Get_num_rows()
	{
		return $this->__stmt->rowCount();
	}

    public function Get_data_array_assoc() 
	{
        $this->Execute();
        return $this->__stmt->fetchAll(PDO::FETCH_ASSOC);
    }

	public function Get_data_array() 
	{
        $this->Execute();
        return $this->__stmt->fetchAll();
    }

    public function Get_data_assoc() 
	{
        $this->Execute();
        return $this->__stmt->fetch();
    }

	public function Execute() 
	{
		try 
		{
			if(!$this->__stmt->execute())
			{
				echo "\nPDOStatement::errorInfo():\n";
				$arr = $this->__stmt->errorInfo();
				print_r($arr);
			}
		} 
		catch (PDOException $e)
		{
			echo 'Execute query failed: ' . $e->getMessage().' - '.$e->getCode(). '<br />';
			print_r($this->__stmt);
			exit;
		}
    }

	public function Optimize_table($table_name)
	{
		$sql = 'optimize table '.$table_name;
		//$this->Prepare_query($sql); //disabled for innodb tables as it crashes the server esp with loads of rows
		//$this->Execute();
	}

	public function Show_query($query, $params)
    {
        $keys = array();
        $values = array();
       
        # build a regular expression for each parameter
        foreach ($params as $key=>$value)
        {
            if (is_string($key))
            {
                $keys[] = '/'.$key.'/';
            }
            else
            {
                $keys[] = '/[?]/';
            }
           
            if(is_numeric($value))
            {
                $values[] = intval($value);
            }
            else
            {
                $values[] = "'".$value ."'";
            }
        }
        $query = preg_replace($keys, $values, $query, 1, $count);
        echo "<hr />".$query."<hr />";
    }
}