<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjCreditAllocationModel extends pjAppModel
{
	protected $primaryKey = 'ca_id';
	
	protected $table = 'credit_allocation';
	
	protected $schema = array(
		array('name' => 'ca_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'ca_sub_agent_id', 'type' => 'int', 'default' => '0'),
        array('name' => 'ca_credit_added', 'type' => 'decimal', 'default' => '0'),
        array('name' => 'ca_commission_percent', 'type' => 'decimal', 'default' => '0'),
        array('name' => 'ca_commission', 'type' => 'decimal', 'default' => '0'),
        array('name' => 'ca_total_commission', 'type' => 'decimal', 'default' => '0'),
        array('name' => 'ca_added_by', 'type' => 'int', 'default' => '0'),
        array('name' => 'ca_added_on', 'type' => 'datetime', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjCreditAllocationModel($attr);
	}
}
?>