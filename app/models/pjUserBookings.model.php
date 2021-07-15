<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjUserBookingsModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'user_bookings';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'booking_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	protected $validate = array(
		'rules' => array(
			'user_id' => array(
				'pjActionNumeric' => true,
				'pjActionRequired' => true
			),
			'booking_id' => array(
				'pjActionNumeric' => true,
				'pjActionRequired' => true
			)
		)
	);

	public static function factory($attr=array())
	{
		return new pjUserBookingsModel($attr);
	}
}
?>