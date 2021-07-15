<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjBusModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'buses';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'route_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'bus_type_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'start_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'end_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'departure_time', 'type' => 'time', 'default' => ':NULL'),
		array('name' => 'arrival_time', 'type' => 'time', 'default' => ':NULL'),
		array('name' => 'recurring', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'set_seats_count', 'type' => 'enum', 'default' => 'F'),
        array('name' => 'discount', 'type' => 'decimal', 'default' => '0'),
        array('name' => 'is_international_bus', 'type' => 'tinyint', 'default' => '0')
	);
	
	public static function factory($attr=array())
	{
		return new pjBusModel($attr);
	}
	
	public function getBusIds($date, $pickup_id, $return_id)
	{
		$bus_id_arr = array();
		$day_of_week = strtolower(date('l', strtotime($date)));
		$arr = $this
			->reset()
			->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')")
			->where("(t1.route_id IN(SELECT TRD.route_id FROM `".pjRouteDetailModel::factory()->getTable()."` AS TRD WHERE TRD.from_location_id = $pickup_id AND TRD.to_location_id = $return_id))")
			->where("(t1.route_id IN(SELECT `TR`.id FROM `".pjRouteModel::factory()->getTable()."` AS `TR` WHERE `TR`.status='T'))")
			->findAll()
			->getData();
		
		$pjBusLocationModel = pjBusLocationModel::factory();
		foreach($arr as $k => $v)
		{
			$pickup_arr = $pjBusLocationModel->reset ()->where ( 'bus_id', $v['id'] )->where ( "location_id", $pickup_id )->limit ( 1 )->findAll ()->getData ();

            if (! empty ( $pickup_arr )) {
                $departure_dt = $date . ' ' . $pickup_arr [0] ['departure_time'];

                if ($pickup_arr[0]['delay_date'] && $pickup_arr[0]['delay_date'] == $date && $pickup_arr[0]['delay_minutes'] > 0) {
                    $minutes_to_add = $pickup_arr[0]['delay_minutes'];

                    $time = new DateTime($departure_dt);
                    $time->add(new DateInterval('PT'.$minutes_to_add.'M'));

                    $departure_dt = $time->format('Y-m-d H:i:s');
                }
				
				if($departure_dt >= date('Y-m-d H:i:s'))
				{
					$bus_id_arr[] = $v['id'];
				}
			}
			
		}
		return $bus_id_arr;
	}
}
?>