<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjCommissionModel extends pjAppModel
{
	protected $primaryKey = 'cm_id';
	
	protected $table = 'commission';
	
	protected $schema = array(
		array('name' => 'cm_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'cm_user_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'cm_credit_added', 'type' => 'decimal', 'default' => '0'),
		array('name' => 'cm_commission_percent', 'type' => 'decimal', 'default' => '0'),
        array('name' => 'cm_commission', 'type' => 'decimal', 'default' => '0'),
        array('name' => 'cm_total_commission', 'type' => 'decimal', 'default' => '0'),
		array('name' => 'cm_added_by', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'cm_added_on', 'type' => 'datetime', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjCommissionModel($attr);
	}
}
?>