<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjTicketModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'tickets';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'bus_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'seats_count', 'type' => 'int', 'default' => ':NULL')
	);
	
	public $i18n = array('title');
	
	public static function factory($attr=array())
	{
		return new pjTicketModel($attr);
	}
}
?>