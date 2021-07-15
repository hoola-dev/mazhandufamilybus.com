<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjBusDelayModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'bus_delays';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'bus_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'agent_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'delayed_by', 'type' => 'time', 'default' => ':NULL'),
		array('name' => 'created_dt', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'updated_dt', 'type' => 'datetime', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjBusDelayModel($attr);
	}
}
?>