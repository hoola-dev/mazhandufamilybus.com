<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjFrontSchedule extends pjFront
{
	public function __construct()
	{
		parent::__construct();
	}

	public function pjActionIndex()
	{
		$this->setLayout('pjActionEmpty');
	
        $date = date('Y-m-d');
        $day_of_week = strtolower(date('l', strtotime($date)));
        
        $column = 'departure';
        $direction = 'ASC';
        if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
        {
            $column = $_GET['column'];
            $direction = strtoupper($_GET['direction']);
        }
        
        $pjBusLocationModel = pjBusLocationModel::factory();
            
        $bus_arr = pjBusModel::factory()
            ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->select(" t1.*, t2.content AS route,
                        (SELECT CONCAT(TSL1.departure_time, '~:~', TSL1.location_id) FROM `".$pjBusLocationModel->getTable()."` AS TSL1 WHERE TSL1.bus_id = t1.id AND TSL1.arrival_time IS NULL AND TSL1.departure_time IS NOT NULL LIMIT 1) AS departure,
                        (SELECT CONCAT(TSL2.arrival_time, '~:~', TSL2.location_id) FROM `".$pjBusLocationModel->getTable()."` AS TSL2 WHERE TSL2.bus_id = t1.id AND TSL2.departure_time IS NULL AND TSL2.arrival_time IS NOT NULL LIMIT 1) AS arrive,
                        (SELECT SUM(TBT.qty) FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT WHERE TBT.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.bus_id = t1.id AND TB.booking_date = '$date' AND TB.pickup_id = (SELECT TL1.city_id FROM `".pjRouteCityModel::factory()->getTable()."` AS TL1 WHERE TL1.route_id=t1.route_id ORDER BY `order` ASC LIMIT 1 ) AND TB.return_id = (SELECT TL2.city_id FROM `".pjRouteCityModel::factory()->getTable()."` AS TL2 WHERE TL2.route_id=t1.route_id ORDER BY `order` DESC LIMIT 1 ) ) LIMIT 1) AS tickets,
                        (SELECT SUM(TBT.qty) FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT WHERE TBT.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.bus_id = t1.id AND TB.booking_date = '$date') LIMIT 1) AS total_tickets")
            ->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')")
            ->orderBy("$column $direction")
            ->findAll()
            ->getData();

        $this->set('date', $date);
        $this->set('bus_arr', $bus_arr);
        
        $route_arr = pjRouteModel::factory()
            ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->select("t1.*, t2.content AS route")
            ->orderBy("route ASC")->findAll()->getData();
        
        $this->set('route_arr', $route_arr);
    }

    public function pjActionBookings()
	{
        if(isset($_GET['bus_id']) && isset($_GET['date']))
        {
            $this->setLayout('pjActionEmpty');            

            $pjUserModel = pjUserModel::factory();

            $bus_id = $_GET['bus_id'];
            $date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);

            $this->set('date', $date);

            $bus_arr = pjBusModel::factory()
                ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->select(" t1.*, t2.content AS route")
                ->find($bus_id)
                ->getData();
            
            $location_arr = pjRouteCityModel::factory()
                ->select('t1.*, t2.content as location')
                ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->where('route_id', $bus_arr['route_id'])
                ->orderBy("t1.order ASC")
                ->findAll()->getData();
            $this->set('location_arr', $location_arr);
            
            $pjBookingModel = pjBookingModel::factory()
                ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.pickup_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t3.model='pjCity' AND t3.foreign_id=t1.return_id AND t3.field='name' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                ->select("t1.*, t2.content as from_location, t3.content as to_location, 
                        (SELECT GROUP_CONCAT(CONCAT_WS(' x ', TML.content, TBT.qty) SEPARATOR '~:~') FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT LEFT OUTER JOIN `".pjMultiLangModel::factory()->getTable()."` AS TML ON (TML.model='pjTicket' AND TML.foreign_id=TBT.ticket_id AND TML.field='title' AND TML.locale='".$this->getLocaleId()."') WHERE TBT.booking_id = t1.id AND TBT.qty > 0) as tickets")
                ->where('status', 'confirmed')
                ->where('bus_id', $bus_id)
                ->where('bus_departure_date', $date);              

           
            $booking_arr =	$pjBookingModel->orderBy("t1.created DESC")
                ->findAll()
                ->toArray('tickets', '~:~')
                ->getData();
                                
            $pjBookingSeatModel = pjBookingSeatModel::factory();
            foreach($booking_arr as $k => $v)
            {					
                $booking_arr[$k]['seats'] = $pjBookingSeatModel
                    ->reset()
                    ->join('pjSeat', "t2.id=t1.seat_id", 'left outer')
                    ->select("t1.seat_id, t2.name")
                    ->where("t1.booking_id", $v['id'])
                    ->findAll()
                    ->getDataPair("seat_id", 'name');
            }

            $ticket_arr = pjBookingTicketModel::factory()
                ->select("t1.ticket_id, t2.content as title, SUM(qty) AS total_tickets")
                ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->where("t1.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.status='confirmed' AND TB.bus_id = ".$bus_id." AND TB.bus_departure_date='$date')")
                ->groupBy("t1.ticket_id")
                ->findAll()
                ->getData();

                
            $total_passengers = 0;
            foreach($ticket_arr as $v)
            {
                $total_passengers += intval($v['total_tickets']);
            }
            
            $this->set('total_passengers', $total_passengers);
            $this->set('total_bookings', count($booking_arr));
            $this->set('ticket_arr', $ticket_arr);
            
            $this->set('booking_arr', $booking_arr);
            $this->set('bus_arr', $bus_arr);         
        }
	}
}
?>