<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjDistrictModel extends pjAppModel
{
	protected $primaryKey = 'dt_id';
	
	protected $table = 'districts';
	
	protected $schema = array(
		array('name' => 'dt_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'dt_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'dt_code', 'type' => 'varchar', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjDistrictModel($attr);
	}
}
?>