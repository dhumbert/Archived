<?php

ini_set('display_errors', 1);

require_once("php_depinj.php");


class UserModel
{
	private $_dataAccess;
	
	public function __construct(IDataAccess $dataAccess)
	{
		$this->_dataAccess = $dataAccess;
	}
	
	public function getUserAge()
	{
		return $this->_dataAccess->query('SELECT age FROM users WHERE user_id = 1');
	}
}


interface IDataAccess
{
	public function query($sql);
}

class FakeDataLayer implements IDataAccess
{	
	public function query($sql)
	{
		return 12;
	}
}

class RealDataLayer implements IDataAccess
{
	public function query($sql)
	{
		mysql_connect('localhost', 'blah', 'blah');
		return 'ERROR';
	}
}

echo '<h1>User Model</h1>';

$container = Container::getInstance();
$container->register('userModel', 'UserModel');
$container->register('activeDataAccess', 'FakeDataLayer', 'IDataAccess');

$userModel = $container->resolve('userModel');
echo $userModel->getUserAge();
?>