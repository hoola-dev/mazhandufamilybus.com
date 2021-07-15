<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjBusDateModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'buses_dates';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'bus_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'date', 'type' => 'date', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjBusDateModel($attr);
	}
}
?>