<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjRouteDetailModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'route_details';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'route_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'from_location_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'to_location_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjRouteDetailModel($attr);
	}
}
?>