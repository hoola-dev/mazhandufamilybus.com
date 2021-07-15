<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjCreditModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'credits';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'credit', 'type' => 'decimal', 'default' => ':0'),
        array('name' => 'commission_percent', 'type' => 'decimal', 'default' => ':0'),
        array('name' => 'total_commission', 'type' => 'decimal', 'default' => ':0'),
        array('name' => 'added_by', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
	);
	
	public static function factory($attr=array())
	{
		return new pjCreditModel($attr);
	}
}
?>