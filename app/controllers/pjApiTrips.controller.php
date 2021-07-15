<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjApiTrips extends pjFront
{
    

	public function pjActionTrips() {
        
        $data = array();

        $trip_data1 = array();
        $trip_data1['bus'] = 'Lusaka - to';
        $trip_data1['location'] = 'Johannesburg';
        $list1 = array('On time', 'Air conditioning', 'On-board restroom', 'Great customer care');
        $trip_data1['list'] = $list1;
        array_push($data, $trip_data1);

        $trip_data2['bus'] = 'Johannesburg - to';
        $trip_data2['location'] = 'Lusaka';
        $list2 = array('Great customer care', 'On time', 'Air conditioning', 'On-board restroom');
        $trip_data2['list'] = $list2;
        array_push($data, $trip_data2);

        $trip_data3['bus'] = 'Lusaka - to';
        $trip_data3['location'] = 'Mazabuka';
        $list3 = array('Air conditioning', 'Great customer care', 'On-board restroom', 'On time');
        $trip_data3['list'] = $list3;
        array_push($data, $trip_data3);

        $trip_data4['bus'] = 'Johannesburg - to';
        $trip_data4['location'] = 'Livingstone';
        $list4 = array('On-board restroom', 'Air conditioning', 'On time', 'Great customer care');
        $trip_data4['list'] = $list4;
        array_push($data, $trip_data4);
        
        $response = array(
            'status' => true,
            'data' => $data
        );    
        pjAppController::jsonResponse($response);
    }

    
}
?>