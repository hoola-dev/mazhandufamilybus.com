<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjBusLocationModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'buses_locations';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'bus_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'location_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'departure_time', 'type' => 'time', 'default' => ':NULL'),
        array('name' => 'arrival_time', 'type' => 'time', 'default' => ':NULL'),
        array('name' => 'day_num', 'type' => 'tinyint', 'default' => ':0'),
        array('name' => 'delay_date', 'type' => 'date', 'default' => ':NULL'),
        array('name' => 'delay_minutes', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjBusLocationModel($attr);
	}
	
	public function getLocationId($bus_id, $location_id_str)
	{
		$location_id = null;
		$location_arr = $this
			->reset()
			->where('bus_id', $bus_id)
			->where("(location_id IN($location_id_str))")
			->limit(1)
			->findAll()
			->getData();
		if(!empty($location_arr))
		{
			$location_id = $location_arr[0]['location_id'];
		}
		return $location_id;
	}
}
?>