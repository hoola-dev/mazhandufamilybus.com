<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjSettingsModel extends pjAppModel
{
	protected $primaryKey = 'st_id';
	
	protected $table = 'settings';
	
	protected $schema = array(
		array('name' => 'st_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'st_type', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'st_value', 'type' => 'varchar', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjSettingsModel($attr);
	}
}
?>