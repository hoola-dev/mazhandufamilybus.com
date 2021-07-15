<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjBusTypeModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'bus_types';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'seats_map', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'seats_count', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'seat_layout', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	public $i18n = array('name');
	
	public static function factory($attr=array())
	{
		return new pjBusTypeModel($attr);
	}
}
?>