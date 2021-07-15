<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjPaymentModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'payments';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'transction_id', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'booking_ids', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'amount', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'gateway_response', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'created_dt', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'updated_dt', 'type' => 'datetime', 'default' => ':NULL')
	);
	
	protected $validate = array(
		'rules' => array(
			'transction_id' => array(
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			),
			'booking_ids' => array(
				'pjActionRequired' => true,
				'pjActionNotEmpty' => true
			)
		)
	);

	public static function factory($attr=array())
	{
		return new pjPaymentModel($attr);
	}
}
?>