<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjUserLogModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'user_log';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'mobile', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'login_date', 'type' => 'datetime', 'default' => ':NOW()'),
	);
	
	protected $validate = array(
		'rules' => array(
			'name' => array(
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			),
			'email' => array(
				'pjActionEmail' => true,
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			),
			'mobile' => array(
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			)
		)
	);

	public static function factory($attr=array())
	{
		return new pjUserLogModel($attr);
	}
}
?>