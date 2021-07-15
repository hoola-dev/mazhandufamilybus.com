<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjDiscountModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'discounts';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),		
		array('name' => 'booking_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'discount', 'type' => 'decimal', 'default' => ':0'),
        array('name' => 'added_by', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'added_on', 'type' => 'datetime', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjDiscountModel($attr);
	}	
}
?>