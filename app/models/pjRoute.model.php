<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjRouteModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'routes';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	public $i18n = array('title');
	
	public static function factory($attr=array())
	{
		return new pjRouteModel($attr);
	}
	
	public function updateRouteDetail($route_id)
	{
		$city_arr = pjRouteCityModel::factory()
			->where('route_id', $route_id)
			->orderBy("t1.order ASC")
			->findAll()
			->getData();
		$pjRouteDetailModel = pjRouteDetailModel::factory();
		
		$pjRouteDetailModel->where('route_id', $route_id)->eraseAll();
		foreach($city_arr as $key => $val)
		{
			foreach($city_arr as $k => $v)
			{
				if($val['order'] < $v['order'])
				{
					$data = array();
					$data['route_id'] = $route_id;
					$data['from_location_id'] = $val['city_id'];
					$data['to_location_id'] = $v['city_id'];
					$pjRouteDetailModel->reset()->setAttributes($data)->insert();
				}
			}
		}
	}
	
	public function updateBusTime($route_id)
	{
		$pjBusLocationModel = pjBusLocationModel::factory();
		
		$city_id_arr = pjRouteCityModel::factory()
			->where('route_id', $route_id)
			->orderBy("t1.order ASC")
			->findAll()
			->getDataPair('city_id', 'city_id');
		
		$bus_arr = pjBusModel::factory()->where('route_id', $route_id)->findAll()->getData();
		foreach($bus_arr as $v)
		{
			$old_time_arr = $pjBusLocationModel
				->reset()
				->where('bus_id', $v['id'])
				->findAll()
				->getDataPair('location_id', 'location_id');
				
			$removed_arr = array_diff($old_time_arr, $city_id_arr);
			foreach($removed_arr as $city_id)
			{
				$pjBusLocationModel->reset()->where('bus_id', $v['id'])->where('location_id', $city_id)->eraseAll();
			}
			$added_arr = array_diff($city_id_arr, $old_time_arr);
			foreach($added_arr as $city_id)
			{
				$data = array();
				$data['bus_id'] = $v['id'];
				$data['location_id'] = $city_id;
				$data['departure_time'] = ":NULL";
				$data['arrival_time'] = ":NULL";
				$pjBusLocationModel->reset()->setAttributes($data)->insert();
			}
		}
	}
	
	public function updateBusPrice($route_id)
	{
		$pjPriceModel = pjPriceModel::factory();
		$pjTicketModel = pjTicketModel::factory();
		
		$city_id_arr = pjRouteCityModel::factory()
			->where('route_id', $route_id)
			->orderBy("t1.order ASC")
			->findAll()
			->getDataPair('city_id', 'city_id');
		
		$bus_arr = pjBusModel::factory()->where('route_id', $route_id)->findAll()->getData();
		foreach($bus_arr as $v)
		{
			$ticket_arr = $pjTicketModel->reset()->where('bus_id', $v['id'])->findAll()->getData();
			foreach($ticket_arr as $ticket)
			{
				$price_arr = $pjPriceModel->reset()->select("t1.*, CONCAT(t1.from_location_id, '-', t1.to_location_id) AS pair")->where('bus_id', $v['id'])->where('ticket_id', $ticket['id'])->where('is_return = "F"')->findAll()->getDataPair('pair', 'pair');
				$pair_arr = array();
				$k = 0;
				foreach($city_id_arr as $row_id)
				{
					if($k <= (count($city_id_arr) - 2))
					{
						$j = 1;
						foreach($city_id_arr as $col_id)
						{
							if($j > 1)
							{
								$cnt = $pjPriceModel
									->reset()
									->where('ticket_id', $ticket['id'])
									->where('from_location_id', $row_id)
									->where('to_location_id', $col_id)
									->where('is_return = "F"')
									->findCount()
									->getData();
								
								$pair_arr[] = $row_id . '-' . $col_id;
								if($cnt == 0)
								{
									$data = array();
									$data['bus_id'] = $v['id'];
									$data['ticket_id'] = $ticket['id'];
									$data['from_location_id'] = $row_id;
									$data['to_location_id'] = $col_id;
									$data['is_return'] = 'F';
									$data['price'] = ":NULL";
									$pjPriceModel->reset()->setAttributes($data)->insert();
								}
							}
							$j++;
						}
					}
					$k++;
				}
				$removed_arr = array_diff($price_arr, $pair_arr);
				foreach($removed_arr as $pair_id)
				{
					list($from_location_id, $to_location_id) = explode("-", $pair_id);
					$pjPriceModel->reset()->where('bus_id', $v['id'])->where('ticket_id', $ticket['id'])->where('from_location_id', $from_location_id)->where('to_location_id', $to_location_id)->where('is_return = "F"')->eraseAll();
				}
			}
		}
		
		$bus_arr = pjBusModel::factory()->where('route_id', $route_id)->findAll()->getData();
		foreach($bus_arr as $v)
		{
			$ticket_arr = $pjTicketModel->reset()->where('bus_id', $v['id'])->findAll()->getData();
			foreach($ticket_arr as $ticket)
			{
				$price_arr = $pjPriceModel->reset()->select("t1.*, CONCAT(t1.from_location_id, '-', t1.to_location_id) AS pair")->where('bus_id', $v['id'])->where('ticket_id', $ticket['id'])->where('is_return = "T"')->findAll()->getDataPair('pair', 'pair');
				$pair_arr = array();
				$k = 0;
				foreach($city_id_arr as $row_id)
				{
					if($k <= (count($city_id_arr) - 2))
					{
						$j = 1;
						foreach($city_id_arr as $col_id)
						{
							if($j > 1)
							{
								$cnt = $pjPriceModel
									->reset()
									->where('ticket_id', $ticket['id'])
									->where('from_location_id', $row_id)
									->where('to_location_id', $col_id)
									->where('is_return = "T"')
									->findCount()
									->getData();
		
								$pair_arr[] = $row_id . '-' . $col_id;
								if($cnt == 0)
								{
									$data = array();
									$data['bus_id'] = $v['id'];
									$data['ticket_id'] = $ticket['id'];
									$data['from_location_id'] = $row_id;
									$data['to_location_id'] = $col_id;
									$data['is_return'] = 'T';
									$data['price'] = ":NULL";
									$pjPriceModel->reset()->setAttributes($data)->insert();
								}
							}
							$j++;
						}
					}
					$k++;
				}
				$removed_arr = array_diff($price_arr, $pair_arr);
				foreach($removed_arr as $pair_id)
				{
					list($from_location_id, $to_location_id) = explode("-", $pair_id);
					$pjPriceModel->reset()->where('bus_id', $v['id'])->where('ticket_id', $ticket['id'])->where('from_location_id', $from_location_id)->where('to_location_id', $to_location_id)->where('is_return = "T"')->eraseAll();
				}
			}
		}
	}
}
?>