<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjPrintSettingsModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'print_settings';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'group_identifier', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'booking_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'print_limit', 'type' => 'tinyint', 'default' => ':NULL')
		
	);
	
	public static function factory($attr=array())
	{
		return new pjPrintSettingsModel($attr);
	}
}
?>