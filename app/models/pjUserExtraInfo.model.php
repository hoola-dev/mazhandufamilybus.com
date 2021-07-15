<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjUserExtraInfoModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'users_extra_info';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'title', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'first_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'last_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'country', 'type' => 'int', 'default' => ':NULL'),
	);
	
	protected $validate = array(
		'rules' => array(
			'user_id' => array(
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			),
			'title' => array(
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			),
			'first_name' => array(
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			),
			'last_name' => array(
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			)
		)
	);

	public static function factory($attr=array())
	{
		return new pjUserExtraInfoModel($attr);
	}
}
?>