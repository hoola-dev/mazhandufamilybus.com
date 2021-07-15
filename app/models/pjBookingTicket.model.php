<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjBookingTicketModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'bookings_tickets';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'booking_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'ticket_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'qty', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'amount', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'is_return', 'type' => 'enum', 'default' => 'F')
	);
	
	public static function factory($attr=array())
	{
		return new pjBookingTicketModel($attr);
	}
}
?>