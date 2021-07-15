<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjDummyBookingModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'bookings_test';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'uuid', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'bus_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'back_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'pickup_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'return_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'is_return', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'booking_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'bus_departure_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'return_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'booking_time', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'booking_route', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'booking_datetime', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'stop_datetime', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'code', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'sub_total', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'tax', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'total', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'deposit', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'payment_method', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'txn_id', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'processed_on', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'is_sent', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'ip', 'type' => 'varchar', 'default' => ':NULL'),
		
		array('name' => 'c_title', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'c_fname', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'c_lname', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'c_phone', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'c_email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'c_company', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'c_notes', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'c_address', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'c_city', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'c_state', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'c_zip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'c_country', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'cc_type', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'cc_num', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'cc_exp', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'cc_code', 'type' => 'varchar', 'default' => ':NULL'),

        array('name' => 'user_id', 'type' => 'int', 'default' => ':0'),
        array('name' => 'updated_by', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'updated_on', 'type' => 'datetime', 'default' => ':NULL'),
        array('name' => 'status_updated_by', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'status_updated_on', 'type' => 'datetime', 'default' => ':NULL'),
        array('name' => 'is_api', 'type' => 'tinyint', 'default' => ':NULL'),
        array('name' => 'is_combined_api', 'type' => 'tinyint', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjDummyBookingModel($attr);
	}
}
?>