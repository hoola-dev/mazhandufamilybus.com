<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAgentTypeModel extends pjAppModel
{
	protected $primaryKey = 'at_id';
	
	protected $table = 'agent_types';
	
	protected $schema = array(
		array('name' => 'at_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'at_type', 'type' => 'varchar', 'default' => '0')
	);
	
	public static function factory($attr=array())
	{
		return new pjAgentTypeModel($attr);
	}
}
?>