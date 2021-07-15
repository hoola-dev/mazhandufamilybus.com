<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjRouteCityModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'routes_cities';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'route_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'city_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'order', 'type' => 'tinyint', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjRouteCityModel($attr);
	}
	
	public function getCity($route_id, $order)
	{
		$arr = $this
			->reset()
			->where('route_id', $route_id)
			->orderBy("`order` $order")
			->findAll()
			->getDataPair('order', 'city_id');
		return $arr;
	}
	
	public function getOrder($route_id, $city_id)
	{
		$arr = $this
			->reset()
			->where('route_id', $route_id)
			->where('city_id', $city_id)
			->findAll()
			->getData();
		return !empty($arr) ? $arr[0]['order'] : null;
	}
	
	public function getLocations($route_id, $pickup_id, $return_id)
	{
		$location_arr = array();

		$from_order = $this->getOrder($route_id, $pickup_id);
		$to_order = $this->getOrder($route_id, $return_id);
			
		if($from_order != null && $to_order != null)
		{				
			$location_arr = $this
				->reset()
				->where('route_id', $route_id)
				->where("($from_order <= t1.order AND t1.order <= $to_order)")
				->findAll()
				->getData();
		}
		return $location_arr;
	}
	
	public function getLocationIdPair($route_id, $pickup_id, $return_id)
	{
		$from_order = $this->getOrder($route_id, $pickup_id);
		$to_order = $this->getOrder($route_id, $return_id);	
		$location_id_arr = $this
			->reset()
			->where('route_id', $route_id)
			->where("($from_order <= t1.order AND t1.order <= $to_order)")
			->findAll()
			->getDataPair("city_id", "city_id");
		return $location_id_arr;
	}
}
?>