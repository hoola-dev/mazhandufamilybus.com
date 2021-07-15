<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjApiDistricts extends pjFront
{

	public function pjActionDistricts() {
        $data = array();
        $districts = pjDistrictModel::factory()
        ->select('dt_id as id, dt_name as name')
        ->orderBy('dt_name ASC')
        ->findAll()
        ->getData();
        if(isset($districts[0]['id']) && !empty($districts[0]['id'])){
            $data = $districts;
            $status = true;  
        }else{
            $data = array(
                    'message' => 'No data found'
                );
            $status = false;  
        }
        $response = array(
            'status' => $status,
            'data' => $data
        );    
        pjAppController::jsonResponse($response);
    }	
   
}
?>