<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjUserModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'users';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'role_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'password', 'type' => 'blob', 'default' => ':NULL', 'encrypt' => 'AES'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'phone', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'last_login', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T'),
		array('name' => 'is_active', 'type' => 'enum', 'default' => 'F'),
        array('name' => 'ip', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'added_by', 'type' => 'int', 'default' => '0'),
        array('name' => 'confirm_code', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'credit_password', 'type' => 'blob', 'default' => ':NULL', 'encrypt' => 'AES'),
        array('name' => 'address', 'type' => 'text', 'default' => ':NULL'),
        array('name' => 'zipcode', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'district', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'user_code', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'agent_type', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'open_time', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'close_time', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'mobile_app_status', 'type' => 'tinyint', 'default' => '1'),
        array('name' => 'discount_status', 'type' => 'tinyint', 'default' => '0')
	);
	
	protected $validate = array(
		'rules' => array(
			'role_id' => array(
				'pjActionNumeric' => true,
				'pjActionRequired' => true
			),
			'email' => array(
				'pjActionEmail' => true,
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			),
			'password' => array(
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			),
			'name' => array(
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			),
			'status' => 'pjActionRequired'
		)
	);

	public static function factory($attr=array())
	{
		return new pjUserModel($attr);
	}
}
?>